<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Http\Middleware;

use Closure;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wraps the request in a database transaction that is ALWAYS rolled back, so
 * writes appear to work (read-after-write within the request) but never persist.
 * Alias: `demo.ephemeral`. Active only when write.strategy = ephemeral.
 *
 * Caveat: only database writes roll back — files, mail, queue and (non-database)
 * session writes do not; compose with the side-effect guards.
 */
final readonly class EphemeralWrites
{
    public function __construct(private DatabaseManager $db) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (config('demo-mode.write.strategy') !== 'ephemeral') {
            return $next($request);
        }

        $connection = $this->db->connection();
        $connection->beginTransaction();

        try {
            return $next($request);
        } finally {
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }
        }
    }
}
