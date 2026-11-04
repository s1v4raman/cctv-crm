<?php

namespace Tests\Feature;

use App\Support\SharedDatabase;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoAnalyticsSeeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

/**
 * The local .env can point at the live Neon database. From a laptop APP_ENV is local, so
 * Laravel would run migrate:fresh or the demo seeders against it without asking, deleting
 * every account or resetting passwords. These commands only run on a local database.
 */
class SharedDatabaseGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::prohibitDestructiveCommands(false);

        parent::tearDown();
    }

    private function useRemotePostgres(array $connection): void
    {
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql' => array_merge(config('database.connections.pgsql'), ['url' => null], $connection),
        ]);
    }

    public function test_sqlite_and_local_servers_are_not_shared(): void
    {
        $this->assertFalse(SharedDatabase::inUse());

        $this->useRemotePostgres(['host' => '127.0.0.1']);
        $this->assertFalse(SharedDatabase::inUse());

        $this->useRemotePostgres(['url' => 'postgresql://crm:secret@localhost:5432/crm']);
        $this->assertFalse(SharedDatabase::inUse());
    }

    public function test_a_hosted_database_is_shared(): void
    {
        $this->useRemotePostgres(['host' => 'ep-example-123456.ap-southeast-1.aws.neon.tech']);
        $this->assertTrue(SharedDatabase::inUse());

        $this->useRemotePostgres(['host' => '127.0.0.1', 'url' => 'postgresql://crm:secret@ep-example-123456.aws.neon.tech/neondb?sslmode=require']);
        $this->assertTrue(SharedDatabase::inUse());

        // An address that cannot be read is not known to be local
        $this->useRemotePostgres(['url' => 'not a url']);
        $this->assertTrue(SharedDatabase::inUse());
    }

    public function test_commands_that_drop_every_table_refuse_a_shared_database(): void
    {
        $this->useRemotePostgres(['host' => 'ep-example-123456.aws.neon.tech']);
        SharedDatabase::guardDestructiveCommands();

        $this->artisan('migrate:fresh', ['--force' => true])->assertFailed();
        $this->artisan('migrate:refresh', ['--force' => true])->assertFailed();
        $this->artisan('db:wipe', ['--force' => true])->assertFailed();
    }

    public function test_seeders_that_reset_logins_refuse_a_shared_database(): void
    {
        $this->useRemotePostgres(['host' => 'ep-example-123456.aws.neon.tech']);

        foreach ([DatabaseSeeder::class, DemoAnalyticsSeeder::class] as $seeder) {
            try {
                $this->app->make($seeder)->run();
                $this->fail("{$seeder} ran on a shared database");
            } catch (RuntimeException $e) {
                $this->assertStringContainsString('does not run on a shared database', $e->getMessage());
            }
        }
    }
}
