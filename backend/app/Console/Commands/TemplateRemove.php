<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class TemplateRemove extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'template:remove
                            {--layer= : Layer to remove (website, api, website-api)}
                            {--scope= : Scope to filter by (posts, comments, tags)}';

    /**
     * The console command description.
     */
    protected $description = 'Remove a template layer (website, api) or example scope (posts, comments, tags)';

    protected Filesystem $files;
    protected array $removed = [];
    protected array $skipped = [];

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
        $layer = $this->option('layer');
        $scope = $this->option('scope');

        if (empty($layer) && empty($scope)) {
            $this->error('Please specify --layer and/or --scope option.');
            $this->line('');
            $this->line('Examples:');
            $this->line('  php artisan template:remove --layer=website');
            $this->line('  php artisan template:remove --layer=api');
            $this->line('  php artisan template:remove --layer=website-api');
            $this->line('  php artisan template:remove --layer=api --scope=posts');
            $this->line('  php artisan template:remove --scope=posts');
            $this->line('  php artisan template:remove --scope=comments');
            $this->line('  php artisan template:remove --scope=tags');
            $this->line('  php artisan template:remove --scope=all');

            return self::FAILURE;
        }

        if (!empty($layer) && !in_array($layer, ['website', 'api', 'website-api'])) {
            $this->error("Invalid layer: {$layer}. Allowed values: website, api, website-api");
            return self::FAILURE;
        }

        if (!empty($scope) && !in_array($scope, ['posts', 'comments', 'tags', 'all'])) {
            $this->error("Invalid scope: {$scope}. Allowed values: posts, comments, tags");
            return self::FAILURE;
        }

        $scopes = $scope === 'all' ? ['posts', 'comments', 'tags'] : [$scope];
        $scopeLabel = $scope === 'all' ? 'all scopes' : "scope: {$scope}";
        $layers = $this->resolveLayers($layer);
        $target = empty($scope)
            ? implode(' + ', $layers) . ' layer'
            : (empty($layers) ? $scopeLabel : implode(' + ', $layers) . ' layer with ' . $scopeLabel);

        if (!$this->confirm("This will remove files for: {$target}. Continue?")) {
            $this->warn('Aborted.');
            return self::FAILURE;
        }

        foreach ($scopes as $s) {
            if (!empty($s) && empty($layer)) {
                // Scope-only: remove from both layers + shared files
                $this->removeLayer('website', $s);
                $this->removeLayer('api', $s);
                $this->removeScopeShared($s);
            } else {
                foreach ($layers as $l) {
                    $this->removeLayer($l, $s);
                }
            }
        }

        $this->newLine();
        $this->info('Removal complete.');
        $this->newLine();

        if (!empty($this->removed)) {
            $this->components->info('Removed ' . count($this->removed) . ' item(s):');
            foreach ($this->removed as $item) {
                $this->line('  <fg=green>✓</> ' . $item);
            }
        }

        if (!empty($this->skipped)) {
            $this->newLine();
            $this->components->warn('Skipped ' . count($this->skipped) . ' item(s) (not found):');
            foreach ($this->skipped as $item) {
                $this->line('  <fg=yellow>−</> ' . $item);
            }
        }

        return self::SUCCESS;
    }

    protected function resolveLayers(?string $layer): array
    {
        if (empty($layer)) {
            return [];
        }

        return $layer === 'website-api' ? ['website', 'api'] : [$layer];
    }

    protected function removeLayer(string $layer, ?string $scope): void
    {
        match ($layer) {
            'website' => $this->removeWebsiteLayer($scope),
            'api'     => $this->removeApiLayer($scope),
            default   => null,
        };
    }

    protected function removeWebsiteLayer(?string $scope): void
    {
        if (empty($scope)) {
            $this->deleteDirectory('app/Livewire');
            $this->deleteDirectory('resources/views/livewire');
            $this->deleteDirectory('resources/views/layouts');
            $this->deleteDirectory('resources/views/components/website');
            $this->deleteFile('resources/views/welcome.blade.php');
            $this->stubWebRoutes();
        } else {
            $this->removeWebsiteScopeFiles($scope);
            $this->cleanWebRoutesForScope($scope);
        }
    }

    protected function removeApiLayer(?string $scope): void
    {
        if (empty($scope)) {
            $this->deleteDirectory('app/Http/Controllers/Api');
            $this->deleteDirectory('app/Http/Requests/Api');
            $this->deleteDirectory('app/Http/Resources/Api');
            $this->deleteFile('app/Http/Middleware/ForceJsonResponse.php');
            $this->deleteFile('app/Services/AuthService.php');
            $this->deleteFile('app/Models/RefreshToken.php');
            $this->deleteDirectory('tests/Feature/Api');
            $this->stubApiRoutes();
        } else {
            $this->removeApiScopeFiles($scope);
            $this->cleanApiRoutesForScope($scope);
        }
    }

    protected function removeWebsiteScopeFiles(string $scope): void
    {
        $map = [
            'posts'    => [
                'app/Livewire/Website/BlogPage.php',
                'app/Livewire/Website/PostPage.php',
                'resources/views/livewire/website/blog-page.blade.php',
                'resources/views/livewire/website/post-page.blade.php',
                'resources/views/components/website/post-card.blade.php',
            ],
            'tags'     => [
                'app/Livewire/Website/TagPage.php',
                'resources/views/livewire/website/tag-page.blade.php',
            ],
            'comments' => [],
        ];

        foreach ($map[$scope] ?? [] as $path) {
            $this->deleteFile($path);
        }
    }

    protected function removeApiScopeFiles(string $scope): void
    {
        $map = [
            'posts'    => [
                'app/Http/Controllers/Api/PostController.php',
                'app/Http/Requests/Api/Post',
                'app/Http/Resources/Api/PostResource.php',
                'tests/Feature/Api/PostTest.php',
            ],
            'comments' => [
                'app/Http/Controllers/Api/CommentController.php',
                'app/Http/Requests/Api/Comment',
                'app/Http/Resources/Api/CommentResource.php',
                'tests/Feature/Api/CommentTest.php',
            ],
            'tags'     => [
                'app/Http/Controllers/Api/TagController.php',
                'app/Http/Resources/Api/TagResource.php',
                'tests/Feature/Api/TagTest.php',
            ],
        ];

        foreach ($map[$scope] ?? [] as $path) {
            if (is_dir(base_path($path))) {
                $this->deleteDirectory($path);
            } else {
                $this->deleteFile($path);
            }
        }
    }

    protected function removeScopeShared(string $scope): void
    {
        $map = [
            'posts'    => [
                'app/Models/Post.php',
                'database/factories/PostFactory.php',
                'database/seeders/PostSeeder.php',
                'app/Services/PostService.php',
                'app/Enums/PostStatus.php',
                'app/Policies/PostPolicy.php',
                'app/Filament/Resources/Posts',
                'resources/views/filament/resources/posts',
            ],
            'comments' => [
                'app/Models/Comment.php',
                'database/factories/CommentFactory.php',
                'app/Services/CommentService.php',
                'app/Enums/CommentStatus.php',
                'app/Policies/CommentPolicy.php',
                'app/Filament/Resources/Comments',
            ],
            'tags'     => [
                'app/Models/Tag.php',
                'database/factories/TagFactory.php',
                'app/Policies/TagPolicy.php',
            ],
        ];

        foreach ($map[$scope] ?? [] as $path) {
            if (is_dir(base_path($path))) {
                $this->deleteDirectory($path);
            } else {
                $this->deleteFile($path);
            }
        }

        $migrationPatterns = [
            'posts'    => [
                'database/migrations/*_create_posts_table.php',
                'database/migrations/*_create_post_tag_table.php',
            ],
            'comments' => [
                'database/migrations/*_create_comments_table.php',
            ],
            'tags'     => [
                'database/migrations/*_create_tags_table.php',
                'database/migrations/*_create_post_tag_table.php',
            ],
        ];

        foreach ($migrationPatterns[$scope] ?? [] as $pattern) {
            $fullPattern = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, base_path($pattern));
            foreach (glob($fullPattern) as $file) {
                $this->deleteFile(str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file));
            }
        }

        $this->cleanDatabaseSeeder($scope);
        $this->cleanLangFile($scope);
    }

    protected function cleanWebRoutesForScope(string $scope): void
    {
        $path = base_path('routes/web.php');
        if (!file_exists($path)) {
            return;
        }

        $patterns = [
            'posts'    => ['/BlogPage/', '/PostPage/', '/post:slug/', '/post\'/', '/blog\'/'],
            'comments' => [],
            'tags'     => ['/TagPage/', '/tag:slug/', '/tag\'/'],
        ];

        $this->filterRouteFile($path, $patterns[$scope] ?? []);
    }

    protected function cleanApiRoutesForScope(string $scope): void
    {
        $path = base_path('routes/api.php');
        if (!file_exists($path)) {
            return;
        }

        $patterns = [
            'posts'    => [
                '/PostController/',
                '/\/posts/',
                '/Posts \(public\)/',
                '/Posts \(authenticated\)/',
            ],
            'comments' => [
                '/CommentController/',
                '/\/posts\/\{slug\}\/comments/',
                '/Comments ─────────/',
            ],
            'tags'     => [
                '/TagController/',
                '/\/tags/',
                '/Tags ─────────────/',
            ],
        ];

        $this->filterRouteFile($path, $patterns[$scope] ?? []);
    }

    protected function filterRouteFile(string $path, array $patterns): void
    {
        if (empty($patterns)) {
            return;
        }

        $lines = file($path);
        $result = [];

        foreach ($lines as $line) {
            $remove = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $remove = true;
                    break;
                }
            }

            if (str_starts_with(trim($line), 'use ') && str_contains($line, 'Controller')) {
                $controllerName = basename(str_replace('\\', '/', trim($line, "; \t\n\r\0\x0B")));
                foreach ($patterns as $pattern) {
                    if (preg_match('/' . preg_quote($controllerName, '/') . '/', $line)) {
                        $remove = true;
                        break 2;
                    }
                }
            }

            if (!$remove) {
                $result[] = $line;
            }
        }

        $result = $this->removeOrphanedBlocks($result);
        file_put_contents($path, implode('', $result));
        $this->removed[] = 'Updated: ' . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
    }

    protected function removeOrphanedBlocks(array $lines): array
    {
        $result = [];
        $prevWasComment = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^\/\/ ───+/', $trimmed)) {
                $prevWasComment = true;
                $result[] = $line;
                continue;
            }

            if ($prevWasComment && (empty($trimmed) || preg_match('/^\/\/ ───+/', $trimmed))) {
                array_pop($result);
                if (preg_match('/^\/\/ ───+/', $trimmed)) {
                    $result[] = $line;
                    $prevWasComment = true;
                } else {
                    $prevWasComment = false;
                }
                continue;
            }

            $prevWasComment = false;
            $result[] = $line;
        }

        while (!empty($result) && trim($result[count($result) - 1]) === '') {
            array_pop($result);
        }

        if (!empty($result) && !str_ends_with($result[count($result) - 1], "\n")) {
            $result[count($result) - 1] .= "\n";
        }

        return $result;
    }

    protected function stubWebRoutes(): void
    {
        $stub = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

// Website layer removed. Add your routes here.
PHP;

        file_put_contents(base_path('routes/web.php'), $stub);
        $this->removed[] = 'Replaced: routes/web.php';
    }

    protected function stubApiRoutes(): void
    {
        $stub = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

// API layer removed. Add your API routes here.
PHP;

        file_put_contents(base_path('routes/api.php'), $stub);
        $this->removed[] = 'Replaced: routes/api.php';
    }

    protected function cleanDatabaseSeeder(string $scope): void
    {
        $path = base_path('database/seeders/DatabaseSeeder.php');
        if (!file_exists($path)) {
            return;
        }

        $seederMap = [
            'posts'    => 'PostSeeder::class',
            'comments' => null,
            'tags'     => null,
        ];

        $target = $seederMap[$scope] ?? null;
        if (empty($target)) {
            return;
        }

        $content = file_get_contents($path);
        $content = preg_replace('/\s+' . preg_quote($target, '/') . ',?/', '', $content);
        file_put_contents($path, $content);
        $this->removed[] = 'Updated: database/seeders/DatabaseSeeder.php';
    }

    protected function cleanLangFile(string $scope): void
    {
        $path = base_path('lang/en/api.php');
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path);
        $scopeKey = "'{$scope}'";
        $inBlock = false;
        $depth = 0;
        $result = [];

        foreach ($lines as $line) {
            if (!$inBlock) {
                $trimmed = trim($line);
                if (str_starts_with($trimmed, $scopeKey) && str_contains($trimmed, '=>')) {
                    $inBlock = true;
                    $depth = substr_count($line, '[') - substr_count($line, ']');
                    if ($depth <= 0) {
                        $inBlock = false;
                    }
                    continue;
                }
                $result[] = $line;
            } else {
                $depth += substr_count($line, '[') - substr_count($line, ']');
                if ($depth <= 0) {
                    $inBlock = false;
                }
            }
        }

        file_put_contents($path, implode('', $result));
        $this->removed[] = 'Updated: lang/en/api.php';
    }

    protected function deleteFile(string $relativePath): void
    {
        $path = base_path($relativePath);
        if (!file_exists($path)) {
            $this->skipped[] = $relativePath;
            return;
        }

        unlink($path);
        $this->removed[] = 'Deleted: ' . $relativePath;
    }

    protected function deleteDirectory(string $relativePath): void
    {
        $path = base_path($relativePath);
        if (!is_dir($path)) {
            $this->skipped[] = $relativePath;
            return;
        }

        $this->files->deleteDirectory($path);
        $this->removed[] = 'Deleted: ' . $relativePath;
    }
}
