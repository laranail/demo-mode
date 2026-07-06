# Changelog

All notable changes to `laranail/demo-mode` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-07-06

### Added

- Author-dictated activation: `manual | license | both | environment | schedule`
  triggers with granular bypass (role / ability / id / IP / gate).
- License-aware activation via a soft `LicenseGateway` adapter over
  `laranail/license-verifier` (degrades to manual when the verifier is absent).
- Layered write protection: HTTP read-only middleware, per-operation Eloquent
  guard (`PreventsDemoWrites` trait + config-driven models), protected
  attributes, and an opt-in strict connection guard that also catches mass
  `update()`/`delete()`.
- Write strategies: `block | ephemeral | readonly-connection | allow`.
- Named feature gating (config + runtime + `demo.feature` middleware + blade),
  entitlement-aware.
- Reset/restore: `migrate-fresh-seed | snapshot | sql-dump | backup-restore |
  callback` strategies, granular scope toggles, lock + min-interval, environment
  safety gate, demo-state re-establishment, scheduling and an on-demand route.
- Per-visitor sandbox strategies: `shared | transactional | scoped` (plus a
  `tenant` integration hook).
- Affordances: dismissible banner with reset countdown, auto-login, side-effect
  guards (mail/broadcasting), console-command guard.
- Fluent per-model `DemoRule` builder
  (`DemoRule::for(User::class)->block()->allow('create')->protectAttributes('email')`),
  additive with the trait/config and consumed by the write guard.
- Optional audit logging: blocked attempts (`demo_blocked_logs`) and completed
  resets (`demo_reset_logs`), toggled by `logging.blocked` / `logging.reset`
  (+ an optional log channel).
- CLI: `laranail::demo-mode.{status,enable,disable,reset,snapshot}` (+ `demo:*`).
- Blade directives, base controller, controller/model traits, events, and a
  full configuration surface.
