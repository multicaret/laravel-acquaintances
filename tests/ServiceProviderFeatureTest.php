<?php

namespace Tests;

use Multicaret\Acquaintances\AcquaintancesServiceProvider;
use Orchestra\Testbench\TestCase;

class ServiceProviderFeatureTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AcquaintancesServiceProvider::class,
        ];
    }

    /** @test */
    public function test_it_maps_interaction_migration_to_interactions_feature(): void
    {
        $provider = new AcquaintancesServiceProvider($this->app);

        $method = new \ReflectionMethod($provider, 'getMigrationFeature');

        $this->assertEquals('interactions', $method->invoke($provider, 'create_acquaintances_interactions_table.php'));
    }

    /** @test */
    public function test_it_maps_friendship_migrations_to_friendships_feature(): void
    {
        $provider = new AcquaintancesServiceProvider($this->app);

        $method = new \ReflectionMethod($provider, 'getMigrationFeature');

        $this->assertEquals('friendships', $method->invoke($provider, 'create_acquaintances_friendship_table.php'));
        $this->assertEquals('friendships', $method->invoke($provider, 'create_acquaintances_friendships_groups_table.php'));
    }

    /** @test */
    public function test_it_maps_verification_migrations_to_verifications_feature(): void
    {
        $provider = new AcquaintancesServiceProvider($this->app);

        $method = new \ReflectionMethod($provider, 'getMigrationFeature');

        $this->assertEquals('verifications', $method->invoke($provider, 'create_acquaintances_verification_table.php'));
        $this->assertEquals('verifications', $method->invoke($provider, 'create_acquaintances_verifications_groups_table.php'));
    }

    /** @test */
    public function test_all_migrations_are_enabled_by_default(): void
    {
        $provider = new AcquaintancesServiceProvider($this->app);

        $method = new \ReflectionMethod($provider, 'getEnabledMigrationFiles');
        $files = $method->invoke($provider);

        $filenames = array_map(fn ($f) => $f->getFilename(), $files);

        $this->assertContains('create_acquaintances_interactions_table.php', $filenames);
        $this->assertContains('create_acquaintances_friendship_table.php', $filenames);
        $this->assertContains('create_acquaintances_friendships_groups_table.php', $filenames);
        $this->assertContains('create_acquaintances_verification_table.php', $filenames);
        $this->assertContains('create_acquaintances_verifications_groups_table.php', $filenames);
    }

    /** @test */
    public function test_disabling_friendships_excludes_friendship_migrations(): void
    {
        $this->app['config']->set('acquaintances.features.friendships', false);

        $provider = new AcquaintancesServiceProvider($this->app);

        $method = new \ReflectionMethod($provider, 'getEnabledMigrationFiles');
        $files = $method->invoke($provider);

        $filenames = array_map(fn ($f) => $f->getFilename(), $files);

        $this->assertContains('create_acquaintances_interactions_table.php', $filenames);
        $this->assertNotContains('create_acquaintances_friendship_table.php', $filenames);
        $this->assertNotContains('create_acquaintances_friendships_groups_table.php', $filenames);
        $this->assertContains('create_acquaintances_verification_table.php', $filenames);
        $this->assertContains('create_acquaintances_verifications_groups_table.php', $filenames);
    }

    /** @test */
    public function test_disabling_verifications_excludes_verification_migrations(): void
    {
        $this->app['config']->set('acquaintances.features.verifications', false);

        $provider = new AcquaintancesServiceProvider($this->app);

        $method = new \ReflectionMethod($provider, 'getEnabledMigrationFiles');
        $files = $method->invoke($provider);

        $filenames = array_map(fn ($f) => $f->getFilename(), $files);

        $this->assertContains('create_acquaintances_interactions_table.php', $filenames);
        $this->assertContains('create_acquaintances_friendship_table.php', $filenames);
        $this->assertNotContains('create_acquaintances_verification_table.php', $filenames);
        $this->assertNotContains('create_acquaintances_verifications_groups_table.php', $filenames);
    }

    /** @test */
    public function test_interactions_only_mode_excludes_friendships_and_verifications(): void
    {
        $this->app['config']->set('acquaintances.features.friendships', false);
        $this->app['config']->set('acquaintances.features.verifications', false);

        $provider = new AcquaintancesServiceProvider($this->app);

        $method = new \ReflectionMethod($provider, 'getEnabledMigrationFiles');
        $files = $method->invoke($provider);

        $filenames = array_map(fn ($f) => $f->getFilename(), $files);

        $this->assertCount(1, $filenames);
        $this->assertContains('create_acquaintances_interactions_table.php', $filenames);
    }

    /** @test */
    public function test_get_migration_files_for_feature_returns_correct_files(): void
    {
        $provider = new AcquaintancesServiceProvider($this->app);

        $method = new \ReflectionMethod($provider, 'getMigrationFilesForFeature');

        $interactionFiles = array_map(fn ($f) => $f->getFilename(), $method->invoke($provider, 'interactions'));
        $this->assertCount(1, $interactionFiles);
        $this->assertContains('create_acquaintances_interactions_table.php', $interactionFiles);

        $friendshipFiles = array_map(fn ($f) => $f->getFilename(), $method->invoke($provider, 'friendships'));
        $this->assertCount(2, $friendshipFiles);
        $this->assertContains('create_acquaintances_friendship_table.php', $friendshipFiles);
        $this->assertContains('create_acquaintances_friendships_groups_table.php', $friendshipFiles);

        $verificationFiles = array_map(fn ($f) => $f->getFilename(), $method->invoke($provider, 'verifications'));
        $this->assertCount(2, $verificationFiles);
        $this->assertContains('create_acquaintances_verification_table.php', $verificationFiles);
        $this->assertContains('create_acquaintances_verifications_groups_table.php', $verificationFiles);
    }
}
