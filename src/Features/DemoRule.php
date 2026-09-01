<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Features;

/**
 * A fluent, config-free per-model write rule, consumed by the
 * {@see EloquentWriteGuard}:
 *
 *   DemoRule::for(User::class)
 *       ->block()                          // block all operations…
 *       ->allow('create')                  // …except create
 *       ->protectAttributes('email', 'password');
 *
 * Semantics are ADDITIVE with an allow override: a write is blocked if this rule
 * (or the trait/config) blocks it, EXCEPT an explicit allow() which always wins.
 * `allow()` un-blocks the OPERATION only — protected attributes still apply.
 */
final class DemoRule
{
    private bool $blockAll = false;

    /**
     * @var array<string, bool> operation => allowed?
     */
    private array $operations = [];

    /**
     * @var list<string>
     */
    private array $attributes = [];

    /**
     * @param  class-string  $class
     */
    public static function for(string $class): self
    {
        return app(DemoRuleRegistry::class)->for($class);
    }

    public function block(): self
    {
        $this->blockAll = true;

        return $this;
    }

    public function allow(string ...$operations): self
    {
        foreach ($operations as $operation) {
            $this->operations[$operation] = true;
        }

        return $this;
    }

    public function deny(string ...$operations): self
    {
        foreach ($operations as $operation) {
            $this->operations[$operation] = false;
        }

        return $this;
    }

    public function protectAttributes(string ...$attributes): self
    {
        $this->attributes = array_values(array_unique([...$this->attributes, ...$attributes]));

        return $this;
    }

    public function allows(string $operation): bool
    {
        return ($this->operations[$operation] ?? null) === true;
    }

    public function blocks(string $operation): bool
    {
        if ($this->allows($operation)) {
            return false;
        }

        return ($this->operations[$operation] ?? null) === false || $this->blockAll;
    }

    public function protectsAttribute(string $attribute): bool
    {
        return in_array($attribute, $this->attributes, true);
    }

    /**
     * @return list<string>
     */
    public function protectedAttributes(): array
    {
        return $this->attributes;
    }
}
