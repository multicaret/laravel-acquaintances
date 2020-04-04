<?php


namespace Multicaret\Acquaintances;

use Illuminate\Support\ServiceProvider;

class AcquaintancesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
    }

    /**
     * Register Acquaintances's migration files.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (count(\File::glob(database_path('migrations/*acquaintances*.php'))) === 0) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->offerPublishing();
    }

    /**
     * Setup the configuration for Acquaintances.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/acquaintances.php', 'acquaintances'
        );
    }

    /**
     * Setup the resource publishing groups for Acquaintances.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/acquaintances.php' => config_path('acquaintances.php'),
            ], 'acquaintances-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'acquaintances-migrations');
        }
    }
}
