<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\State;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Query\Builder;
use Simtabi\Laranail\Demo\Mode\Contracts\StateStore;
use Throwable;

/**
 * Persists the override in the demo_state table. Reads are resilient: when the
 * table is absent (e.g. right after migrate:fresh) the store reports "no
 * override" rather than throwing.
 */
final readonly class DatabaseStateStore implements StateStore
{
    private const string KEY = 'override';

    public function __construct(private ConnectionResolverInterface $db) {}

    public function get(): ?bool
    {
        try {
            $row = $this->table()->where('key', self::KEY)->value('value');

            return $row === null ? null : (bool) $row;
        } catch (Throwable) {
            return null;
        }
    }

    public function set(bool $active): void
    {
        try {
            $this->table()->updateOrInsert(
                ['key' => self::KEY],
                ['value' => $active, 'updated_at' => now()],
            );
        } catch (Throwable) {
            // Best effort.
        }
    }

    public function forget(): void
    {
        try {
            $this->table()->where('key', self::KEY)->delete();
        } catch (Throwable) {
            // Best effort.
        }
    }

    private function table(): Builder
    {
        return $this->db->connection()->table('demo_state');
    }
}
