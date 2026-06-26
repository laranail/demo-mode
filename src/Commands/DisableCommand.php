<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Commands;

final class DisableCommand extends Command
{
    protected $signature = 'laranail::demo-mode.disable';

    protected $description = 'Disable demo mode (runtime override)';

    /**
     * @var list<string>
     */
    protected array $commandAliases = ['demo:disable'];

    public function handle(): int
    {
        $this->demo()->disable();
        $this->info('Demo mode disabled.');

        return self::SUCCESS;
    }
}
