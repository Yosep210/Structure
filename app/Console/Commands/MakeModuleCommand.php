<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module 
                            {name : Nama modul yang akan dibuat (misal: Member, Order)} 
                            {--path= : Folder root tempat modul disimpan (opsional, default: auto-detect composer.json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat struktur folder & file dasar modul DDD baru (Otomatis mendeteksi composer.json PSR-4)';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = trim($this->argument('name'));
        $moduleName = Str::studly($name);
        $moduleSlug = Str::kebab($name);

        // Auto-detect path & namespace from composer.json
        $mapping = $this->getComposerMapping($this->option('path'));
        $basePathOption = $mapping['path'];
        $baseNamespace = $mapping['namespace'];

        $modulePath = base_path($basePathOption.'/'.$moduleName);

        if ($this->files->exists($modulePath)) {
            $this->error("Modul [{$moduleName}] sudah ada di path [{$basePathOption}/{$moduleName}]!");

            return self::FAILURE;
        }

        $this->info("Membuat modul DDD: [{$moduleName}] di [{$basePathOption}/{$moduleName}]...");

        // List of directories to create inside the module
        $directories = [
            // Domain Layer
            $modulePath.'/Domain/Entities',
            $modulePath.'/Domain/Enums',
            $modulePath.'/Domain/Events',
            $modulePath.'/Domain/Exceptions',
            $modulePath.'/Domain/Interfaces',
            $modulePath.'/Domain/Services',
            $modulePath.'/Domain/ValueObjects',

            // Application Layer
            $modulePath.'/Application/DTOs',
            $modulePath.'/Application/Queries',
            $modulePath.'/Application/UseCases',

            // Http Layer
            $modulePath.'/Http/Controllers',
            $modulePath.'/Http/Middleware',
            $modulePath.'/Http/Requests',
            $modulePath.'/Http/Responses',
            $modulePath.'/Http/Resources',

            // Infrastructure Layer
            $modulePath.'/Infrastructure/Eloquent',
            $modulePath.'/Infrastructure/Repositories',

            // Tests Layer
            $modulePath.'/Tests',
        ];

        /*
         |--------------------------------------------------------------------------
         | PANDUAN FUNGSI STRUKTUR DIREKTORI MODUL DDD
         |--------------------------------------------------------------------------
         |
         | 1. DOMAIN LAYER (Aturan Bisnis Inti - Bebas Framework)
         |    - Entities/     : Objek bisnis utama dengan identitas unik & getter/setter domain (--entity)
         |    - Enums/        : Nilai konstan berlabel & berwarna misal Status/Tipe (--enum)
         |    - Events/       : Kejadian penting yang terjadi di domain misal MemberRegistered (--event)
         |    - Exceptions/   : Penanganan pesan kesalahan aturan bisnis & toast Inertia (--exception)
         |    - Interfaces/   : Kontrak pemisahan pembacaan & penulisan data Get & Post (--interface-get, --interface-post)
         |    - Services/     : Kalkulator rumus atau aturan bisnis kompleks multi-entitas (--service)
         |    - ValueObjects/ : Objek nilai tanpa identitas yang immutable & tervalidasi (--vo)
         |
         | 2. APPLICATION LAYER (Orkestrasi Alur Kerja Aplikasi)
         |    - DTOs/         : Pembawa data terstruktur dari HTTP Request ke UseCase (--dto)
         |    - Queries/      : Eksekusi pembacaan data, filter, sorting, dan paginasi (--query)
         |    - UseCases/     : Alur eksekusi logika bisnis proses (Write / Action) (--usecase, --get)
         |
         | 3. HTTP LAYER (Antarmuka Komunikasi Web & API)
         |    - Controllers/  : Single-action controller penerima request HTTP (__invoke) (--controller, --get)
         |    - Middleware/   : Filter proteksi dan verifikasi request spesifik modul (--middleware)
         |    - Requests/     : Validasi input form request otomatis dari migrasi (--request)
         |    - Responses/    : Custom response / redirect handler Responsable (--response)
         |    - Resources/    : Transformasi data JSON / Inertia props (--resource)
         |
         | 4. INFRASTRUCTURE LAYER (Akses Database & Pihak Ketiga)
         |    - Eloquent/     : Model database Laravel ActiveRecord (Model[Feature]) (--model)
         |    - Repositories/ : Implementasi akses data Eloquent yang terikat ke Interfaces (--repo, --repo-only)
         |
         | 5. TESTS LAYER (Pengujian Otomatis)
         |    - Tests/        : File automated unit & feature testing terisolasi per modul (--test)
         |
         */

        foreach ($directories as $dir) {
            $this->files->makeDirectory($dir, 0755, true, true);
            $this->files->put($dir.'/.gitkeep', '');
        }

        // Buat folder Halaman React Inertia di root resources/js/Pages/[module_lowercase]
        $reactPageFolder = strtolower($moduleSlug);
        $reactPagePath = base_path('resources/js/Pages/'.$reactPageFolder);
        if (! $this->files->exists($reactPagePath)) {
            $this->files->makeDirectory($reactPagePath, 0755, true, true);
            $this->files->put($reactPagePath.'/.gitkeep', '');
        }

        $namespacePath = $baseNamespace.'\\'.$moduleName;

        // Generate routes.php
        $this->generateRoutesFile($modulePath, $moduleName, $moduleSlug);

        $this->newLine();
        $this->info("✅ Modul [{$moduleName}] berhasil dibuat!");
        $this->comment("Lokasi Modul  : {$basePathOption}/{$moduleName}");
        $this->comment("Namespace     : {$namespacePath}");
        $this->comment("Routes File   : {$basePathOption}/{$moduleName}/Http/routes.php");
        $this->comment("React Pages   : resources/js/Pages/{$reactPageFolder}");
        $this->newLine();
        $this->info('💡 Tips: Tambahkan baris berikut di routes/web.php:');
        $this->comment("   require base_path('{$basePathOption}/{$moduleName}/Http/routes.php');");
        $this->newLine();

        return self::SUCCESS;
    }

    protected function getComposerMapping(?string $userPath = null): array
    {
        $composerFile = base_path('composer.json');
        $defaultMapping = ['path' => 'app', 'namespace' => 'App'];

        if (! $this->files->exists($composerFile)) {
            return $defaultMapping;
        }

        $composer = json_decode($this->files->get($composerFile), true);
        $psr4 = $composer['autoload']['psr-4'] ?? [];

        $systemPaths = ['app/', 'database/factories/', 'database/seeders/', 'tests/'];

        // If user explicitly provided --path, find matching namespace in composer (case-insensitive)
        if (! empty($userPath)) {
            $normalizedUserPath = strtolower(str_replace('\\', '/', trim($userPath, '/\\')).'/');
            foreach ($psr4 as $namespace => $path) {
                $normalizedPath = strtolower(str_replace('\\', '/', trim($path, '/\\')).'/');
                if ($normalizedPath === $normalizedUserPath) {
                    return $this->formatMappingResult(trim($path, '/\\'), rtrim($namespace, '\\'));
                }
            }

            return $this->formatMappingResult(trim($userPath, '/\\'), Str::studly($userPath));
        }

        // Auto-detect first custom PSR-4 mapping (not app/ or database/)
        foreach ($psr4 as $namespace => $path) {
            $normalizedPath = strtolower(str_replace('\\', '/', trim($path, '/\\')).'/');
            if (! in_array($normalizedPath, $systemPaths)) {
                return $this->formatMappingResult(trim($path, '/\\'), rtrim($namespace, '\\'));
            }
        }

        // Fallback: Check physical directories
        if ($this->files->exists(app_path('Domain'))) {
            return ['path' => 'app/Domain', 'namespace' => 'App\\Domain'];
        }

        if ($this->files->exists(app_path('Modules'))) {
            return ['path' => 'app/Modules', 'namespace' => 'App\\Modules'];
        }

        if ($this->files->exists(base_path('domain'))) {
            return ['path' => 'domain', 'namespace' => 'Domain'];
        }

        if ($this->files->exists(base_path('source'))) {
            return ['path' => 'source', 'namespace' => 'Source'];
        }

        if ($this->files->exists(base_path('modules'))) {
            return ['path' => 'modules', 'namespace' => 'Modules'];
        }

        return $defaultMapping;
    }

    protected function formatMappingResult(string $rawPath, string $namespace): array
    {
        $parts = explode('/', str_replace('\\', '/', $rawPath));
        if (count($parts) > 1 && strtolower($parts[0]) === 'app') {
            // Di dalam app/ -> Subfolder diawali huruf kapital (misal: app/Domain, app/Modules)
            $formattedPath = 'app/'.Str::studly($parts[1]);
        } else {
            // Langsung di root proyek -> Folder menggunakan huruf kecil (misal: domain, source, modules)
            $formattedPath = strtolower($parts[0]);
        }

        return [
            'path' => $formattedPath,
            'namespace' => $namespace,
        ];
    }

    protected function generateRoutesFile(string $modulePath, string $moduleName, string $moduleSlug): void
    {
        $stubPath = base_path('stubs/ddd/routes.stub');
        $targetFile = $modulePath.'/Http/routes.php';

        if (! $this->files->exists($stubPath)) {
            $this->warn("Stub file [{$stubPath}] tidak ditemukan.");

            return;
        }

        $stub = $this->files->get($stubPath);
        $content = str_replace(
            ['{{module}}', '{{moduleSlug}}'],
            [$moduleName, $moduleSlug],
            $stub
        );

        $this->files->put($targetFile, $content);
    }
}
