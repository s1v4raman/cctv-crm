<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * A developer's .env can point at the live database that Render uses (Neon). Commands that
 * drop tables or reset logins must never reach it from a laptop, where APP_ENV=local means
 * Laravel asks no "are you sure" question.
 */
class SharedDatabase
{
    /** Hosts that are this machine or a container next to it */
    private const LOCAL_HOSTS = ['127.0.0.1', 'localhost', '::1', 'host.docker.internal', 'db', 'mysql', 'mariadb', 'pgsql', 'postgres'];

    /**
     * Whether the default connection is a database server on another machine.
     */
    public static function inUse(): bool
    {
        $connection = config('database.connections.' . config('database.default'), []);

        if (($connection['driver'] ?? null) === 'sqlite') {
            return false;
        }

        if (! empty($connection['url'])) {
            $host = parse_url($connection['url'], PHP_URL_HOST);

            // An address that cannot be read is not known to be local
            if (! is_string($host) || $host === '') {
                return true;
            }
        } else {
            $host = (string) ($connection['host'] ?? '127.0.0.1');
        }

        return ! in_array(strtolower(trim($host, '[]')), self::LOCAL_HOSTS, true);
    }

    /**
     * migrate:fresh, migrate:refresh, migrate:reset and db:wipe drop every table, users included.
     */
    public static function guardDestructiveCommands(): void
    {
        DB::prohibitDestructiveCommands(static::inUse());
    }

    /**
     * For seeders that set known passwords on user accounts or clear tables.
     */
    public static function refuseSeeding(string $seeder): void
    {
        if (static::inUse()) {
            throw new RuntimeException(class_basename($seeder) . ' resets user logins and demo data, so it does not run on a shared database. Point DB_* in .env at a local database first.');
        }
    }
}
