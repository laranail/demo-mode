<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Commands;

use Simtabi\Laranail\Demo\Mode\Doctor\ResetEnvironmentCheck;
use Simtabi\Laranail\Demo\Mode\Doctor\ResetStrategyCheck;
use Simtabi\Laranail\Demo\Mode\Doctor\StateStoreCheck;
use Simtabi\Laranail\Demo\Mode\Doctor\VerifierPresentCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorCheck;
use Simtabi\Laranail\Package\Tools\Services\Doctor\DoctorService;

final class DoctorCommand extends Command
{
    protected $signature = 'laranail::demo-mode.doctor {--json}';

    protected $description = 'Diagnose the demo-mode configuration and environment';

    /** @var list<string> */
    protected array $commandAliases = ['demo:doctor'];

    /**
     * Canonical demo-mode health checks, reused by the service provider to feed
     * the unified package-tools doctor.
     *
     * @var list<class-string<DoctorCheck>>
     */
    public const array CHECKS = [
        StateStoreCheck::class,
        ResetStrategyCheck::class,
        ResetEnvironmentCheck::class,
        VerifierPresentCheck::class,
    ];

    public function handle(): int
    {
        $service = new DoctorService;

        foreach (self::CHECKS as $check) {
            $service->register($check);
        }

        $report = $service->run();
        $summary = $service->summarise($report);
        $failed = $summary['fail'] > 0;

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode([
                'status' => $failed ? 'degraded' : 'ok',
                'summary' => $summary,
                'checks' => array_map(static fn (array $row): array => [
                    'name' => $row['check']->name(),
                    'status' => $row['result']->status->value,
                    'message' => $row['result']->message,
                    'detail' => $row['result']->detail,
                ], $report),
            ], JSON_PRETTY_PRINT));

            return $failed ? self::FAILURE : self::SUCCESS;
        }

        $this->table(['', 'Check', 'Result'], array_map(static fn (array $row): array => [
            $row['result']->status->symbol(),
            $row['check']->name(),
            $row['result']->message,
        ], $report));

        $this->line(sprintf(
            '%d passed, %d warning(s), %d failure(s), %d skipped.',
            $summary['pass'],
            $summary['warn'],
            $summary['fail'],
            $summary['skip'],
        ));

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
