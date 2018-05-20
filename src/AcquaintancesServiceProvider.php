<?php


namespace Liliom\Acquaintances;

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
        $root = dirname(__DIR__);

        if ( ! file_exists(config_path('acquaintances.php'))) {
            $this->publishes([
                $root . '/config/acquaintances.php' => config_path('acquaintances.php'),
            ], 'config');
        }

        if ( ! class_exists('CreateAcquaintancesInteractionsTable')) {
            $datePrefix = date('Y_m_d_His');
            $this->publishes([
                $root . '/database/migrations/create_acquaintances_interactions_table.php' => database_path("/migrations/{$datePrefix}_create_acquaintances_interactions_table.php"),
            ], 'migrations');
        }

        if ( ! class_exists('CreateAcquaintancesFriendshipTable')) {
            $datePrefix = date('Y_m_d_His');
            $this->publishes([
                $root . '/database/migrations/create_acquaintances_friendship_table.php' => database_path("/migrations/{$datePrefix}_create_acquaintances_friendship_table.php"),
            ], 'migrations');
        }

        if ( ! class_exists('CreateAcquaintancesFriendshipsGroupsTable')) {
            $datePrefix = date('Y_m_d_His');
            $this->publishes([
                $root . '/database/migrations/create_acquaintances_friendships_groups_table.php' => database_path("/migrations/{$datePrefix}_create_acquaintances_friendships_groups_table.php"),
            ], 'migrations');
        }

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/acquaintances.php', 'acquaintances');
    }
}
