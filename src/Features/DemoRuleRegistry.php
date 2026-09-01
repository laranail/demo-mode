<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Features;

/**
 * Holds the per-model {@see DemoRule}s defined fluently at runtime
 * (request-scoped singleton). Not derived from config, so it is never flushed by
 * a config change — only explicitly via {@see flush()} (tests / manual reset).
 */
final class DemoRuleRegistry
{
    /**
     * @var array<class-string, DemoRule>
     */
    private array $rules = [];

    /**
     * @param  class-string  $class
     */
    public function for(string $class): DemoRule
    {
        return $this->rules[$class] ??= new DemoRule;
    }

    /**
     * @param  class-string  $class
     */
    public function get(string $class): ?DemoRule
    {
        return $this->rules[$class] ?? null;
    }

    public function flush(): void
    {
        $this->rules = [];
    }
}
