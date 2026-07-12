<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Commands;

final class EnableCommand extends Command
{
    protected $signature = 'laranail::demo-mode.enable';

    protected $description = 'Enable demo mode (runtime override)';

    /**
     * @var list<string>
     */
    protected array $commandAliases = ['demo:enable'];

    public function handle(): int
    {
        $this->demo()->enable();
        $this->info('Demo mode enabled.');

        return self::SUCCESS;
    }
}
