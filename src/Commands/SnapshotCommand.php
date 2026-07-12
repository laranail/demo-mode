<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Commands;

use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;

final class SnapshotCommand extends Command
{
    protected $signature = 'laranail::demo-mode.snapshot {name? : Snapshot name}';

    protected $description = 'Capture the current database as the demo baseline snapshot';

    /**
     * @var list<string>
     */
    protected array $commandAliases = ['demo:snapshot'];

    public function handle(): int
    {
        try {
            $this->demo()->snapshot($this->argument('name'));
        } catch (DemoModeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Demo baseline snapshot captured.');

        return self::SUCCESS;
    }
}
