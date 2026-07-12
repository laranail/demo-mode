<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class DemoReset
{
    use Dispatchable;

    /**
     * @param  list<string>  $scope  the scopes that were reset (database, files, ...)
     */
    public function __construct(
        public string $strategy,
        public array $scope = [],
    ) {}
}
