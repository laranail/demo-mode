<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Reset;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Facades\File;
use Simtabi\Laranail\Demo\Mode\Contracts\ResetStrategy;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;

/**
 * Restores the database by replaying a SQL dump file (driver-agnostic: executes
 * the statements through the current connection).
 */
final readonly class SqlDumpStrategy implements ResetStrategy
{
    public function __construct(private ConnectionResolverInterface $db) {}

    public function reset(): void
    {
        $path = config('demo-mode.reset.sql_dump_path');

        if (! is_string($path) || ! File::exists($path)) {
            throw DemoModeException::resetRefused('reset.sql_dump_path is missing or not readable');
        }

        $this->db->connection()->unprepared((string) File::get($path));
    }

    public function name(): string
    {
        return 'sql-dump';
    }
}
