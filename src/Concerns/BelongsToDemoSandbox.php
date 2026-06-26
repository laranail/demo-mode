<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Demo\Mode\DemoMode;
use Simtabi\Laranail\Demo\Mode\Sandbox\SandboxContext;

/**
 * Row-level per-visitor isolation for the `scoped` sandbox strategy. Add a
 * nullable `demo_sandbox_id` column to the model's table; rows created in a demo
 * session are stamped with the visitor's sandbox id and queries are scoped to it.
 */
trait BelongsToDemoSandbox
{
    public static function bootBelongsToDemoSandbox(): void
    {
        static::creating(static function (Model $model): void {
            $id = app(SandboxContext::class)->id();

            if ($id !== null && app(DemoMode::class)->isActive() && $model->getAttribute('demo_sandbox_id') === null) {
                $model->setAttribute('demo_sandbox_id', $id);
            }
        });

        static::addGlobalScope('demoSandbox', static function (Builder $builder): void {
            $context = app(SandboxContext::class);

            if ($context->id() !== null && app(DemoMode::class)->isActive()) {
                $builder->where($builder->getModel()->getTable().'.demo_sandbox_id', $context->id());
            }
        });
    }
}
