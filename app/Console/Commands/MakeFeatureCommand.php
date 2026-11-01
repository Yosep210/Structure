<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeFeatureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:feature 
                            {module : Nama modul target (misal: Member, Commission)} 
                            {name? : Nama fitur/model/action (opsional, default: sama dengan nama modul)} 
                            
                            {--all : [Shortcut] Generate paket lengkap dari Model sampai Controller} 
                            {--get : [Shortcut] Generate fitur Read-Only: Controller GET, UseCase GET, Resource} 
                            
                            {--model : 1. [Infrastructure] Generate Eloquent Model di Infrastructure/Eloquent/Model[Name].php} 
                            
                            {--enum : 2. [Domain] Generate Domain Enum di Domain/Enums/[Name].php} 
                            {--vo : 3. [Domain] Generate Value Object di Domain/ValueObjects/[Name].php} 
                            {--value-object : [Domain] Alias untuk --vo} 
                            {--entity : 4. [Domain] Generate Domain Entity di Domain/Entities/[Name].php} 
                            {--event : 5. [Domain] Generate Domain Event di Domain/Events/[Name].php} 
                            {--exception : 6. [Domain] Generate Domain Exception di Domain/Exceptions/[Name]Exception.php} 
                            {--service : 7. [Domain] Generate Domain Service di Domain/Services/[Name].php} 
                            {--interface-get : 8. [Domain] Generate Interface Get di Domain/Interfaces/[Name]GetInterface.php} 
                            {--interface-post : 9. [Domain] Generate Interface Post di Domain/Interfaces/[Name]PostInterface.php} 
                            
                            {--repo : 10. [Infrastructure] Generate Repository lengkap dengan Interface Get & Post} 
                            {--repository : [Infrastructure] Alias untuk --repo} 
                            {--repo-only : [Infrastructure] Generate Repository saja (tanpa Interface)} 
                            
                            {--dto : 11. [Application] Generate DTO (Spatie Data) di Application/DTOs/[Name]DTO.php} 
                            {--usecase : 12. [Application] Generate UseCase di Application/UseCases/[Name]UseCase.php} 
                            {--query : 13. [Application] Generate Query CQRS di Application/Queries/Get[Name]Query.php} 
                            
                            {--request : 14. [Http] Generate FormRequest di Http/Requests/[Name]Request.php} 
                            {--resource : 15. [Http] Generate JsonResource di Http/Resources/[Name]Resource.php} 
                            {--response : 16. [Http] Generate Http Response di Http/Responses/[Name]Response.php} 
                            {--middleware : 17. [Http] Generate Middleware di Http/Middleware/[Name].php} 
                            
                            {--controller : 18. [Http] Generate Controller di Http/Controllers/[Name]Controller.php} 
                            
                            {--test : 19. [Tests] Generate Automated Test di Tests/[Name]Test.php} 
                            
                            {--path= : Folder root tempat modul disimpan (opsional, default: auto-detect composer.json)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat file skeleton fitur/model baru di dalam modul DDD (--model, --get, --all)';

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
        $module = Str::studly(trim($this->argument('module')));
        $nameArg = $this->argument('name');
        $feature = Str::studly(trim($nameArg ?: $module));

        $mapping = $this->getComposerMapping($this->option('path'));
        $basePathOption = $mapping['path'];
        $baseNamespace = $mapping['namespace'];

        $modulePath = base_path($basePathOption.'/'.$module);

        if (! $this->files->exists($modulePath)) {
            $this->error("Modul [{$module}] tidak ditemukan di path [{$basePathOption}/{$module}]!");
            $this->comment("Gunakan [php artisan make:module {$module}] terlebih dahulu untuk membuat struktur modul.");

            return self::FAILURE;
        }

        $namespacePath = $baseNamespace.'\\'.$module;

        $createdFiles = [];

        // Single component flags OR --all
        $generateAll = $this->option('all');
        $hasIndividual = $this->hasIndividualFlags();

        // 1. Model [Infrastructure]
        if ($generateAll || $this->option('model')) {
            $createdFiles[] = $this->generateFile('model', $modulePath, $feature, $namespacePath, $module, "Infrastructure/Eloquent/Model{$feature}.php");
        }

        // 2. Enum [Domain]
        if ($this->option('enum')) {
            $createdFiles[] = $this->generateFile('enum', $modulePath, $feature, $namespacePath, $module, "Domain/Enums/{$feature}.php");
        }

        // 3. Value Object [Domain]
        if ($this->option('vo') || $this->option('value-object')) {
            $createdFiles[] = $this->generateFile('value_object', $modulePath, $feature, $namespacePath, $module, "Domain/ValueObjects/{$feature}.php");
        }

        // 4. Entity [Domain]
        if ($generateAll || $this->option('entity')) {
            $createdFiles[] = $this->generateFile('entity', $modulePath, $feature, $namespacePath, $module, "Domain/Entities/{$feature}.php");
        }

        // 5. Event [Domain]
        if ($this->option('event')) {
            $createdFiles[] = $this->generateFile('event', $modulePath, $feature, $namespacePath, $module, "Domain/Events/{$feature}.php");
        }

        // 6. Exception [Domain]
        if ($this->option('exception')) {
            $exceptionClass = str_ends_with($feature, 'Exception') ? $feature : "{$feature}Exception";
            $createdFiles[] = $this->generateFile('exception', $modulePath, $exceptionClass, $namespacePath, $module, "Domain/Exceptions/{$exceptionClass}.php");
        }

        // 7. Service [Domain]
        if ($this->option('service')) {
            $createdFiles[] = $this->generateFile('service', $modulePath, $feature, $namespacePath, $module, "Domain/Services/{$feature}.php");
        }

        // 8. Interface Get [Domain]
        $hasInterfaceGet = $this->option('interface-get');
        $hasRepo = $this->option('repo') || $this->option('repository');
        $hasRepoOnly = $this->option('repo-only');

        if ($generateAll || $hasInterfaceGet || $hasRepo) {
            $createdFiles[] = $this->generateFile('repository_get_interface', $modulePath, $feature, $namespacePath, $module, "Domain/Interfaces/{$feature}GetInterface.php");
        }

        // 6. Interface Post [Domain]
        $hasInterfacePost = $this->option('interface-post');
        if ($generateAll || $hasInterfacePost || $hasRepo) {
            $createdFiles[] = $this->generateFile('repository_post_interface', $modulePath, $feature, $namespacePath, $module, "Domain/Interfaces/{$feature}PostInterface.php");
        }

        // 7. Repository [Infrastructure]
        if ($generateAll || $hasRepo || $hasRepoOnly) {
            $createdFiles[] = $this->generateFile('repository', $modulePath, $feature, $namespacePath, $module, "Infrastructure/Repositories/{$feature}Repository.php");
        }

        // Auto-bind interfaces that have matching repository
        if ($this->files->exists($modulePath."/Infrastructure/Repositories/{$feature}Repository.php")) {
            $this->bindInServiceProvider($modulePath, $feature, $namespacePath, $module);
        }

        // 8. DTO [Application]
        if ($generateAll || $this->option('dto')) {
            $createdFiles[] = $this->generateFile('dto', $modulePath, $feature, $namespacePath, $module, "Application/DTOs/{$feature}DTO.php");
        }

        // 7. UseCase [Application]
        if ($generateAll || $this->option('usecase')) {
            $createdFiles[] = $this->generateFile('usecase', $modulePath, $feature, $namespacePath, $module, "Application/UseCases/{$feature}UseCase.php");
        }

        // 8. Query CQRS [Application]
        if ($this->option('query')) {
            $createdFiles[] = $this->generateFile('query', $modulePath, $feature, $namespacePath, $module, "Application/Queries/Get{$feature}Query.php");
        }

        // 9. FormRequest [Http]
        if ($generateAll || $this->option('request')) {
            $createdFiles[] = $this->generateFile('request', $modulePath, $feature, $namespacePath, $module, "Http/Requests/{$feature}Request.php");
        }

        // 10. Resource [Http]
        if ($generateAll || $this->option('resource')) {
            $createdFiles[] = $this->generateFile('resource', $modulePath, $feature, $namespacePath, $module, "Http/Resources/{$feature}Resource.php");
        }

        // 11. Response [Http]
        if ($this->option('response')) {
            $createdFiles[] = $this->generateFile('response', $modulePath, $feature, $namespacePath, $module, "Http/Responses/{$feature}Response.php");
        }

        // 12. Middleware [Http]
        if ($this->option('middleware')) {
            $createdFiles[] = $this->generateFile('middleware', $modulePath, $feature, $namespacePath, $module, "Http/Middleware/{$feature}.php");
        }

        // 13. Controller [Http]
        if ($generateAll || $this->option('controller')) {
            $createdFiles[] = $this->generateFile('controller', $modulePath, $feature, $namespacePath, $module, "Http/Controllers/{$feature}Controller.php");
        }

        // 14. Test [Tests]
        if ($this->option('test')) {
            $testClass = str_ends_with($feature, 'Test') ? $feature : "{$feature}Test";
            $createdFiles[] = $this->generateFile('test', $modulePath, $testClass, $namespacePath, $module, "Tests/{$testClass}.php");
        }

        // Composite flag: --get (Controller + UseCase + Resource)
        if ($this->option('get') && ! $generateAll) {
            $createdFiles[] = $this->generateFile('controller_get', $modulePath, $feature, $namespacePath, $module, "Http/Controllers/{$feature}Controller.php");
            $createdFiles[] = $this->generateFile('usecase_get', $modulePath, $feature, $namespacePath, $module, "Application/UseCases/{$feature}UseCase.php");
            $createdFiles[] = $this->generateFile('resource', $modulePath, $feature, $namespacePath, $module, "Http/Resources/{$feature}Resource.php");
        }

        // Fallback if no options supplied
        if (! $generateAll && ! $hasIndividual && ! $this->option('get')) {
            $this->info('💡 Tidak ada opsi yang dipilih. Menulis komponen standar (UseCase)...');
            $createdFiles[] = $this->generateFile('usecase', $modulePath, $feature, $namespacePath, $module, "Application/UseCases/{$feature}UseCase.php");
        }

        $this->newLine();
        $this->info("✅ Fitur [{$feature}] berhasil dibuat di Modul [{$module}]!");
        foreach ($createdFiles as $file) {
            $this->comment("  📄 {$file}");
        }

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

    protected function hasIndividualFlags(): bool
    {
        return $this->option('model') ||
            $this->option('enum') ||
            $this->option('vo') || $this->option('value-object') ||
            $this->option('entity') ||
            $this->option('event') ||
            $this->option('exception') ||
            $this->option('service') ||
            $this->option('interface-get') ||
            $this->option('interface-post') ||
            $this->option('repo') || $this->option('repository') ||
            $this->option('repo-only') ||
            $this->option('dto') ||
            $this->option('usecase') ||
            $this->option('query') ||
            $this->option('request') ||
            $this->option('resource') ||
            $this->option('response') ||
            $this->option('middleware') ||
            $this->option('controller') ||
            $this->option('test');
    }

    protected function generateFile(string $stubType, string $modulePath, string $feature, string $namespacePath, string $moduleName, string $relativePath): string
    {
        $targetPath = str_starts_with($relativePath, 'database/') ? base_path($relativePath) : $modulePath.'/'.$relativePath;
        $directory = dirname($targetPath);

        if (! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0755, true, true);
        }

        if ($this->files->exists($targetPath)) {
            $this->warn("⚠️ File sudah ada, dilewati: {$relativePath}");

            return "{$relativePath} (Sudah Ada)";
        }

        $stubFile = base_path("stubs/ddd/{$stubType}.stub");
        $stubContent = $this->files->exists($stubFile) ? $this->files->get($stubFile) : '';
        $schemaData = $this->getTableSchemaData($feature, $modulePath);
        $tableName = $schemaData['tableName'] ?? $this->getPluralTableName($feature);

        $usecaseImports = '';
        $usecaseConstructor = '';
        $usecaseBody = "// TODO: Implement business logic execution\n            return null;";

        $usecaseSmartBodyGetPost = <<<'PHP'
$data = array_filter($dto->toArray(), fn ($value) => ! is_null($value));

            if ($dto->id) {
                $this->post->update($dto->id, $data);

                return $this->get->findById($dto->id);
            }

            return $this->post->create($data);
PHP;

        $usecaseSmartBodyRepo = <<<'PHP'
$data = array_filter($dto->toArray(), fn ($value) => ! is_null($value));

            if ($dto->id) {
                $this->repository->update($dto->id, $data);

                return $this->repository->findById($dto->id);
            }

            return $this->repository->create($data);
PHP;

        if ($this->files->exists($modulePath."/Domain/Interfaces/{$feature}GetInterface.php") && $this->files->exists($modulePath."/Domain/Interfaces/{$feature}PostInterface.php")) {
            $usecaseImports = "use {$namespacePath}\\Domain\\Interfaces\\{$feature}GetInterface;\nuse {$namespacePath}\\Domain\\Interfaces\\{$feature}PostInterface;";
            $usecaseConstructor = "        protected {$feature}GetInterface \$get,\n        protected {$feature}PostInterface \$post,";
            $usecaseBody = $usecaseSmartBodyGetPost;
        } elseif ($this->files->exists($modulePath."/Domain/Interfaces/{$moduleName}GetInterface.php") && $this->files->exists($modulePath."/Domain/Interfaces/{$moduleName}PostInterface.php")) {
            $usecaseImports = "use {$namespacePath}\\Domain\\Interfaces\\{$moduleName}GetInterface;\nuse {$namespacePath}\\Domain\\Interfaces\\{$moduleName}PostInterface;";
            $usecaseConstructor = "        protected {$moduleName}GetInterface \$get,\n        protected {$moduleName}PostInterface \$post,";
            $usecaseBody = $usecaseSmartBodyGetPost;
        } elseif ($this->files->exists($modulePath."/Domain/Interfaces/{$feature}Interface.php")) {
            $usecaseImports = "use {$namespacePath}\\Domain\\Interfaces\\{$feature}Interface;";
            $usecaseConstructor = "        protected {$feature}Interface \$repository,";
            $usecaseBody = $usecaseSmartBodyRepo;
        } elseif ($this->files->exists($modulePath."/Domain/Interfaces/{$moduleName}Interface.php")) {
            $usecaseImports = "use {$namespacePath}\\Domain\\Interfaces\\{$moduleName}Interface;";
            $usecaseConstructor = "        protected {$moduleName}Interface \$repository,";
            $usecaseBody = $usecaseSmartBodyRepo;
        }

        $content = str_replace(
            [
                '{{namespace}}',
                '{{module}}',
                '{{moduleLower}}',
                '{{moduleSlug}}',
                '{{feature}}',
                '{{featureLower}}',
                '{{featureSlug}}',
                '{{featureTable}}',
                '{{featureSummary}}',
                '{{entityProperties}}',
                '{{dtoProperties}}',
                '{{modelFillable}}',
                '{{requestRules}}',
                '{{queryFilterText}}',
                '{{usecaseImports}}',
                '{{usecaseConstructor}}',
                '{{usecaseBody}}',
            ],
            [
                $namespacePath,
                $moduleName,
                strtolower($moduleName),
                Str::kebab($moduleName),
                $feature,
                Str::camel($feature),
                Str::kebab($feature),
                $tableName,
                Str::snake($feature).'_summary',
                $schemaData['entityProperties'] ?? '',
                $schemaData['dtoProperties'],
                $schemaData['modelFillable'],
                $schemaData['requestRules'],
                $schemaData['queryFilterText'],
                $usecaseImports,
                $usecaseConstructor,
                $usecaseBody,
            ],
            $stubContent
        );

        $this->files->put($targetPath, $content);

        return $relativePath;
    }

    protected function getPluralTableName(string $feature): string
    {
        $plural = Str::snake(Str::pluralStudly($feature));
        $singular = Str::snake($feature);

        if ($plural === $singular && ! str_ends_with($singular, 's')) {
            return $singular.'s';
        }

        return $plural;
    }

    protected function getTableSchemaData(string $feature, ?string $modulePath = null): array
    {
        $pluralTable = $this->getPluralTableName($feature);
        $tableNames = [
            $pluralTable,
            Str::snake(Str::pluralStudly($feature)),
            Str::snake($feature).'s',
            Str::snake($feature),
            strtolower($feature).'s',
            strtolower($feature),
        ];

        $matchedTable = null;
        foreach ($tableNames as $table) {
            if (Schema::hasTable($table)) {
                $matchedTable = $table;
                break;
            }
        }

        $columns = [];
        if ($matchedTable) {
            $columns = Schema::getColumns($matchedTable);
        } else {
            $columns = $this->getColumnsFromMigrations($feature, $modulePath, $tableNames);
        }

        $summary = Str::snake($feature).'_summary';

        if (empty($columns)) {
            return [
                'tableName' => $pluralTable,
                'entityProperties' => "        public ?int \$id = null,\n        // public string \$name = '',",
                'dtoProperties' => "        // public string \$name,\n        // public ?string \$email = null,",
                'modelFillable' => '        // Add fillable columns here',
                'requestRules' => '            // Define validation rules here',
                'queryFilterText' => "                // \$q->orWhere('name', 'like', \"%{\$this->search}%\");",
            ];
        }

        $entityProps = [];
        $dtoProps = [];
        $fillables = [];
        $rules = [];
        $pgFields = [];
        $pgCols = [];
        $pgFilters = [];
        $qSorts = [];
        $qFilterText = [];
        $qFilterSelect = [];
        $qFilterDate = [];
        $lwProps = [];
        $lwEditAssigns = [];
        $lwResetFields = ["'id'"];
        $lwFormInputs = [];

        foreach ($columns as $col) {
            $colName = $col['name'];
            $type = strtolower($col['type_name'] ?? $col['type'] ?? 'string');
            $nullable = (bool) ($col['nullable'] ?? false);
            $default = $col['default'] ?? null;
            $isPrimary = (bool) ($col['auto_increment'] ?? ($colName === 'id'));

            if (in_array($colName, ['created_at', 'updated_at', 'deleted_at', 'remember_token'])) {
                if (in_array($colName, ['created_at', 'updated_at'])) {
                    $qSorts[] = "            '{$summary}.{$colName}' => '{$summary}.{$colName}',";
                    $qSorts[] = "            '{$colName}' => '{$summary}.{$colName}',";
                    $qFilterDate[] = "                '{$colName}' => '{$summary}.{$colName}',";
                }

                continue;
            }

            // 1. Entity & DTO property mapping
            $phpType = match (true) {
                str_contains($type, 'int') => 'int',
                str_contains($type, 'bool') => 'bool',
                str_contains($type, 'float') || str_contains($type, 'double') || str_contains($type, 'decimal') => 'float',
                str_contains($type, 'json') => 'array',
                default => 'string',
            };

            if ($isPrimary || str_ends_with($colName, '_id')) {
                $entityProps[] = "        public ?{$phpType} \${$colName} = null,";
                $dtoProps[] = "        public ?{$phpType} \${$colName} = null,";
            } elseif ($colName === 'password') {
                $entityProps[] = '        public ?string $password = null,';
                $dtoProps[] = '        public ?string $password = null,';
            } elseif ($phpType === 'bool') {
                $boolDefault = ($default === '1' || $default === 1 || $default === 'true') ? 'true' : 'false';
                $entityProps[] = "        public bool \${$colName} = {$boolDefault},";
                $dtoProps[] = "        public bool \${$colName} = {$boolDefault},";
            } elseif ($nullable) {
                $entityProps[] = "        public ?{$phpType} \${$colName} = null,";
                $dtoProps[] = "        public ?{$phpType} \${$colName} = null,";
            } elseif ($default !== null) {
                $cleanDefault = trim($default, "'\"");
                $formattedDefault = is_numeric($cleanDefault) ? $cleanDefault : "'{$cleanDefault}'";
                $entityProps[] = "        public {$phpType} \${$colName} = {$formattedDefault},";
                $dtoProps[] = "        public {$phpType} \${$colName} = {$formattedDefault},";
            } else {
                $entityProps[] = "        public ?{$phpType} \${$colName} = null,";
                $dtoProps[] = "        public {$phpType} \${$colName},";
            }

            // 2. Model fillable
            if (! $isPrimary) {
                $fillables[] = "        '{$colName}',";
            }

            // 3. Request rules
            if (! $isPrimary) {
                if ($colName === 'password') {
                    $rules[] = "            'password' => ['nullable', 'string', 'min:8'],";
                } else {
                    $ruleList = [];
                    if ($nullable) {
                        $ruleList[] = "'nullable'";
                    } else {
                        $ruleList[] = "'required'";
                    }

                    if (str_contains($colName, 'email')) {
                        $ruleList[] = "'email'";
                    } elseif (str_contains($type, 'int')) {
                        $ruleList[] = "'integer'";
                    } elseif (str_contains($type, 'bool')) {
                        $ruleList[] = "'boolean'";
                    } elseif (str_contains($type, 'json')) {
                        $ruleList[] = "'array'";
                    } else {
                        $ruleList[] = "'string'";
                    }

                    $ruleString = implode(', ', $ruleList);
                    $rules[] = "            '{$colName}' => [{$ruleString}],";
                }
            }

            // 4. Query Searchable Columns (Text & Strings)
            if (! in_array($colName, ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'id', 'image'])) {
                if (str_contains($type, 'string') || str_contains($type, 'text') || str_contains($colName, 'name') || str_contains($colName, 'email') || str_contains($colName, 'phone') || str_contains($colName, 'code')) {
                    $qFilterText[] = "                \$q->orWhere('{$colName}', 'like', \"%{\$this->search}%\");";
                }
            }
        }

        return [
            'tableName' => $matchedTable ?? $pluralTable,
            'entityProperties' => implode("\n", $entityProps),
            'dtoProperties' => implode("\n", $dtoProps),
            'modelFillable' => implode("\n", $fillables),
            'requestRules' => implode("\n", $rules),
            'queryFilterText' => implode("\n", $qFilterText),
        ];
    }

    protected function getColumnsFromMigrations(string $feature, ?string $modulePath, array $tableNames): array
    {
        $migrationsDir = database_path('migrations');
        if (! $this->files->isDirectory($migrationsDir)) {
            return [];
        }

        $files = $this->files->files($migrationsDir);
        usort($files, fn ($a, $b) => strcmp($b->getFilename(), $a->getFilename()));

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $content = $this->files->get($file->getRealPath());

            foreach ($tableNames as $table) {
                if (str_contains($filename, "create_{$table}_table") || str_contains($content, "Schema::create('{$table}'") || str_contains($content, "Schema::create(\"{$table}\"")) {
                    $parsed = $this->parseMigrationContent($content);
                    if (! empty($parsed)) {
                        return $parsed;
                    }
                }
            }
        }

        return [];
    }

    protected function parseMigrationContent(string $content): array
    {
        $columns = [];
        $pattern = '/\$table->([a-zA-Z0-9_]+)\s*\((.*?)\)(.*?);/s';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $method = $match[1];
                $argsString = trim($match[2]);
                $chain = $match[3];

                if (in_array($method, ['timestamps', 'timestampsTz', 'rememberToken', 'softDeletes', 'softDeletesTz', 'id', 'bigIncrements', 'increments'])) {
                    if (in_array($method, ['id', 'bigIncrements', 'increments'])) {
                        $colName = 'id';
                        if (! empty($argsString)) {
                            $argParts = str_getcsv($argsString, ',', "'");
                            $colName = trim($argParts[0] ?? 'id', " '\"");
                        }
                        $columns[] = [
                            'name' => $colName,
                            'type' => 'integer',
                            'type_name' => 'integer',
                            'nullable' => false,
                            'default' => null,
                            'auto_increment' => true,
                        ];
                    }

                    continue;
                }

                $argParts = str_getcsv($argsString, ',', "'");
                if (empty($argParts) || empty(trim($argParts[0]))) {
                    continue;
                }

                $colName = trim($argParts[0], " '\"");
                if (empty($colName) || str_contains($colName, '$')) {
                    continue;
                }

                $nullable = str_contains($chain, '->nullable()');
                $default = null;
                if (preg_match('/->default\((.*?)\)/', $chain, $defaultMatch)) {
                    $default = trim($defaultMatch[1], " '\"");
                }

                $columns[] = [
                    'name' => $colName,
                    'type' => $method,
                    'type_name' => $method,
                    'nullable' => $nullable,
                    'default' => $default,
                    'auto_increment' => false,
                ];
            }
        }

        return $columns;
    }

    protected function bindInServiceProvider(string $modulePath, string $feature, string $namespacePath, string $module): void
    {
        $appProviderPath = app_path('Providers/AppServiceProvider.php');
        $moduleProviderPath = $modulePath."/Providers/{$module}ServiceProvider.php";
        $providerPath = $this->files->exists($appProviderPath) ? $appProviderPath : ($this->files->exists($moduleProviderPath) ? $moduleProviderPath : null);

        if (! $providerPath) {
            return;
        }

        $content = $this->files->get($providerPath);
        $getInterfaceClass = "\\{$namespacePath}\\Domain\\Interfaces\\{$feature}GetInterface::class";
        $postInterfaceClass = "\\{$namespacePath}\\Domain\\Interfaces\\{$feature}PostInterface::class";
        $repoClass = "\\{$namespacePath}\\Infrastructure\\Repositories\\{$feature}Repository::class";

        $bindingCode = '';
        if ($this->files->exists($modulePath."/Domain/Interfaces/{$feature}GetInterface.php") && ! str_contains($content, "{$feature}GetInterface::class")) {
            $bindingCode .= "        \$this->app->bind(\n            {$getInterfaceClass},\n            {$repoClass}\n        );\n";
        }

        if ($this->files->exists($modulePath."/Domain/Interfaces/{$feature}PostInterface.php") && ! str_contains($content, "{$feature}PostInterface::class")) {
            $bindingCode .= "        \$this->app->bind(\n            {$postInterfaceClass},\n            {$repoClass}\n        );\n";
        }

        if (empty($bindingCode)) {
            return;
        }

        if (preg_match('/public function register\(\): void\s*\{(\s*)/', $content, $matches)) {
            $content = preg_replace(
                '/public function register\(\): void\s*\{(\s*)/',
                "public function register(): void\n    {\n{$bindingCode}",
                $content,
                1
            );
            $this->files->put($providerPath, $content);
            $filename = basename($providerPath);
            $this->info("  🔗 Berhasil mendaftarkan binding di [{$filename}]");
        }
    }
}
