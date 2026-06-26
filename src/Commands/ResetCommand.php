<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Commands;

use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;

final class ResetCommand extends Command
{
    protected $signature = 'laranail::demo-mode.reset
                            {--strategy= : Override the configured reset strategy}
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Reset the demo to its baseline (data, files, cache)';

    /**
     * @var list<string>
     */
    protected array $commandAliases = ['demo:reset'];

    public function handle(): int
    {
        if (! $this->option('force')
            && ! $this->confirm('This will reset the application to its demo baseline. Continue?')) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        try {
            $this->demo()->reset($this->option('strategy'));
        } catch (DemoModeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Demo reset complete.');

        return self::SUCCESS;
    }
}
