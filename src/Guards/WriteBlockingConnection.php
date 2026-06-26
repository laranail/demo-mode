<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Guards;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\DatabaseManager;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;
use Throwable;

/**
 * Strict, airtight write blocking. Registers a beforeExecuting hook on the
 * configured connections so it fires BEFORE every statement — including mass
 * Model::where()->update()/->delete(), which Eloquent observers do NOT fire on.
 *
 * Opt-in (demo-mode.write.strict_connection): the hook self-disables when the
 * option is off and resolves the orchestrator live. The package's own writes run
 * under withoutGuards() (isReadOnly() is false while guards are suspended) and
 * are therefore exempt.
 */
final class WriteBlockingConnection
{
    private const string WRITE = '/^\s*(insert|update|delete|truncate|drop|alter|replace|merge)\b/i';

    public static function install(Container $app): void
    {
        $db = $app->make('db');

        if (! $db instanceof DatabaseManager) {
            return;
        }

        $hook = static function (string $query) use ($app): void {
            if (! (bool) config('demo-mode.write.strict_connection', false)) {
                return;
            }

            if ($app->make(DemoMode::class)->isReadOnly() && preg_match(self::WRITE, $query) === 1) {
                throw DemoModeException::writeBlocked();
            }
        };

        foreach (self::connectionNames($db) as $name) {
            try {
                $db->connection($name)->beforeExecuting($hook);
            } catch (Throwable) {
                // A connection that can't be resolved at boot is simply skipped.
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function connectionNames(DatabaseManager $db): array
    {
        $names = array_keys((array) config('database.connections', []));

        if ($names === []) {
            $names = [$db->getDefaultConnection()];
        }

        return array_values(array_unique($names));
    }
}
