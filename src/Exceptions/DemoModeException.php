<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Exceptions;

use RuntimeException;

/**
 * Thrown when a guarded action is attempted while demo mode is active, and when
 * an unsafe reset is refused.
 */
final class DemoModeException extends RuntimeException
{
    public static function actionBlocked(string $action): self
    {
        return new self("The action [{$action}] is disabled in demo mode.");
    }

    public static function writeBlocked(?string $target = null): self
    {
        return new self($target !== null
            ? "Writes to [{$target}] are disabled in demo mode."
            : 'Writes are disabled in demo mode.');
    }

    public static function resetRefused(string $reason): self
    {
        return new self("Demo reset refused: {$reason}.");
    }
}
