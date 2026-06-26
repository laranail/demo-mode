<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Commands;

final class StatusCommand extends Command
{
    protected $signature = 'laranail::demo-mode.status';

    protected $description = 'Show the current demo-mode status';

    /**
     * @var list<string>
     */
    protected array $commandAliases = ['demo:status'];

    public function handle(): int
    {
        $demo = $this->demo();

        $this->table(['Property', 'Value'], [
            ['Active', $demo->isActive() ? 'yes' : 'no'],
            ['Read-only', $demo->isReadOnly() ? 'yes' : 'no'],
            ['Reason', $demo->reason()],
            ['Trigger', (string) config('demo-mode.trigger', 'both')],
            ['Write strategy', (string) config('demo-mode.write.strategy', 'block')],
            ['Sandbox', (string) config('demo-mode.sandbox.strategy', 'shared')],
            ['Reset strategy', (string) config('demo-mode.reset.strategy', 'migrate-fresh-seed')],
            ['License present', $demo->license()->isPresent() ? 'yes' : 'no'],
        ]);

        return self::SUCCESS;
    }
}
