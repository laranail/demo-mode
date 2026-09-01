<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Doctor;

use Simtabi\Laranail\Demo\Mode\Contracts\StateStore;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorResult;
use Throwable;

/**
 * The configured demo state store must be valid and reachable.
 */
final class StateStoreCheck implements DoctorCheck
{
    public function name(): string
    {
        return 'demo-mode:state-store';
    }

    public function description(): string
    {
        return 'The demo state store is configured and reachable';
    }

    public function run(): DoctorResult
    {
        $store = (string) config('demo-mode.state.store', 'cache');

        if (! in_array($store, ['config', 'cache', 'database'], true)) {
            return DoctorResult::fail("Unknown state store \"{$store}\".");
        }

        try {
            app(StateStore::class)->get();
        } catch (Throwable $e) {
            return DoctorResult::fail("State store \"{$store}\" is not reachable.", ['error' => $e->getMessage()]);
        }

        return DoctorResult::pass("State store \"{$store}\" is reachable.");
    }
}
