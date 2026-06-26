<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Guards;

use function class_uses_recursive;

use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Demo\Mode\Concerns\PreventsDemoWrites;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Events\DemoActionBlocked;
use Simtabi\Laranail\Demo\Mode\Exceptions\DemoModeException;

/**
 * Shared model-write guard used by both the {@see PreventsDemoWrites} trait and
 * the config-driven global observer. Throws {@see DemoModeException} when a
 * protected operation (or a change to a protected attribute) is attempted while
 * demo mode is active.
 *
 * NOTE: Eloquent events do NOT fire on mass update()/delete(); the strict
 * connection guard ({@see WriteBlockingConnection}) covers those.
 */
final readonly class EloquentWriteGuard
{
    public function __construct(private DemoMode $demo) {}

    public function guard(Model $model, string $operation): void
    {
        if (! $this->demo->isActive()) {
            return;
        }

        $blocked = $this->operationBlocked($model, $operation)
            || ($operation === 'update' && $this->touchesProtectedAttributes($model));

        if (! $blocked) {
            return;
        }

        $this->report($model, $operation);

        throw DemoModeException::writeBlocked(class_basename($model));
    }

    private function operationBlocked(Model $model, string $operation): bool
    {
        // Trait opt-in: all operations blocked except those listed in $demoAllowed.
        if (in_array(PreventsDemoWrites::class, class_uses_recursive($model), true)) {
            $allowed = property_exists($model, 'demoAllowed') ? (array) $model->demoAllowed : [];

            return ! in_array($operation, $allowed, true);
        }

        $protection = $this->configProtection($model::class);

        if ($protection === null) {
            return false;
        }

        if ($protection === true) {
            return true;
        }

        return (bool) ($protection[$operation] ?? false);
    }

    /**
     * @return true|array<string, bool>|null
     */
    private function configProtection(string $class): true|array|null
    {
        foreach ((array) config('demo-mode.write.protected_models', []) as $key => $value) {
            // Bare class name (numeric key) → all operations blocked.
            if (is_int($key) && $value === $class) {
                return true;
            }

            // class => ['delete' => true, ...] map.
            if ($key === $class && is_array($value)) {
                return $value;
            }
        }

        return null;
    }

    private function touchesProtectedAttributes(Model $model): bool
    {
        $map = (array) config('demo-mode.write.protected_attributes', []);
        $attributes = (array) ($map[$model::class] ?? []);

        if ($attributes === []) {
            return false;
        }

        return array_any($attributes, fn ($attribute) => $model->isDirty($attribute));
    }

    private function report(Model $model, string $operation): void
    {
        if ((bool) config('demo-mode.events.enabled', true)) {
            event(new DemoActionBlocked('model', $model::class.':'.$operation));
        }
    }
}
