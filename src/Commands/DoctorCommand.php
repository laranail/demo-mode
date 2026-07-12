<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Commands;

use Simtabi\Laranail\Demo\Mode\Doctor\Checks;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorReporter;

final class DoctorCommand extends Command
{
    protected $signature = 'laranail::demo-mode.doctor {--json}';

    protected $description = 'Diagnose the demo-mode configuration and environment';

    /** @var list<string> */
    protected array $commandAliases = ['demo:doctor'];

    public function handle(): int
    {
        return DoctorReporter::render($this, Checks::all(), (bool) $this->option('json'));
    }
}
