<?php

namespace Multicaret\Acquaintances;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Multicaret\Acquaintances\Scopes\AcquaintanceScopes;

class AcquaintancesServiceProvider extends ServiceProvider
{
    /**
     * Migration file-name patterns mapped to their feature flag.
     *
     * @var array<string, string>
     */
    protected array $migrationFeatureMap = [
        'interactions' => 'interactions',
        'friendship_table' => 'friendships',
        'friendships_groups' => 'friendships',
        'verification_table' => 'verifications',
        'verifications_groups' => 'verifications',
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerMigrations();
        $this->registerMorphMap();

        AcquaintanceScopes::register();
    }

    /**
     * Register Acquaintances's migration files.
     */
    protected function registerMigrations(): void
    {
        $config = $this->app['config']['acquaintances'];
        $runMigrations = is_null($config['migrations'] ?? null)
            ? count(\File::glob(database_path('migrations/*acquaintances*.php'))) === 0
            : $config['migrations'];

        if ($runMigrations) {
            $this->loadMigrationsFrom($this->getEnabledMigrationPaths());
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configure();
        $this->offerPublishing();
    }

    /**
     * Setup the configuration for Acquaintances.
     */
    protected function configure(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/acquaintances.php', 'acquaintances'
        );
    }

    /**
     * Setup the resource publishing groups for Acquaintances.
     */
    protected function offerPublishing(): void
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/acquaintances.php' => config_path('acquaintances.php'),
            ], 'acquaintances-config');

            // Publish all enabled migrations under the main tag
            $this->publishes(
                $this->updateMigrationDate($this->getEnabledMigrationFiles()),
                'acquaintances-migrations'
            );

            // Granular per-feature publish tags
            $this->publishes(
                $this->updateMigrationDate($this->getMigrationFilesForFeature('interactions')),
                'acquaintances-migrations-interactions'
            );

            $this->publishes(
                $this->updateMigrationDate($this->getMigrationFilesForFeature('friendships')),
                'acquaintances-migrations-friendships'
            );

            $this->publishes(
                $this->updateMigrationDate($this->getMigrationFilesForFeature('verifications')),
                'acquaintances-migrations-verifications'
            );
        }
    }

    /**
     * Register morph map if configured.
     */
    protected function registerMorphMap(): void
    {
        $config = $this->app['config']['acquaintances'];

        if (! empty($config['enforce_morph_map']) && ! empty($config['morph_map'])) {
            Relation::enforceMorphMap($config['morph_map']);
        } elseif (! empty($config['morph_map'])) {
            Relation::morphMap($config['morph_map']);
        }
    }

    /**
     * Determine if a feature is enabled in the config.
     */
    protected function isFeatureEnabled(string $feature): bool
    {
        return (bool) ($this->app['config']['acquaintances']['features'][$feature] ?? true);
    }

    /**
     * Get all migration file paths that belong to enabled features.
     *
     * @return array<string>
     */
    protected function getEnabledMigrationPaths(): array
    {
        return array_map(
            fn ($file) => $file->getPathname(),
            $this->getEnabledMigrationFiles()
        );
    }

    /**
     * Get all migration SplFileInfo objects that belong to enabled features.
     *
     * @return array<\SplFileInfo>
     */
    protected function getEnabledMigrationFiles(): array
    {
        $path = __DIR__.'/../database/migrations';

        return array_filter(
            \File::allFiles($path),
            fn ($file) => $this->isMigrationEnabled($file->getFilename())
        );
    }

    /**
     * Get migration SplFileInfo objects for a specific feature.
     *
     * @return array<\SplFileInfo>
     */
    protected function getMigrationFilesForFeature(string $feature): array
    {
        $path = __DIR__.'/../database/migrations';

        return array_filter(
            \File::allFiles($path),
            fn ($file) => $this->getMigrationFeature($file->getFilename()) === $feature
        );
    }

    /**
     * Check if a migration file belongs to an enabled feature.
     */
    protected function isMigrationEnabled(string $filename): bool
    {
        $feature = $this->getMigrationFeature($filename);

        // If we can't map to a feature, include it by default
        if ($feature === null) {
            return true;
        }

        return $this->isFeatureEnabled($feature);
    }

    /**
     * Determine which feature a migration file belongs to.
     */
    protected function getMigrationFeature(string $filename): ?string
    {
        foreach ($this->migrationFeatureMap as $pattern => $feature) {
            if (str_contains($filename, $pattern)) {
                return $feature;
            }
        }

        return null;
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param  array<\SplFileInfo>  $files
     * @return array<string, string>
     */
    protected function updateMigrationDate(array $files = []): array
    {
        $tempArray = [];
        $path = __DIR__.'/../database/migrations';

        foreach ($files as $file) {
            $basename = \File::basename($file);
            $tempArray[$path.'/'.$basename] = app()->databasePath().'/migrations/'.date('Y_m_d_His').'_'.$basename;
        }

        return $tempArray;
    }
}
