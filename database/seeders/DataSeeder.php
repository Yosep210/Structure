<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DataSeeder extends Seeder
{
    /**
     * Master Data Seeder to import legacy pengenalan_dbs.sql dump
     * into 55 clean domain tables according to domain.sql & struktur refaktor.md.
     */
    public function run(): void
    {
        $candidatePaths = [
            env('LEGACY_SQL_FILE'),
            function_exists('base_path') ? base_path('pengenalan_dbs.sql') : null,
            function_exists('database_path') ? database_path('seeders/pengenalan_dbs.sql') : null,
            function_exists('database_path') ? database_path('pengenalan_dbs.sql') : null,
            __DIR__.'/../pengenalan_dbs.sql',
            __DIR__.'/../../pengenalan_dbs.sql',
            __DIR__.'/pengenalan_dbs.sql',
            getcwd().'/pengenalan_dbs.sql',
        ];

        $sqlFile = null;
        foreach ($candidatePaths as $path) {
            if ($path && file_exists($path)) {
                $sqlFile = realpath($path);
                break;
            }
        }

        if (! $sqlFile) {
            if (isset($this->command)) {
                $this->command->error("SQL dump file 'pengenalan_dbs.sql' tidak ditemukan.");
            }

            return;
        }

        if (isset($this->command)) {
            $this->command->info('Memulai Impor Data Lengkap (55 Tabel Domain) dari SQL dump...');
        }

        $handle = fopen($sqlFile, 'r');
        if (! $handle) {
            if (isset($this->command)) {
                $this->command->error('Gagal membuka file SQL dump.');
            }

            return;
        }

        $chunkSize = 500;
        $tables = [
            // Master & Geographic
            'areas',
            'countries',
            'provinces',
            'cities',
            'districts',
            'banks',
            'ongkirs',
            // Member & Network
            'members',
            'member_profiles',
            'member_networks',
            'stockists',
            'member_confirms',
            'member_omzets',
            'member_points',
            'member_reports',
            'member_upgrades',
            // Product & Inventory
            'packages',
            'products',
            'product_varians',
            'product_area_fees',
            'product_packages',
            'product_package_items',
            'suppliers',
            'product_stock_ins',
            'product_stock_outs',
            'product_stock_opnames',
            // Stockist & PIN
            'pins',
            'pin_transfers',
            'product_stockists',
            'product_stockist_opnames',
            'purchase_orders',
            'purchase_order_details',
            // Commission & E-Wallet
            'bonuses',
            'bonus_generation_refs',
            'bonus_level_refs',
            'bonus_matching_refs',
            'ewallets',
            'ewallet_transfers',
            'withdrawals',
            // Shop & E-Commerce
            'carts',
            'shop_orders',
            'shop_order_details',
            'ros',
            'auto_ros',
            'eproduct_mutations',
            // Reward & Rank
            'reward_configs',
            'member_rewards',
            'member_reward_points',
            'member_ro_point_monthlies',
            'member_rank_histories',
            // Staff & System
            'staffs',
            'news',
            'notification_templates',
            'videos',
            'otps',
            'model_has_roles',
            'model_has_permissions',
        ];

        // DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Mysql
        Schema::disableForeignKeyConstraints(); // sqlite
        foreach ($tables as $t) {
            if (DB::getSchemaBuilder()->hasTable($t)) {
                DB::table($t)->truncate();
            }
        }
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Mysql
        Schema::enableForeignKeyConstraints();  // sqlite

        // Auto-seed default Spatie roles based on Member Type constants
        $roleLookup = null;
        if (DB::getSchemaBuilder()->hasTable('roles')) {
            $now = now();
            $rolesList = [
                'admin',         // 1 - ADMINISTRATOR -> ADMIN
                'member',        // 2 - MEMBER
                'stockist',      // 3 - STOCKIST
                'warehouse',     // 4 - WAREHOUSE
                'customer',      // 0 - CUSTOMER
                'staff',         // Staff Backoffice
            ];
            foreach ($rolesList as $roleName) {
                DB::table('roles')->updateOrInsert(
                    ['name' => $roleName, 'guard_name' => 'web'],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }

            $roleLookup = [];
            foreach (DB::table('roles')->get(['id', 'name']) as $role) {
                $roleLookup[$role->name] = $role->id;
            }
        }

        // Auto-seed numeric permissions 1..56 (compatible with StaffAccess enum)
        $permissionLookup = null;
        if (DB::getSchemaBuilder()->hasTable('permissions')) {
            $now = now();
            for ($i = 1; $i <= 56; $i++) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => (string) $i, 'guard_name' => 'web'],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }

            $permissionLookup = [];
            foreach (DB::table('permissions')->get(['id', 'name']) as $perm) {
                $permissionLookup[(int) $perm->name] = $perm->id;
            }
        }

        $chunks = [];
        $counts = [];
        foreach ($tables as $t) {
            $chunks[$t] = [];
            $counts[$t] = 0;
        }

        // Deduplication lookups
        $slugLookups = [
            'news' => [],
            'notification_templates' => [],
            'products' => [],
            'product_packages' => [],
            'packages' => [],
        ];
        $uniqueLookups = [
            'ewallet' => [],
            'bonus_code' => [],
            'transfer_code' => [],
            'invoice' => [],
            'pin' => [],
        ];

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (empty($line) || ! str_starts_with($line, 'INSERT INTO ')) {
                continue;
            }

            if (! preg_match('/^INSERT INTO `([^`]+)` \(([^)]+)\) VALUES \((.+)\);$/i', $line, $matches)) {
                continue;
            }

            $oldTable = $matches[1];
            $colsString = $matches[2];
            $valsString = $matches[3];

            $cols = array_map(fn ($c) => trim($c, ' `'), explode(',', $colsString));
            $vals = str_getcsv($valsString, ',', "'", '\\');
            $vals = array_map(function ($v) {
                $v = trim($v);

                return (strcasecmp($v, 'NULL') === 0 || $v === "''" || $v === '0000-00-00 00:00:00') ? null : trim($v, "'");
            }, $vals);

            if (count($cols) !== count($vals)) {
                continue;
            }

            $rawRow = array_combine($cols, $vals);

            switch ($oldTable) {
                // 1. Master & Geographic
                case 'jpb_area':
                    $chunks['areas'][] = [
                        'id' => $rawRow['id'],
                        'name' => $rawRow['area_name'] ?? ($rawRow['name'] ?? ''),
                        'code' => $rawRow['area_code'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $counts['areas']++;
                    break;

                case 'jpb_country':
                    $chunks['countries'][] = [
                        'id' => $rawRow['id'],
                        'iso' => $rawRow['iso'] ?? 'ID',
                        'iso3' => $rawRow['iso3'] ?? 'IDN',
                        'name' => $rawRow['name'] ?? '',
                        'nice_name' => $rawRow['nicename'] ?? ($rawRow['name'] ?? ''),
                        'num_code' => $rawRow['numcode'] ?? '360',
                        'phone_code' => $rawRow['phonecode'] ?? '62',
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $counts['countries']++;
                    break;

                case 'jpb_province':
                    $chunks['provinces'][] = [
                        'id' => $rawRow['id'],
                        'country_id' => ! empty($rawRow['country_id']) ? (int) $rawRow['country_id'] : 100,
                        'area_id' => ! empty($rawRow['area_id']) ? (int) $rawRow['area_id'] : null,
                        'name' => $rawRow['province_name'] ?? ($rawRow['name'] ?? ''),
                        'code' => $rawRow['province_code'] ?? null,
                        'id_express' => $rawRow['id_express'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $counts['provinces']++;
                    break;

                case 'jpb_district':
                    $chunks['cities'][] = [
                        'id' => $rawRow['id'],
                        'province_id' => ! empty($rawRow['province_id']) ? (int) $rawRow['province_id'] : 1,
                        'name' => $rawRow['district_name'] ?? ($rawRow['name'] ?? ''),
                        'type' => $rawRow['district_type'] ?? 'Kabupaten',
                        'code' => $rawRow['district_code'] ?? null,
                        'postal_code' => $rawRow['postal_code'] ?? null,
                        'id_express' => $rawRow['id_express'] ?? null,
                        'lion' => $rawRow['lion'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $counts['cities']++;
                    break;

                case 'jpb_subdistrict':
                    $chunks['districts'][] = [
                        'id' => $rawRow['id'],
                        'city_id' => ! empty($rawRow['district_id']) ? (int) $rawRow['district_id'] : (! empty($rawRow['city_id']) ? (int) $rawRow['city_id'] : 1),
                        'name' => $rawRow['subdistrict_name'] ?? ($rawRow['district_name'] ?? ($rawRow['name'] ?? '')),
                        'id_express' => $rawRow['id_express'] ?? null,
                        'lion' => $rawRow['lion'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $counts['districts']++;
                    break;

                case 'jpb_banks':
                    $chunks['banks'][] = [
                        'id' => $rawRow['id'],
                        'name' => $rawRow['nama'] ?? ($rawRow['name'] ?? ''),
                        'code' => $rawRow['kode'] ?? ($rawRow['code'] ?? null),
                        'type' => $rawRow['type'] ?? 'bank',
                        'flipcode' => $rawRow['flipcode'] ?? null,
                        'espaycode' => $rawRow['espaycode'] ?? null,
                        'linkitacode' => $rawRow['linkitacode'] ?? null,
                        'logo' => $rawRow['logo'] ?? null,
                        'img' => $rawRow['img'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $counts['banks']++;
                    break;

                case 'jpb_rajaongkir':
                    $chunks['ongkirs'][] = [
                        'id' => $rawRow['id'],
                        'origin_id' => ! empty($rawRow['origin_id']) ? (int) $rawRow['origin_id'] : 151,
                        'destination_id' => ! empty($rawRow['id_city']) ? (int) $rawRow['id_city'] : (! empty($rawRow['destination_id']) ? (int) $rawRow['destination_id'] : 1),
                        'province_id' => ! empty($rawRow['id_province']) ? (int) $rawRow['id_province'] : (! empty($rawRow['province_id']) ? (int) $rawRow['province_id'] : null),
                        'city_id' => ! empty($rawRow['id_city']) ? (int) $rawRow['id_city'] : (! empty($rawRow['city_id']) ? (int) $rawRow['city_id'] : null),
                        'district_id' => ! empty($rawRow['id_district']) ? (int) $rawRow['id_district'] : (! empty($rawRow['district_id']) ? (int) $rawRow['district_id'] : null),
                        'id_province' => ! empty($rawRow['id_province']) ? (int) $rawRow['id_province'] : (! empty($rawRow['province_id']) ? (int) $rawRow['province_id'] : null),
                        'id_city' => ! empty($rawRow['id_city']) ? (int) $rawRow['id_city'] : (! empty($rawRow['city_id']) ? (int) $rawRow['city_id'] : null),
                        'id_district' => ! empty($rawRow['id_district']) ? (int) $rawRow['id_district'] : (! empty($rawRow['district_id']) ? (int) $rawRow['district_id'] : null),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $counts['ongkirs']++;
                    break;

                    // 2. Member & Network (Split from jpb_member)
                case 'jpb_member':
                    // A. members table
                    $status = 0;
                    if (($rawRow['status'] ?? 0) == 1) {
                        $status = 1;
                    } elseif (($rawRow['status'] ?? 0) == 2) {
                        $status = 2;
                    }
                    $email = ! empty($rawRow['email']) ? trim($rawRow['email']) : (($rawRow['username'] ?? 'member'.$rawRow['id']).'@jpbuana.com');

                    $chunks['members'][] = [
                        'id' => $rawRow['id'],
                        'name' => $rawRow['name'] ?? ('Member '.$rawRow['id']),
                        'username' => $rawRow['username'] ?? ('user'.$rawRow['id']),
                        'email' => $email,
                        'email_verified_at' => now(),
                        'password' => $rawRow['password'] ?? bcrypt('12345678'),
                        'two_factor_secret' => null,
                        'two_factor_recovery_codes' => null,
                        'two_factor_confirmed_at' => null,
                        'status' => $status,
                        'ewallet_balance' => $rawRow['ewallet_balance'] ?? ($rawRow['saldo_ewallet'] ?? ($rawRow['ewallet'] ?? 0)),
                        'last_login' => $this->cleanDate($rawRow['last_login'] ?? null),
                        'remember_token' => null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['members']++;

                    // B. member_profiles table
                    $countryId = ! empty($rawRow['country']) ? (int) $rawRow['country'] : 100;
                    $provinceId = ! empty($rawRow['province']) ? (int) $rawRow['province'] : null;
                    $cityId = ! empty($rawRow['city']) ? (int) $rawRow['city'] : (! empty($rawRow['district']) ? (int) $rawRow['district'] : null);
                    $districtId = ! empty($rawRow['subdistrict']) ? (int) $rawRow['subdistrict'] : null;
                    $bankId = ! empty($rawRow['bank']) ? (int) $rawRow['bank'] : null;

                    $chunks['member_profiles'][] = [
                        'id' => $rawRow['id'],
                        'member_id' => $rawRow['id'],
                        'birthdate' => $this->cleanDate($rawRow['birthdate'] ?? null),
                        'gender' => $rawRow['gender'] ?? null,
                        'phone' => $this->normalizePhone($rawRow['phone'] ?? null),
                        'bank_id' => $bankId ?: null,
                        'bank_account_number' => $rawRow['bill'] ?? null,
                        'bank_account_name' => $rawRow['bill_name'] ?? null,
                        'bank_status' => 1,
                        'id_card_number' => $rawRow['idcard'] ?? null,
                        'id_card_img' => $rawRow['idcard_img'] ?? null,
                        'npwp_number' => $rawRow['npwp'] ?? null,
                        'image' => $rawRow['photo'] ?? null,
                        'qr_code_img' => $rawRow['qrcode_img'] ?? null,
                        'country_id' => $countryId ?: null,
                        'province_id' => $provinceId ?: null,
                        'city_id' => $cityId ?: null,
                        'district_id' => $districtId ?: null,
                        'village' => $rawRow['village'] ?? null,
                        'address' => $rawRow['address'] ?? null,
                        'password_security' => $rawRow['password_pin'] ?? null,
                        'password_transaction' => $rawRow['password_trx'] ?? null,
                        'agree_ethic' => (($rawRow['agree_ethic'] ?? 0) == 1),
                        'wd_status' => (int) ($rawRow['wd_status'] ?? 1),
                        'wd_min' => $rawRow['wd_min'] ?? 0,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_profiles']++;

                    // C. member_networks table
                    $sponsorId = ! empty($rawRow['sponsor']) ? (int) $rawRow['sponsor'] : null;
                    $parentId = ! empty($rawRow['parent']) ? (int) $rawRow['parent'] : null;
                    $pos = ! empty($rawRow['position']) ? strtolower($rawRow['position']) : null;
                    if ($pos !== 'left' && $pos !== 'right') {
                        $pos = null;
                    }

                    $chunks['member_networks'][] = [
                        'id' => $rawRow['id'],
                        'member_id' => $rawRow['id'],
                        'referral_code' => ! empty($rawRow['referral_code']) ? $rawRow['referral_code'] : ('REF-'.$rawRow['id'].'-'.Str::random(4)),
                        'hu_index' => max(1, (int) ($rawRow['member_hu'] ?? 1)),
                        'package' => ! empty($rawRow['package']) ? $rawRow['package'] : 'common',
                        'sponsor_id' => $sponsorId ?: null,
                        'parent_id' => $parentId ?: null,
                        'position' => $pos,
                        'group' => (int) ($rawRow['group'] ?? 0),
                        'gen' => (int) ($rawRow['gen'] ?? 0),
                        'level' => (int) ($rawRow['level'] ?? 0),
                        'tree' => $rawRow['tree'] ?? null,
                        'tree_sponsor' => $rawRow['tree_sponsor'] ?? null,
                        'total_omzet' => $rawRow['total_omzet'] ?? 0,
                        'package_omzet' => $rawRow['package_omzet'] ?? 0,
                        'rank_id' => $this->parseRankId($rawRow['rank'] ?? null) ?? 1,
                        'is_ro' => (($rawRow['is_ro'] ?? 0) == 1),
                        'autoro_balance' => $rawRow['autoro_balance'] ?? ($rawRow['saldo_autoro'] ?? ($rawRow['autoro'] ?? 0)),
                        'datero' => $this->cleanDate($rawRow['datero'] ?? null),
                        'dateupgrade' => $this->cleanDate($rawRow['dateupgrade'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_networks']++;

                    // D. stockists table (if stockist data exists)
                    $asStockistVal = (int) ($rawRow['as_stockist'] ?? 0);
                    if ($asStockistVal > 0 || ! empty($rawRow['as_stockist_pusat']) || ! empty($rawRow['stockist_name'])) {
                        $chunks['stockists'][] = [
                            'id' => $rawRow['id'],
                            'member_network_id' => $rawRow['id'],
                            'as_stockist' => in_array($asStockistVal, [1, 2, 3]),
                            'as_stockist_pusat' => (((int) ($rawRow['as_stockist_pusat'] ?? 0) == 1) || ($asStockistVal == 3)),
                            'stockist_name' => $rawRow['stockist_name'] ?? null,
                            'stockist_province' => ! empty($rawRow['stockist_province']) ? (int) $rawRow['stockist_province'] : null,
                            'stockist_city' => ! empty($rawRow['stockist_district']) ? (int) $rawRow['stockist_district'] : null,
                            'stockist_district' => ! empty($rawRow['stockist_subdistrict']) ? (int) $rawRow['stockist_subdistrict'] : null,
                            'stockist_village' => $rawRow['stockist_village'] ?? null,
                            'stockist_address' => $rawRow['stockist_address'] ?? null,
                            'to_stockist' => ! empty($rawRow['to_stockist']) ? (int) $rawRow['to_stockist'] : null,
                            'datestockist' => $this->cleanDate($rawRow['datestockist'] ?? null),
                            'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                            'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                        ];
                        $counts['stockists']++;
                    }

                    // E. Assign Spatie Role to model_has_roles based on Member Type
                    if ($roleLookup !== null) {
                        $typeVal = (int) ($rawRow['type'] ?? 0);
                        $roleName = match ($typeVal) {
                            1 => 'admin',
                            2 => 'member',
                            3 => 'stockist',
                            4 => 'warehouse',
                            0 => (! empty($rawRow['package']) && $rawRow['package'] !== 'customer' ? 'member' : 'customer'),
                            default => 'member',
                        };

                        if (! empty($roleLookup[$roleName])) {
                            $chunks['model_has_roles'][] = [
                                'role_id' => $roleLookup[$roleName],
                                'model_type' => 'App\Modules\Member\Infrastructure\Eloquent\ModelMember',
                                'model_id' => $rawRow['id'],
                            ];
                            $counts['model_has_roles']++;
                        }
                    }
                    break;

                case 'jpb_member_confirm':
                    $chunks['member_confirms'][] = [
                        'id' => $rawRow['id'],
                        'member_id' => $rawRow['id_member'] ?? $rawRow['member_id'] ?? 1,
                        'downline_id' => ! empty($rawRow['id_downline']) ? (int) $rawRow['id_downline'] : (! empty($rawRow['downline_id']) ? (int) $rawRow['downline_id'] : null),
                        'sponsor_id' => ! empty($rawRow['id_sponsor']) ? (int) $rawRow['id_sponsor'] : null,
                        'parent_id' => ! empty($rawRow['id_upline']) ? (int) $rawRow['id_upline'] : null,
                        'position' => ! empty($rawRow['position']) && in_array(strtolower($rawRow['position']), ['left', 'right']) ? strtolower($rawRow['position']) : null,
                        'package' => ! empty($rawRow['package']) ? $rawRow['package'] : 'member',
                        'package_count' => (int) ($rawRow['package_count'] ?? 1),
                        'referral_code' => $rawRow['referral_code'] ?? null,
                        'bv' => $rawRow['bv'] ?? 0,
                        'nominal' => $rawRow['nominal'] ?? 0,
                        'shipping' => $rawRow['shipping'] ?? 0,
                        'uniquecode' => ! empty($rawRow['uniquecode']) ? (int) $rawRow['uniquecode'] : null,
                        'total_payment' => $rawRow['total_payment'] ?? 0,
                        'shop_order_id' => ! empty($rawRow['id_shop_order']) ? (int) $rawRow['id_shop_order'] : null,
                        'access' => $rawRow['access'] ?? null,
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'dateconfirmed' => $this->cleanDate($rawRow['dateconfirmed'] ?? null),
                        'confirm_by' => $rawRow['confirm_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_confirms']++;
                    break;

                case 'jpb_member_omzet':
                    $statusVal = 0;
                    if (isset($rawRow['status'])) {
                        $sLower = strtolower($rawRow['status']);
                        if ($sLower === 'perdana') {
                            $statusVal = 1;
                        } elseif ($sLower === 'ro') {
                            $statusVal = 2;
                        } else {
                            $statusVal = (int) $rawRow['status'];
                        }
                    }
                    $chunks['member_omzets'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'pack_omzet' => $rawRow['pack_omzet'] ?? null,
                        'bv' => $rawRow['bv'] ?? 0,
                        'sponsor' => $rawRow['sponsor'] ?? 0,
                        'pairing' => $rawRow['pairing'] ?? 0,
                        'reward' => $rawRow['reward'] ?? 0,
                        'stockist' => $rawRow['stockist'] ?? 0,
                        'ro' => $rawRow['ro'] ?? 0,
                        'omzet' => $rawRow['omzet'] ?? 0,
                        'price' => $rawRow['price'] ?? 0,
                        'amount' => $rawRow['amount'] ?? 0,
                        'status' => $statusVal,
                        'description' => $rawRow['desc'] ?? ($rawRow['description'] ?? null),
                        'date' => $this->cleanDate($rawRow['date'] ?? null) ?: date('Y-m-d'),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_omzets']++;
                    break;

                case 'jpb_member_point':
                case 'jpb_pairing_point':
                    $chunks['member_points'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'package' => $rawRow['package'] ?? null,
                        'type' => $rawRow['type'] ?? 'regular',
                        'bv' => $rawRow['bv'] ?? 0,
                        'point' => $rawRow['point'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_points']++;
                    break;

                case 'jpb_member_report':
                    $chunks['member_reports'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'pairing_left' => $rawRow['pairing_left'] ?? 0,
                        'pairing_right' => $rawRow['pairing_right'] ?? 0,
                        'pairing_qualified' => $rawRow['pairing_qualified'] ?? 0,
                        'reward_left' => $rawRow['reward_left'] ?? 0,
                        'reward_right' => $rawRow['reward_right'] ?? 0,
                        'total_omzet' => $rawRow['total_omzet'] ?? 0,
                        'total_ro_omzet' => $rawRow['total_ro_omzet'] ?? 0,
                        'total_ro_point' => $rawRow['total_ro_point'] ?? 0,
                        'last_ro_omzet' => $rawRow['last_ro_omzet'] ?? 0,
                        'last_ro_point' => $rawRow['last_ro_point'] ?? 0,
                        'last_ro_date' => $this->cleanDate($rawRow['last_ro_date'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_reports']++;
                    break;

                case 'jpb_upgrade':
                    $pins = null;
                    if (! empty($rawRow['pins'])) {
                        $pins = json_encode(explode(',', $rawRow['pins']));
                    }
                    $chunks['member_upgrades'][] = [
                        'id' => $rawRow['id'],
                        'upgrader_id' => ! empty($rawRow['upgrader']) ? (int) $rawRow['upgrader'] : null,
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'package_before' => $rawRow['package_before'] ?? null,
                        'package_after' => $rawRow['package_after'] ?? null,
                        'upgrade_type' => $rawRow['upgrade'] ?? null,
                        'omzet' => $rawRow['omzet'] ?? 0,
                        'amount' => $rawRow['amount'] ?? 0,
                        'point' => $rawRow['point'] ?? 0,
                        'pins' => $pins,
                        'description' => $rawRow['desc'] ?? ($rawRow['description'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_upgrades']++;
                    break;

                    // 3. Product & Inventory
                case 'jpb_package':
                    $chunks['packages'][] = [
                        'id' => $rawRow['id'] ?? null,
                        'package' => $rawRow['package'],
                        'package_name' => $rawRow['package_name'] ?? $rawRow['package'],
                        'package_index' => (int) ($rawRow['package_index'] ?? 1),
                        'package_count' => (int) ($rawRow['package_count'] ?? 1),
                        'bv' => $rawRow['bv'] ?? 0,
                        'nominal' => $rawRow['nominal'] ?? 0,
                        'sponsor_percent' => $rawRow['sponsor_percent'] ?? 0,
                        'passup_percent' => $rawRow['passup_percent'] ?? 0,
                        'pairing_percent' => $rawRow['pairing_percent'] ?? 0,
                        'pairing_nominal' => $rawRow['pairing_nominal'] ?? 0,
                        'pairing_max' => $rawRow['pairing_max'] ?? 0,
                        'pairing_point' => $rawRow['pairing_point'] ?? 0,
                        'reward_point' => $rawRow['reward_point'] ?? 0,
                        'description' => $rawRow['description'] ?? null,
                        'is_register' => ($rawRow['is_register'] ?? 1) == 1,
                        'is_order' => ($rawRow['is_order'] ?? 1) == 1,
                        'is_active' => ($rawRow['is_active'] ?? 1) == 1,
                        'modified_by' => null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['packages']++;
                    break;

                case 'jpb_product':
                    $sku = $rawRow['sku'] ?? ('PROD-'.$rawRow['id']);
                    $slug = $rawRow['slug'] ?? Str::slug($rawRow['name'] ?? ('product-'.$rawRow['id']));
                    if (isset($slugLookups['products'][$slug])) {
                        $slug = $slug.'-'.$rawRow['id'];
                    }
                    $slugLookups['products'][$slug] = true;

                    $chunks['products'][] = [
                        'id' => $rawRow['id'],
                        'sku' => $sku,
                        'name' => $rawRow['name'] ?? 'Produk',
                        'slug' => $slug,
                        'type' => $rawRow['type'] ?? 'perdana',
                        'hu' => (int) ($rawRow['hu'] ?? 0),
                        'bv' => $rawRow['bv'] ?? 0,
                        'price_hpp' => $rawRow['price_hpp'] ?? 0,
                        'price' => $rawRow['price'] ?? 0,
                        'price_member' => $rawRow['price_member'] ?? ($rawRow['price_number'] ?? 0),
                        'stock' => (int) ($rawRow['stock'] ?? ($rawRow['total_stock'] ?? 0)),
                        'price_customer' => $rawRow['price_customer'] ?? 0,
                        'sponsor_point' => $rawRow['sponsor_point'] ?? 0,
                        'pairing_point' => $rawRow['pairing_point'] ?? 0,
                        'reward_point' => $rawRow['reward_point'] ?? 0,
                        'stockist_point' => $rawRow['stockist_point'] ?? 0,
                        'reward_budget' => $rawRow['reward_budget'] ?? 0,
                        'weight' => $rawRow['weight'] ?? 0,
                        'description' => $rawRow['description'] ?? null,
                        'image' => $rawRow['image'] ?? null,
                        's3_upload' => ($rawRow['s3_upload'] ?? 0) == 1,
                        'show_order' => ($rawRow['show_order'] ?? 1) == 1,
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'created_by' => $rawRow['created_by'] ?? null,
                        'modified_by' => $rawRow['modified_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['products']++;
                    break;

                case 'jpb_product_varian':
                    $chunks['product_varians'][] = [
                        'id' => $rawRow['id'] ?? null,
                        'product_id' => ! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1,
                        'varian' => $rawRow['varian'] ?? 'Default',
                        'bv' => $rawRow['bv'] ?? 0,
                        'price_hpp' => $rawRow['price_hpp'] ?? 0,
                        'price' => $rawRow['price'] ?? 0,
                        'stock' => (int) ($rawRow['stock'] ?? 0),
                        'weight' => $rawRow['weight'] ?? 0,
                        'image' => $rawRow['image'] ?? null,
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['product_varians']++;
                    break;

                case 'jpb_product_area_fee':
                    $chunks['product_area_fees'][] = [
                        'id' => $rawRow['id'] ?? null,
                        'feeable_type' => $rawRow['feeable_type'] ?? 'product',
                        'feeable_id' => ! empty($rawRow['feeable_id']) ? (int) $rawRow['feeable_id'] : (! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1),
                        'area_id' => ! empty($rawRow['area_id']) ? (int) $rawRow['area_id'] : 1,
                        'fee' => $rawRow['fee'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['product_area_fees']++;
                    break;

                case 'jpb_product_package':
                    $pkgSku = $rawRow['sku'] ?? ('PKG-'.$rawRow['id']);
                    $pkgSlug = $rawRow['slug'] ?? Str::slug($rawRow['name'] ?? ('package-'.$rawRow['id']));
                    if (isset($slugLookups['product_packages'][$pkgSlug])) {
                        $pkgSlug = $pkgSlug.'-'.$rawRow['id'];
                    }
                    $slugLookups['product_packages'][$pkgSlug] = true;

                    $chunks['product_packages'][] = [
                        'id' => $rawRow['id'],
                        'sku' => $pkgSku,
                        'name' => $rawRow['name'] ?? $rawRow['package_name'] ?? 'Paket Produk',
                        'slug' => $pkgSlug,
                        'type' => $rawRow['type'] ?? null,
                        'hu' => (int) ($rawRow['hu'] ?? 0),
                        'type_price' => in_array($rawRow['type_price'] ?? '', ['flat', 'total']) ? $rawRow['type_price'] : 'flat',
                        'total_item' => (int) ($rawRow['total_item'] ?? 0),
                        'total_qty' => (int) ($rawRow['total_qty'] ?? 0),
                        'total_amount' => $rawRow['total_amount'] ?? 0,
                        'bv' => $rawRow['bv'] ?? 0,
                        'price' => $rawRow['price'] ?? 0,
                        'weight' => $rawRow['weight'] ?? 0,
                        'sponsor_point' => $rawRow['sponsor_point'] ?? 0,
                        'pairing_point' => $rawRow['pairing_point'] ?? 0,
                        'stockist_point' => $rawRow['stockist_point'] ?? 0,
                        'reward_point' => $rawRow['reward_point'] ?? 0,
                        'image' => $rawRow['image'] ?? null,
                        'description' => $rawRow['description'] ?? null,
                        'show_order' => ($rawRow['show_order'] ?? 1) == 1,
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'created_by' => $rawRow['created_by'] ?? null,
                        'modified_by' => $rawRow['modified_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['product_packages']++;
                    break;

                case 'jpb_product_package_item':
                    $chunks['product_package_items'][] = [
                        'id' => $rawRow['id'],
                        'package_id' => ! empty($rawRow['package_id']) ? (int) $rawRow['package_id'] : (! empty($rawRow['id_package']) ? (int) $rawRow['id_package'] : 1),
                        'product_id' => ! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : (! empty($rawRow['id_product']) ? (int) $rawRow['id_product'] : 1),
                        'varian_id' => ! empty($rawRow['varian_id']) ? (int) $rawRow['varian_id'] : null,
                        'qty' => (int) ($rawRow['qty'] ?? 1),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['product_package_items']++;
                    break;

                case 'jpb_supplier':
                case 'jpb_suppliers':
                case 'supplier':
                case 'suppliers':
                    $suppName = $rawRow['name'] ?? ($rawRow['supplier_name'] ?? 'Supplier');
                    $suppEmail = trim($rawRow['email'] ?? '');
                    if (empty($suppEmail)) {
                        $suppEmail = (strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $suppName)) ?: 'supplier').'@jpbuana.com';
                    }
                    $chunks['suppliers'][] = [
                        'id' => (int) $rawRow['id'],
                        'name' => $suppName,
                        'email' => $suppEmail,
                        'phone' => $rawRow['phone'] ?? null,
                        'address' => $rawRow['address'] ?? null,
                        'id_contact_kledo' => $rawRow['id_contact_kledo'] ?? null,
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? ($rawRow['created_at'] ?? null)) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? ($rawRow['updated_at'] ?? null)) ?: now(),
                    ];
                    $counts['suppliers']++;
                    break;

                case 'jpb_product_stock_in':
                case 'jpb_product_stock':
                    $chunks['product_stock_ins'][] = [
                        'id' => $rawRow['id'],
                        'supplier_id' => ! empty($rawRow['id_supplier']) ? (int) $rawRow['id_supplier'] : null,
                        'source' => $rawRow['source'] ?? 'purchase',
                        'source_id' => (int) ($rawRow['id_source'] ?? 0),
                        'detail_id' => ! empty($rawRow['id_detail']) ? (int) $rawRow['id_detail'] : null,
                        'product_id' => ! empty($rawRow['product']) ? (int) $rawRow['product'] : (! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1),
                        'varian_id' => ! empty($rawRow['varian']) ? (int) $rawRow['varian'] : null,
                        'qty' => (int) ($rawRow['qty'] ?? 0),
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'description' => $rawRow['description'] ?? null,
                        'dateexpired' => $this->cleanDate($rawRow['dateexpired'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['product_stock_ins']++;
                    break;

                case 'jpb_product_stock_out':
                    $chunks['product_stock_outs'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => ! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : null,
                        'source' => $rawRow['source'] ?? 'sales',
                        'source_id' => (int) ($rawRow['id_source'] ?? 0),
                        'detail_id' => ! empty($rawRow['id_detail']) ? (int) $rawRow['id_detail'] : null,
                        'product_id' => ! empty($rawRow['product']) ? (int) $rawRow['product'] : (! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1),
                        'varian_id' => ! empty($rawRow['varian']) ? (int) $rawRow['varian'] : null,
                        'qty' => (int) ($rawRow['qty'] ?? 0),
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'description' => $rawRow['description'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['product_stock_outs']++;
                    break;

                case 'jpb_product_stock_opname':
                    $chunks['product_stock_opnames'][] = [
                        'id' => $rawRow['id'],
                        'ref_number' => $rawRow['ref_number'] ?? $rawRow['ref_no'] ?? ('OPN-'.$rawRow['id']),
                        'product_id' => ! empty($rawRow['product']) ? (int) $rawRow['product'] : (! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1),
                        'varian_id' => ! empty($rawRow['varian']) ? (int) $rawRow['varian'] : null,
                        'recorded_qty' => (int) ($rawRow['recorded_qty'] ?? ($rawRow['stock_system'] ?? 0)),
                        'actual_qty' => (int) ($rawRow['actual_qty'] ?? ($rawRow['stock_real'] ?? 0)),
                        'difference_qty' => (int) ($rawRow['difference_qty'] ?? ($rawRow['diff'] ?? 0)),
                        'price' => $rawRow['price'] ?? 0,
                        'total' => $rawRow['total'] ?? 0,
                        'type' => in_array(strtoupper($rawRow['type'] ?? ''), ['IN', 'OUT']) ? strtoupper($rawRow['type']) : 'IN',
                        'memo' => $rawRow['memo'] ?? null,
                        'description' => $rawRow['description'] ?? null,
                        'date_trans' => $this->cleanDate($rawRow['date_trans'] ?? ($rawRow['datecreated'] ?? null)) ?: date('Y-m-d'),
                        'created_by' => $rawRow['created_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['product_stock_opnames']++;
                    break;

                    // 4. Stockist & PIN
                case 'jpb_pin':
                    $idPin = $rawRow['id_pin'] ?? ('PIN-'.$rawRow['id']);
                    if (isset($uniqueLookups['pin'][$idPin])) {
                        $idPin = $idPin.'-'.$rawRow['id'];
                    }
                    $uniqueLookups['pin'][$idPin] = true;

                    $chunks['pins'][] = [
                        'id' => $rawRow['id'],
                        'id_order_pin' => $rawRow['id_order_pin'] ?? null,
                        'id_pin' => $idPin,
                        'member_network_id' => ! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : 1,
                        'owner_id' => ! empty($rawRow['id_member_owner']) ? (int) $rawRow['id_member_owner'] : (! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : 1),
                        'registrar_id' => ! empty($rawRow['id_member_register']) ? (int) $rawRow['id_member_register'] : null,
                        'registered_network_id' => ! empty($rawRow['id_member_registered']) ? (int) $rawRow['id_member_registered'] : null,
                        'product_id' => ! empty($rawRow['product']) ? (int) $rawRow['product'] : (! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1),
                        'varian_id' => ! empty($rawRow['varian']) ? (int) $rawRow['varian'] : null,
                        'type' => $rawRow['type'] ?? null,
                        'bv' => $rawRow['bv'] ?? 0,
                        'amount' => $rawRow['amount'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'used' => (! empty($rawRow['used']) || ($rawRow['status'] ?? 0) == 2),
                        'description' => $rawRow['description'] ?? null,
                        'dateexpired' => $this->cleanDate($rawRow['dateexpired'] ?? null),
                        'dateused' => $this->cleanDate($rawRow['dateused'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['pins']++;
                    break;

                case 'jpb_pin_transfer':
                    $chunks['pin_transfers'][] = [
                        'id' => $rawRow['id'],
                        'sender_id' => ! empty($rawRow['id_member_sender']) ? (int) $rawRow['id_member_sender'] : (! empty($rawRow['sender_id']) ? (int) $rawRow['sender_id'] : 1),
                        'member_network_id' => ! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : (! empty($rawRow['member_network_id']) ? (int) $rawRow['member_network_id'] : 1),
                        'pin_id' => ! empty($rawRow['id_pin']) ? (int) $rawRow['id_pin'] : (! empty($rawRow['pin_id']) ? (int) $rawRow['pin_id'] : 1),
                        'product_id' => ! empty($rawRow['product']) ? (int) $rawRow['product'] : (! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1),
                        'varian_id' => ! empty($rawRow['varian']) ? (int) $rawRow['varian'] : null,
                        'product_package_id' => ! empty($rawRow['product_package']) ? (int) $rawRow['product_package'] : null,
                        'amount' => $rawRow['amount'] ?? 0,
                        'price_member' => $rawRow['price_member'] ?? 0,
                        'type' => $rawRow['type'] ?? null,
                        'description' => $rawRow['description'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['pin_transfers']++;
                    break;

                case 'jpb_product_stockist':
                    $chunks['product_stockists'][] = [
                        'id' => $rawRow['id'],
                        'product_id' => ! empty($rawRow['product']) ? (int) $rawRow['product'] : (! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1),
                        'stockist_id' => ! empty($rawRow['id_stockist']) ? (int) $rawRow['id_stockist'] : (! empty($rawRow['stockist_id']) ? (int) $rawRow['stockist_id'] : 1),
                        'price' => $rawRow['price'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['product_stockists']++;
                    break;

                case 'jpb_product_stockist_stock_opname':
                    $chunks['product_stockist_opnames'][] = [
                        'id' => $rawRow['id'],
                        'ref_number' => $rawRow['ref_number'] ?? $rawRow['ref_no'] ?? ('STK-OPN-'.$rawRow['id']),
                        'stockist_id' => ! empty($rawRow['id_stockist']) ? (int) $rawRow['id_stockist'] : (! empty($rawRow['stockist_id']) ? (int) $rawRow['stockist_id'] : 1),
                        'product_id' => ! empty($rawRow['product']) ? (int) $rawRow['product'] : (! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1),
                        'varian_id' => ! empty($rawRow['varian']) ? (int) $rawRow['varian'] : null,
                        'recorded_qty' => (int) ($rawRow['recorded_qty'] ?? ($rawRow['stock_system'] ?? 0)),
                        'actual_qty' => (int) ($rawRow['actual_qty'] ?? ($rawRow['stock_real'] ?? 0)),
                        'difference_qty' => (int) ($rawRow['difference_qty'] ?? ($rawRow['diff'] ?? 0)),
                        'price' => $rawRow['price'] ?? 0,
                        'total' => $rawRow['total'] ?? 0,
                        'type' => in_array(strtoupper($rawRow['type'] ?? ''), ['IN', 'OUT']) ? strtoupper($rawRow['type']) : 'IN',
                        'memo' => $rawRow['memo'] ?? null,
                        'description' => $rawRow['description'] ?? null,
                        'date_trans' => $this->cleanDate($rawRow['date_trans'] ?? ($rawRow['datecreated'] ?? null)) ?: date('Y-m-d'),
                        'created_by' => $rawRow['created_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['product_stockist_opnames']++;
                    break;

                case 'jpb_purchase_order':
                    $poInv = $rawRow['invoice'] ?? ('PO-'.$rawRow['id']);
                    if (isset($uniqueLookups['invoice'][$poInv])) {
                        $poInv = $poInv.'-'.$rawRow['id'];
                    }
                    $uniqueLookups['invoice'][$poInv] = true;

                    $chunks['purchase_orders'][] = [
                        'id' => $rawRow['id'],
                        'invoice' => $poInv,
                        'supplier_id' => ! empty($rawRow['id_supplier']) ? (int) $rawRow['id_supplier'] : (! empty($rawRow['supplier_id']) ? (int) $rawRow['supplier_id'] : 1),
                        'ref_number_order' => $rawRow['ref_number_order'] ?? null,
                        'ref_number_delivery' => $rawRow['ref_number_delivery'] ?? null,
                        'products' => $this->safeJson($rawRow['products'] ?? null),
                        'total_qty' => (int) ($rawRow['total_qty'] ?? 0),
                        'subtotal' => $rawRow['subtotal'] ?? 0,
                        'shipping' => $rawRow['shipping'] ?? 0,
                        'unique' => ! empty($rawRow['unique']) ? (int) $rawRow['unique'] : null,
                        'pph' => $rawRow['pph'] ?? 0,
                        'ppn' => $rawRow['ppn'] ?? 0,
                        'discount' => $rawRow['discount'] ?? 0,
                        'down_payment' => $rawRow['down_payment'] ?? 0,
                        'total_payment' => $rawRow['total_payment'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'memo' => $rawRow['memo'] ?? null,
                        'description' => $rawRow['description'] ?? null,
                        'trans_date' => $this->cleanDate($rawRow['trans_date'] ?? null) ?: date('Y-m-d'),
                        'due_date' => $this->cleanDate($rawRow['due_date'] ?? null),
                        'delivery_date' => $this->cleanDate($rawRow['delivery_date'] ?? null),
                        'dateexpired' => $this->cleanDate($rawRow['dateexpired'] ?? null),
                        'dateconfirmed' => $this->cleanDate($rawRow['dateconfirmed'] ?? null),
                        'created_by' => $rawRow['created_by'] ?? null,
                        'modified_by' => $rawRow['modified_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['purchase_orders']++;
                    break;

                case 'jpb_purchase_order_detail':
                    $chunks['purchase_order_details'][] = [
                        'id' => $rawRow['id'],
                        'purchase_order_id' => ! empty($rawRow['id_purchase_order']) ? (int) $rawRow['id_purchase_order'] : (! empty($rawRow['purchase_order_id']) ? (int) $rawRow['purchase_order_id'] : 1),
                        'supplier_id' => ! empty($rawRow['id_supplier']) ? (int) $rawRow['id_supplier'] : (! empty($rawRow['supplier_id']) ? (int) $rawRow['supplier_id'] : 1),
                        'product_id' => ! empty($rawRow['product']) ? (int) $rawRow['product'] : (! empty($rawRow['product_id']) ? (int) $rawRow['product_id'] : 1),
                        'varian_id' => ! empty($rawRow['id_varian']) ? (int) $rawRow['id_varian'] : (! empty($rawRow['varian']) ? (int) $rawRow['varian'] : null),
                        'type' => $rawRow['type'] ?? null,
                        'varian' => $rawRow['varian'] ?? null,
                        'image' => $rawRow['image'] ?? null,
                        'weight' => $rawRow['weight'] ?? 0,
                        'bv' => $rawRow['bv'] ?? 0,
                        'omzet' => $rawRow['omzet'] ?? 0,
                        'price' => $rawRow['price'] ?? 0,
                        'additional_cost' => $rawRow['additional_cost'] ?? 0,
                        'qty' => (int) ($rawRow['qty'] ?? 1),
                        'discount' => $rawRow['discount'] ?? 0,
                        'subtotal' => $rawRow['subtotal'] ?? 0,
                        'subtotal_bv' => $rawRow['subtotal_bv'] ?? 0,
                        'subtotal_omzet' => $rawRow['subtotal_omzet'] ?? 0,
                        'subtotal_weight' => $rawRow['subtotal_weight'] ?? 0,
                        'subtotal_cost' => $rawRow['subtotal_cost'] ?? 0,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['purchase_order_details']++;
                    break;

                    // 5. Commission & E-Wallet
                case 'jpb_bonus':
                    $bCode = ! empty($rawRow['id_bonus']) ? $rawRow['id_bonus'] : ('B'.$rawRow['id']);
                    if (isset($uniqueLookups['bonus_code'][$bCode])) {
                        $bCode = $bCode.'-'.$rawRow['id'];
                    }
                    $uniqueLookups['bonus_code'][$bCode] = true;

                    $chunks['bonuses'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'code' => $bCode,
                        'type' => (int) ($rawRow['type'] ?? 1),
                        'description' => $rawRow['desc'] ?? ($rawRow['description'] ?? null),
                        'amount' => $rawRow['amount'] ?? 0,
                        'tax' => $rawRow['tax'] ?? 0,
                        'amount_tax' => $rawRow['amount_tax'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['bonuses']++;
                    break;

                case 'jpb_bonus_generation_refs':
                    $chunks['bonus_generation_refs'][] = [
                        'id' => $rawRow['id'],
                        'bonus_id' => $rawRow['id_bonus'] ?? $rawRow['bonus_id'] ?? 1,
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'downline_id' => $rawRow['id_downline'] ?? $rawRow['downline_id'] ?? 1,
                        'gen' => (int) ($rawRow['gen'] ?? 1),
                        'type' => strtolower($rawRow['type'] ?? '') === 'perdana' ? 1 : (strtolower($rawRow['type'] ?? '') === 'ro' ? 2 : (int) ($rawRow['type'] ?? 1)),
                        'omzet' => $rawRow['omzet'] ?? 0,
                        'percentage' => $rawRow['percentage'] ?? 0,
                        'amount' => $rawRow['amount'] ?? 0,
                        'year' => (int) ($rawRow['year'] ?? date('Y')),
                        'month' => (int) ($rawRow['month'] ?? date('n')),
                        'date' => $this->cleanDate($rawRow['date'] ?? null) ?: date('Y-m-d'),
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'description' => $rawRow['description'] ?? null,
                        'dateomzet' => $this->cleanDate($rawRow['dateomzet'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['bonus_generation_refs']++;
                    break;

                case 'jpb_bonus_level_refs':
                    $chunks['bonus_level_refs'][] = [
                        'id' => $rawRow['id'],
                        'bonus_id' => $rawRow['id_bonus'] ?? $rawRow['bonus_id'] ?? 1,
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'downline_id' => $rawRow['id_downline'] ?? $rawRow['downline_id'] ?? 1,
                        'level' => (int) ($rawRow['level'] ?? 1),
                        'omzet' => $rawRow['omzet'] ?? 0,
                        'percentage' => $rawRow['percentage'] ?? 0,
                        'amount' => $rawRow['amount'] ?? 0,
                        'year' => (int) ($rawRow['year'] ?? date('Y')),
                        'month' => (int) ($rawRow['month'] ?? date('n')),
                        'date' => $this->cleanDate($rawRow['date'] ?? null) ?: date('Y-m-d'),
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'description' => $rawRow['description'] ?? null,
                        'dateomzet' => $this->cleanDate($rawRow['dateomzet'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['bonus_level_refs']++;
                    break;

                case 'jpb_bonus_matching_refs':
                    $chunks['bonus_matching_refs'][] = [
                        'id' => $rawRow['id'],
                        'bonus_pairing_id' => $rawRow['id_bonus_pairing'] ?? $rawRow['bonus_pairing_id'] ?? 1,
                        'bonus_matching_id' => $rawRow['id_bonus_matching'] ?? $rawRow['bonus_matching_id'] ?? 1,
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'downline_id' => $rawRow['id_downline'] ?? $rawRow['downline_id'] ?? 1,
                        'gen' => (int) ($rawRow['gen'] ?? 1),
                        'percentage' => $rawRow['percentage'] ?? 0,
                        'bonus' => $rawRow['bonus'] ?? 0,
                        'amount' => $rawRow['amount'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'description' => $rawRow['description'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['bonus_matching_refs']++;
                    break;

                case 'jpb_ewallet':
                    $ewkey = ($rawRow['source'] ?? 'bonus').'-'.($rawRow['id_source'] ?? 0).'-'.($rawRow['type'] ?? 'IN');
                    if (isset($uniqueLookups['ewallet'][$ewkey])) {
                        break;
                    }
                    $uniqueLookups['ewallet'][$ewkey] = true;

                    $chunks['ewallets'][] = [
                        'id' => $rawRow['id'],
                        'member_id' => $rawRow['id_member'] ?? $rawRow['member_id'] ?? 1,
                        'source' => $rawRow['source'] ?? 'bonus',
                        'source_id' => (int) ($rawRow['id_source'] ?? 0),
                        'category' => in_array($rawRow['category'] ?? '', ['commission', 'profit']) ? $rawRow['category'] : 'commission',
                        'nominal' => $rawRow['nominal'] ?? 0,
                        'percent' => $rawRow['percent'] ?? 100,
                        'autoro' => $rawRow['autoro'] ?? 0,
                        'tax' => $rawRow['tax'] ?? 0,
                        'amount' => $rawRow['amount'] ?? 0,
                        'type' => in_array(strtoupper($rawRow['type'] ?? ''), ['IN', 'OUT']) ? strtoupper($rawRow['type']) : 'IN',
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'description' => $rawRow['description'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['ewallets']++;
                    break;

                case 'jpb_ewallet_transfer':
                    $trxId = ! empty($rawRow['id_transfer']) ? $rawRow['id_transfer'] : ('TRX-'.$rawRow['id']);
                    if (isset($uniqueLookups['transfer_code'][$trxId])) {
                        $trxId = $trxId.'-'.$rawRow['id'];
                    }
                    $uniqueLookups['transfer_code'][$trxId] = true;

                    $chunks['ewallet_transfers'][] = [
                        'id' => $rawRow['id'],
                        'id_transfer' => $trxId,
                        'sender_network_id' => $rawRow['id_member_sender'] ?? $rawRow['sender_id'] ?? 1,
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_id'] ?? 1,
                        'nominal' => $rawRow['nominal'] ?? 0,
                        'nominal_receipt' => $rawRow['nominal_receipt'] ?? 0,
                        'admin_fund' => $rawRow['admin_fund'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'description' => $rawRow['description'] ?? null,
                        'created_by' => $rawRow['created_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['ewallet_transfers']++;
                    break;

                case 'jpb_withdraw':
                    $chunks['withdrawals'][] = [
                        'id' => $rawRow['id'],
                        'member_id' => $rawRow['id_member'] ?? $rawRow['member_id'] ?? 1,
                        'type' => $rawRow['type'] ?? null,
                        'bank_id' => ! empty($rawRow['bank_id']) ? (int) $rawRow['bank_id'] : (! empty($rawRow['bank']) ? (int) $rawRow['bank'] : null),
                        'bank_type' => $rawRow['bank_type'] ?? null,
                        'bank_code' => $rawRow['bank_code'] ?? null,
                        'bank_name' => $rawRow['bank_name'] ?? null,
                        'bank_account' => $rawRow['bank_account'] ?? ($rawRow['bill'] ?? null),
                        'bank_account_name' => $rawRow['bank_account_name'] ?? ($rawRow['bill_name'] ?? null),
                        'nominal' => $rawRow['nominal'] ?? 0,
                        'nominal_receipt' => $rawRow['nominal_receipt'] ?? 0,
                        'tax' => $rawRow['tax'] ?? 0,
                        'auto_ro' => $rawRow['auto_ro'] ?? 0,
                        'admin_fund' => $rawRow['admin_fund'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'flip_id' => $rawRow['flip_id'] ?? null,
                        'linkita_inquiry' => $rawRow['linkita_inquiry'] ?? null,
                        'inquiry_status' => $rawRow['inquiry_status'] ?? null,
                        'linkita_pay' => $rawRow['linkita_pay'] ?? null,
                        'payment_status' => $rawRow['payment_status'] ?? null,
                        'dateconfirmed' => $this->cleanDate($rawRow['dateconfirmed'] ?? null),
                        'confirm_by' => $rawRow['confirm_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['withdrawals']++;
                    break;

                    // 6. Shop & E-Commerce
                case 'jpb_cart':
                    $cartId = is_numeric($rawRow['id'] ?? null) ? (int) $rawRow['id'] : null;
                    $cartCode = ! empty($rawRow['cart_code']) ? $rawRow['cart_code'] : ($rawRow['id'] ?? null);

                    $cartData = [
                        'cart_code' => $cartCode,
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_id'] ?? 1,
                        'type' => $rawRow['type'] ?? 'shop_order',
                        'buyable_type' => $rawRow['buyable_type'] ?? null,
                        'buyable_id' => ! empty($rawRow['buyable_id']) ? (int) $rawRow['buyable_id'] : null,
                        'varian_id' => ! empty($rawRow['varian_id']) ? (int) $rawRow['varian_id'] : null,
                        'name' => $rawRow['name'] ?? 'Item',
                        'weight' => $rawRow['weight'] ?? 0,
                        'bv' => $rawRow['bv'] ?? 0,
                        'price' => $rawRow['price'] ?? 0,
                        'qty' => (int) ($rawRow['qty'] ?? 1),
                        'subtotal' => $rawRow['subtotal'] ?? 0,
                        'total_item' => (int) ($rawRow['total_item'] ?? 1),
                        'items' => $this->safeJson($rawRow['items'] ?? null),
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];

                    if ($cartId !== null) {
                        $cartData['id'] = $cartId;
                    }

                    $chunks['carts'][] = $cartData;
                    $counts['carts']++;
                    break;

                case 'jpb_shop_order':
                    $inv = ! empty($rawRow['invoice']) ? $rawRow['invoice'] : ('INV-'.$rawRow['id']);
                    if (isset($uniqueLookups['invoice'][$inv])) {
                        $inv = $inv.'-'.$rawRow['id'];
                    }
                    $uniqueLookups['invoice'][$inv] = true;

                    $chunks['shop_orders'][] = [
                        'id' => $rawRow['id'],
                        'invoice' => $inv,
                        'member_network_id' => ! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : null,
                        'stockist_id' => ! empty($rawRow['id_stockist']) ? (int) $rawRow['id_stockist'] : null,
                        'type_order' => $rawRow['type_order'] ?? 'shop',
                        'products' => $this->safeJson($rawRow['products'] ?? null),
                        'meta' => $this->safeJson($rawRow['meta'] ?? null),
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'point_sponsor' => $rawRow['point_sponsor'] ?? 0,
                        'point_pairing' => $rawRow['point_pairing'] ?? 0,
                        'point_reward' => $rawRow['point_reward'] ?? 0,
                        'point_stockist' => $rawRow['point_stockist'] ?? 0,
                        'total_bv' => $rawRow['total_bv'] ?? 0,
                        'total_omzet' => $rawRow['total_omzet'] ?? 0,
                        'total_qty' => (int) ($rawRow['total_qty'] ?? 0),
                        'subtotal' => $rawRow['subtotal'] ?? 0,
                        'shipping' => $rawRow['shipping'] ?? 0,
                        'unique' => ! empty($rawRow['unique']) ? (int) $rawRow['unique'] : null,
                        'discount' => $rawRow['discount'] ?? 0,
                        'shipping_discount' => $rawRow['shipping_discount'] ?? 0,
                        'fee' => $rawRow['fee'] ?? 0,
                        'ppn' => $rawRow['ppn'] ?? 0,
                        'handling_fee' => $rawRow['handling_fee'] ?? 0,
                        'insurance_fee' => $rawRow['insurance_fee'] ?? 0,
                        'additional_cost' => $rawRow['additional_cost'] ?? 0,
                        'autoro' => (($rawRow['autoro'] ?? 0) == 1),
                        'total_checkout' => $rawRow['total_checkout'] ?? 0,
                        'total_payment' => $rawRow['total_payment'] ?? 0,
                        'payment_remain' => $rawRow['payment_remain'] ?? 0,
                        'voucher' => $rawRow['voucher'] ?? null,
                        'saldo_eproduct' => $rawRow['saldo_eproduct'] ?? 0,
                        'saldo_eshipping' => $rawRow['saldo_eshipping'] ?? 0,
                        'saldo_eshipping_subsidy' => $rawRow['saldo_eshipping_subsidy'] ?? 0,
                        'payment_method' => $rawRow['payment_method'] ?? null,
                        'payment_shipping_method' => $rawRow['payment_shipping_method'] ?? null,
                        'payment_shipping_status' => $rawRow['payment_shipping_status'] ?? null,
                        'bank_code' => $rawRow['bank_code'] ?? null,
                        'account_number' => $rawRow['account_number'] ?? null,
                        'shipping_method' => $rawRow['shipping_method'] ?? null,
                        'name' => $rawRow['name'] ?? 'Member',
                        'phone' => $rawRow['phone'] ?? '-',
                        'email' => $rawRow['email'] ?? null,
                        'province_id' => ! empty($rawRow['province_id']) ? (int) $rawRow['province_id'] : null,
                        'district_id' => ! empty($rawRow['district_id']) ? (int) $rawRow['district_id'] : null,
                        'subdistrict_id' => ! empty($rawRow['subdistrict_id']) ? (int) $rawRow['subdistrict_id'] : null,
                        'province' => $rawRow['province'] ?? null,
                        'district' => $rawRow['district'] ?? null,
                        'subdistrict' => $rawRow['subdistrict'] ?? null,
                        'village' => $rawRow['village'] ?? null,
                        'address' => $rawRow['address'] ?? '-',
                        'postcode' => $rawRow['postcode'] ?? null,
                        'courier' => $rawRow['courier'] ?? null,
                        'service' => $rawRow['service'] ?? null,
                        'weight' => $rawRow['weight'] ?? 0,
                        'resi' => $rawRow['resi'] ?? null,
                        'pin_transfer' => $rawRow['pin_transfer'] ?? null,
                        'description' => $rawRow['description'] ?? null,
                        'created_by' => $rawRow['created_by'] ?? null,
                        'confirmed_by' => $rawRow['confirmed_by'] ?? null,
                        'modified_by' => $rawRow['modified_by'] ?? null,
                        'dateconfirmed' => $this->cleanDate($rawRow['dateconfirmed'] ?? null),
                        'datestockistconfirmed' => $this->cleanDate($rawRow['datestockistconfirmed'] ?? null),
                        'dateexpired' => $this->cleanDate($rawRow['dateexpired'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['shop_orders']++;
                    break;

                case 'jpb_shop_order_detail':
                    $chunks['shop_order_details'][] = [
                        'id' => $rawRow['id'],
                        'shop_order_id' => $rawRow['id_shop_order'] ?? $rawRow['shop_order_id'] ?? 1,
                        'member_network_id' => ! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : null,
                        'product_package_id' => ! empty($rawRow['product_package']) ? (int) $rawRow['product_package'] : null,
                        'product_id' => ! empty($rawRow['product']) ? (int) $rawRow['product'] : null,
                        'varian_id' => ! empty($rawRow['varian']) ? (int) $rawRow['varian'] : null,
                        'type' => $rawRow['type'] ?? null,
                        'weight' => $rawRow['weight'] ?? 0,
                        'point' => $rawRow['point'] ?? 0,
                        'bv' => $rawRow['bv'] ?? 0,
                        'omzet' => $rawRow['omzet'] ?? 0,
                        'price' => $rawRow['price'] ?? 0,
                        'price_cart' => $rawRow['price_cart'] ?? 0,
                        'additional_cost' => $rawRow['additional_cost'] ?? 0,
                        'qty' => (int) ($rawRow['qty'] ?? 1),
                        'discount' => $rawRow['discount'] ?? 0,
                        'subtotal' => $rawRow['subtotal'] ?? 0,
                        'subtotal_bv' => $rawRow['subtotal_bv'] ?? 0,
                        'subtotal_omzet' => $rawRow['subtotal_omzet'] ?? 0,
                        'subtotal_weight' => $rawRow['subtotal_weight'] ?? 0,
                        'subtotal_cost' => $rawRow['subtotal_cost'] ?? 0,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['shop_order_details']++;
                    break;

                case 'jpb_ro':
                    $chunks['ros'][] = [
                        'id' => $rawRow['id'],
                        'invoice' => $rawRow['invoice'] ?? ('RO-'.$rawRow['id']),
                        'activator_id' => ! empty($rawRow['id_activator']) ? (int) $rawRow['id_activator'] : 1,
                        'member_network_id' => ! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : 1,
                        'stockist_id' => ! empty($rawRow['id_stockist']) ? (int) $rawRow['id_stockist'] : null,
                        'shop_order_id' => ! empty($rawRow['id_shop_order']) ? (int) $rawRow['id_shop_order'] : 1,
                        'type' => $rawRow['type'] ?? null,
                        'pins' => $this->safeJson($rawRow['pins'] ?? null),
                        'meta' => $this->safeJson($rawRow['meta'] ?? null),
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'point' => $rawRow['point'] ?? 0,
                        'total_bv' => $rawRow['total_bv'] ?? 0,
                        'total_qty' => (int) ($rawRow['total_qty'] ?? 0),
                        'subtotal' => $rawRow['subtotal'] ?? 0,
                        'unique' => ! empty($rawRow['unique']) ? (int) $rawRow['unique'] : null,
                        'shipping' => $rawRow['shipping'] ?? 0,
                        'shipping_discount' => $rawRow['shipping_discount'] ?? 0,
                        'discount' => $rawRow['discount'] ?? 0,
                        'voucher' => $rawRow['voucher'] ?? null,
                        'fee' => $rawRow['fee'] ?? 0,
                        'ppn' => $rawRow['ppn'] ?? 0,
                        'autoro' => (($rawRow['autoro'] ?? 0) == 1),
                        'total_checkout' => $rawRow['total_checkout'] ?? 0,
                        'total_payment' => $rawRow['total_payment'] ?? 0,
                        'total_omzet' => $rawRow['total_omzet'] ?? 0,
                        'shipping_method' => $rawRow['shipping_method'] ?? null,
                        'description' => $rawRow['desc'] ?? ($rawRow['description'] ?? null),
                        'dateconfirmed' => $this->cleanDate($rawRow['dateconfirmed'] ?? null),
                        'confirmed_by' => $rawRow['confirmed_by'] ?? null,
                        'modified_by' => $rawRow['modified_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['ros']++;
                    break;

                case 'jpb_auto_ro':
                case 'jpb_auto_ro_out':
                    $isOut = ($oldTable === 'jpb_auto_ro_out');
                    $chunks['auto_ros'][] = [
                        'id' => $isOut ? ((int) $rawRow['id'] + 1000000) : (int) $rawRow['id'],
                        'member_network_id' => ! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : 1,
                        'source' => $rawRow['source'] ?? ($isOut ? 'shop' : 'bonus'),
                        'source_id' => (int) ($rawRow['id_source'] ?? 0),
                        'nominal' => $rawRow['nominal'] ?? ($rawRow['amount'] ?? 0),
                        'percent' => $rawRow['percent'] ?? 0,
                        'amount' => $rawRow['amount'] ?? 0,
                        'type' => $isOut ? 'OUT' : 'IN',
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'description' => $rawRow['description'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['auto_ros']++;
                    break;

                case 'jpb_eproduct':
                    $chunks['eproduct_mutations'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => ! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : 1,
                        'source' => $rawRow['source'] ?? 'purchase',
                        'source_id' => (int) ($rawRow['id_source'] ?? 0),
                        'category' => in_array($rawRow['category'] ?? '', ['product', 'shipping']) ? $rawRow['category'] : 'product',
                        'amount' => $rawRow['amount'] ?? ($rawRow['nominal'] ?? 0),
                        'type' => in_array(strtoupper($rawRow['type'] ?? ''), ['IN', 'OUT']) ? strtoupper($rawRow['type']) : 'IN',
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'description' => $rawRow['description'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                    ];
                    $counts['eproduct_mutations']++;
                    break;

                    // 7. Reward & Rank
                case 'jpb_reward_config':
                    $chunks['reward_configs'][] = [
                        'id' => $rawRow['id'],
                        'type' => $rawRow['type'] ?? 'regular',
                        'reward' => $rawRow['reward'] ?? 'Reward',
                        'nominal' => $rawRow['nominal'] ?? 0,
                        'point' => $rawRow['point'] ?? 0,
                        'packages' => $this->safeJson($rawRow['packages'] ?? null),
                        'rank_id' => $this->parseRankId($rawRow['rank'] ?? null),
                        'message' => $rawRow['message'] ?? null,
                        'start_date' => $this->cleanDate($rawRow['start_date'] ?? null),
                        'end_date' => $this->cleanDate($rawRow['end_date'] ?? null),
                        'is_lifetime' => (($rawRow['is_lifetime'] ?? 0) == 1),
                        'is_trip' => (strtolower($rawRow['type'] ?? '') === 'trip' || ($rawRow['is_trip'] ?? 0) == 1),
                        'is_active' => (($rawRow['is_active'] ?? 1) == 1),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['reward_configs']++;
                    break;

                case 'jpb_reward':
                    $chunks['member_rewards'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'reward_config_id' => ! empty($rawRow['id_reward']) ? (int) $rawRow['id_reward'] : (! empty($rawRow['reward_config_id']) ? (int) $rawRow['reward_config_id'] : 1),
                        'type' => $rawRow['type'] ?? null,
                        'point_qualified' => $rawRow['point_qualified'] ?? 0,
                        'point_left' => $rawRow['point_left'] ?? 0,
                        'point_right' => $rawRow['point_right'] ?? 0,
                        'rank_id' => $this->parseRankId($rawRow['rank'] ?? null),
                        'message' => $rawRow['message'] ?? null,
                        'nominal' => $rawRow['nominal'] ?? 0,
                        'nominal_receipt' => $rawRow['nominal_receipt'] ?? 0,
                        'admin_fund' => $rawRow['admin_fund'] ?? 0,
                        'tax' => $rawRow['tax'] ?? 0,
                        'bank_id' => ! empty($rawRow['bank_id']) ? (int) $rawRow['bank_id'] : (! empty($rawRow['bank']) ? (int) $rawRow['bank'] : null),
                        'bank_code' => $rawRow['bank_code'] ?? null,
                        'bank_account' => $rawRow['bank_account'] ?? ($rawRow['bill'] ?? null),
                        'bank_account_name' => $rawRow['bank_account_name'] ?? ($rawRow['bill_name'] ?? null),
                        'is_trip' => (($rawRow['is_trip'] ?? 0) == 1),
                        'claim' => (($rawRow['claim'] ?? 0) == 1),
                        'flip_id' => $rawRow['flip_id'] ?? null,
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'inquiry_status' => $rawRow['inquiry_status'] ?? null,
                        'dateconfirmed' => $this->cleanDate($rawRow['dateconfirmed'] ?? null),
                        'dateclaimed' => $this->cleanDate($rawRow['dateclaimed'] ?? null),
                        'confirm_by' => $rawRow['confirm_by'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_rewards']++;
                    break;

                case 'jpb_reward_point':
                    $chunks['member_reward_points'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'package' => $rawRow['package'] ?? null,
                        'type' => $rawRow['type'] ?? null,
                        'bv' => $rawRow['bv'] ?? 0,
                        'point' => $rawRow['point'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_reward_points']++;
                    break;

                case 'jpb_ro_point_monthly':
                    $chunks['member_ro_point_monthlies'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => $rawRow['id_member'] ?? $rawRow['member_network_id'] ?? 1,
                        'year' => (int) ($rawRow['year'] ?? date('Y')),
                        'month' => (int) ($rawRow['month'] ?? date('n')),
                        'total_bv' => $rawRow['total_bv'] ?? ($rawRow['bv'] ?? 0),
                        'total_bv_left' => $rawRow['total_bv_left'] ?? 0,
                        'total_bv_right' => $rawRow['total_bv_right'] ?? 0,
                        'kurs_bv_point' => $rawRow['kurs_bv_point'] ?? 1,
                        'point' => $rawRow['point'] ?? 0,
                        'point_left' => $rawRow['point_left'] ?? 0,
                        'point_right' => $rawRow['point_right'] ?? 0,
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'description' => $rawRow['description'] ?? null,
                        'date' => $this->cleanDate($rawRow['date'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_ro_point_monthlies']++;
                    break;

                case 'jpb_ranks':
                case 'jpb_rank_history':
                    $chunks['member_rank_histories'][] = [
                        'id' => $rawRow['id'],
                        'member_network_id' => $rawRow['id_member'] ?? ($rawRow['member_network_id'] ?? 1),
                        'rank_id' => $this->parseRankId($rawRow['rank_id'] ?? ($rawRow['rank'] ?? null)) ?? 1,
                        'downline_count' => (int) ($rawRow['downline_count'] ?? ($rawRow['downline'] ?? 0)),
                        'point_left' => (int) ($rawRow['point_left'] ?? 0),
                        'point_right' => (int) ($rawRow['point_right'] ?? 0),
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['member_rank_histories']++;
                    break;

                    // 8. Staff & System
                case 'jpb_staff':
                    $stfUser = ! empty($rawRow['username']) ? trim($rawRow['username']) : ('staff'.$rawRow['id']);
                    $stfEmail = ! empty($rawRow['email']) ? trim($rawRow['email']) : ($stfUser.'@jpbuana.com');

                    $chunks['staffs'][] = [
                        'id' => $rawRow['id'],
                        'username' => $stfUser,
                        'name' => $rawRow['name'] ?? ('Staff '.$rawRow['id']),
                        'email' => $stfEmail,
                        'password' => $rawRow['password'] ?? bcrypt('12345678'),
                        'phone' => $this->normalizePhone($rawRow['phone'] ?? null),
                        'image' => $rawRow['photo'] ?? ($rawRow['image'] ?? null),
                        'status' => (int) ($rawRow['status'] ?? 1),
                        'last_login' => $this->cleanDate($rawRow['last_login'] ?? null),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['staffs']++;

                    if ($roleLookup !== null && ! empty($roleLookup['staff'])) {
                        $chunks['model_has_roles'][] = [
                            'role_id' => $roleLookup['staff'],
                            'model_type' => 'App\Modules\Staff\Infrastructure\Eloquent\ModelStaff',
                            'model_id' => $rawRow['id'],
                        ];
                        $counts['model_has_roles']++;
                    }

                    // Assign numeric permissions to staff in model_has_permissions
                    if ($permissionLookup !== null && ! empty($rawRow['role'])) {
                        $accessList = @unserialize($rawRow['role']);
                        if (is_array($accessList)) {
                            foreach ($accessList as $accNum) {
                                $accNum = (int) $accNum;
                                if (isset($permissionLookup[$accNum])) {
                                    $chunks['model_has_permissions'][] = [
                                        'permission_id' => $permissionLookup[$accNum],
                                        'model_type' => 'App\Modules\Staff\Infrastructure\Eloquent\ModelStaff',
                                        'model_id' => $rawRow['id'],
                                    ];
                                    $counts['model_has_permissions']++;
                                }
                            }
                        }
                    }
                    break;

                case 'jpb_news':
                    $newsStatus = 1;
                    if (isset($rawRow['status'])) {
                        $sLower = strtolower($rawRow['status']);
                        if ($sLower === 'draft' || $sLower === 'delete' || $sLower === '0') {
                            $newsStatus = 0;
                        }
                    }
                    $newsSlug = ! empty($rawRow['slug']) ? $rawRow['slug'] : Str::slug($rawRow['title'] ?? ('news-'.$rawRow['id']));
                    if (isset($slugLookups['news'][$newsSlug])) {
                        $newsSlug = $newsSlug.'-'.$rawRow['id'];
                    }
                    $slugLookups['news'][$newsSlug] = true;

                    $chunks['news'][] = [
                        'id' => $rawRow['id'],
                        'title' => $rawRow['title'] ?? ('News '.$rawRow['id']),
                        'slug' => $newsSlug,
                        'content' => $rawRow['content'] ?? '',
                        'mime_type' => $rawRow['mime_type'] ?? null,
                        'url' => $rawRow['url'] ?? null,
                        'status' => $newsStatus,
                        'created_at' => $this->cleanDate($rawRow['created_at'] ?? ($rawRow['datecreated'] ?? null)) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['updated_at'] ?? ($rawRow['datemodified'] ?? null)) ?: now(),
                    ];
                    $counts['news']++;
                    break;

                case 'jpb_notification':
                    $notifSlug = ! empty($rawRow['slug']) ? $rawRow['slug'] : Str::slug($rawRow['name'] ?? ('notif-'.$rawRow['id']));
                    if (isset($slugLookups['notification_templates'][$notifSlug])) {
                        $notifSlug = $notifSlug.'-'.$rawRow['id'];
                    }
                    $slugLookups['notification_templates'][$notifSlug] = true;

                    $chunks['notification_templates'][] = [
                        'id' => $rawRow['id'],
                        'name' => $rawRow['name'] ?? ('Notification '.$rawRow['id']),
                        'slug' => $notifSlug,
                        'title' => $rawRow['title'] ?? '',
                        'content' => $rawRow['content'] ?? '',
                        'status' => (($rawRow['status'] ?? 1) == 1) ? 1 : 0,
                        'type' => $rawRow['type'] ?? null,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['notification_templates']++;
                    break;

                case 'jpb_video':
                    $chunks['videos'][] = [
                        'id' => $rawRow['id'],
                        'title' => $rawRow['title'] ?? ('Video '.$rawRow['id']),
                        'url' => $rawRow['url'] ?? '',
                        'sequence' => (int) ($rawRow['sequence'] ?? 0),
                        'image' => $rawRow['image'] ?? null,
                        'status' => (($rawRow['status'] ?? 1) == 1) ? 1 : 0,
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => $this->cleanDate($rawRow['datemodified'] ?? null) ?: now(),
                    ];
                    $counts['videos']++;
                    break;

                case 'jpb_otp':
                    $chunks['otps'][] = [
                        'id' => $rawRow['id'] ?? ($rawRow['id_otp'] ?? null),
                        'member_id' => ! empty($rawRow['member_id']) ? (int) $rawRow['member_id'] : (! empty($rawRow['id_member']) ? (int) $rawRow['id_member'] : (! empty($rawRow['user_id']) ? (int) $rawRow['user_id'] : null)),
                        'email' => $rawRow['email'] ?? null,
                        'type' => $rawRow['type'] ?? 'email',
                        'ip_address' => $rawRow['ip_address'] ?? ($rawRow['ip'] ?? null),
                        'platform' => $rawRow['platform'] ?? null,
                        'browser' => $rawRow['browser'] ?? null,
                        'agent' => $rawRow['agent'] ?? null,
                        'user_agent' => $rawRow['user_agent'] ?? null,
                        'otp' => $rawRow['otp'] ?? '000000',
                        'expiration' => $this->cleanDate($rawRow['expiration'] ?? null) ?: now()->addMinutes(15),
                        'status' => (int) ($rawRow['status'] ?? 0),
                        'created_at' => $this->cleanDate($rawRow['datecreated'] ?? null) ?: now(),
                        'updated_at' => now(),
                    ];
                    $counts['otps']++;
                    break;
            }

            // Flush chunks if limit reached
            foreach ($chunks as $targetTable => $rows) {
                if (count($rows) >= $chunkSize) {
                    // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    Schema::disableForeignKeyConstraints();
                    if (DB::getSchemaBuilder()->hasTable($targetTable)) {
                        DB::table($targetTable)->insertOrIgnore($rows);
                    }
                    // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    Schema::enableForeignKeyConstraints();
                    $chunks[$targetTable] = [];
                }
            }
        }

        fclose($handle);

        // Flush remaining chunks
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::disableForeignKeyConstraints();
        foreach ($chunks as $targetTable => $rows) {
            if (count($rows) > 0 && DB::getSchemaBuilder()->hasTable($targetTable)) {
                DB::table($targetTable)->insertOrIgnore($rows);
            }
        }
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Schema::enableForeignKeyConstraints();

        // 1. Sinkronisasi saldo akumulasi members.ewallet_balance dari riwayat transaksi ewallets
        if (DB::getSchemaBuilder()->hasTable('members') && DB::getSchemaBuilder()->hasTable('ewallets')) {
            DB::statement("
                UPDATE members
                SET ewallet_balance = COALESCE((
                    SELECT SUM(CASE WHEN type = 'IN' THEN amount ELSE -amount END)
                    FROM ewallets
                    WHERE ewallets.member_id = members.id
                      AND ewallets.status = 1
                ), 0)
            ");
        }

        // 2. Sinkronisasi saldo akumulasi member_networks.autoro_balance dari auto_ros
        if (DB::getSchemaBuilder()->hasTable('member_networks') && DB::getSchemaBuilder()->hasTable('auto_ros')) {
            DB::statement("
                UPDATE member_networks
                SET autoro_balance = COALESCE((
                    SELECT SUM(CASE WHEN type = 'IN' THEN amount ELSE -amount END)
                    FROM auto_ros
                    WHERE auto_ros.member_network_id = member_networks.id
                      AND auto_ros.status = 1
                ), 0)
            ");
        }

        // 3. Inisialisasi record member_reports untuk setiap member_networks jika belum ada
        if (DB::getSchemaBuilder()->hasTable('member_networks') && DB::getSchemaBuilder()->hasTable('member_reports')) {
            DB::statement("
                INSERT OR IGNORE INTO member_reports (member_network_id, pairing_left, pairing_right, pairing_qualified, reward_left, reward_right, total_omzet, total_ro_omzet, total_ro_point, last_ro_omzet, last_ro_point, created_at, updated_at)
                SELECT id, 0, 0, 0, 0, 0, total_omzet, 0, 0, 0, 0, datetime('now'), datetime('now')
                FROM member_networks
            ");
        }

        if (isset($this->command)) {
            foreach ($counts as $table => $count) {
                if ($count > 0) {
                    $this->command->info("Berhasil mengimpor {$count} record ke tabel '{$table}'.");
                }
            }
        }
    }

    private function cleanDate(?string $dateStr): ?string
    {
        if (! $dateStr || $dateStr === '0000-00-00 00:00:00' || $dateStr === '0000-00-00' || str_starts_with($dateStr, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($dateStr)->toDateTimeString();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $phone = preg_replace('/[^0-9+]/', '', $phone);

        return $phone ?: null;
    }

    private function safeJson($val): ?string
    {
        if (empty($val)) {
            return null;
        }

        $decoded = json_decode($val, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $val;
        }

        $unserialized = @unserialize($val);
        if ($unserialized !== false) {
            return json_encode($unserialized);
        }

        return null;
    }

    private function parseRankId(?string $rank): ?int
    {
        if ($rank === null || $rank === '') {
            return null;
        }

        $r = strtolower(trim($rank));

        return match ($r) {
            'star', 'starmember', '2' => 2,
            'member', '1' => 1,
            '0' => 0,
            default => is_numeric($rank) ? (int) $rank : null,
        };
    }
}
