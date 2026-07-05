# laranail/demo-mode

[![Latest version on Packagist](https://img.shields.io/packagist/v/laranail/demo-mode.svg)](https://packagist.org/packages/laranail/demo-mode)
[![Tests](https://github.com/laranail/demo-mode/actions/workflows/tests.yml/badge.svg)](https://github.com/laranail/demo-mode/actions/workflows/tests.yml)
[![Static analysis](https://github.com/laranail/demo-mode/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/laranail/demo-mode/actions/workflows/static-analysis.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

> A granular, license-aware demo / sandbox controller for Laravel products — block writes, gate features, reset data periodically, isolate visitors, auto-login, and show a banner, all configurable with sensible defaults.

For Laravel `^13`. Complements [`laranail/license-verifier`](https://opensource.simtabi.com/documentation/laranail/license-verifier/) (a trial app can drop into demo mode automatically) but works standalone with no hard dependency.

## Install

```bash
composer require laranail/demo-mode
php artisan vendor:publish --tag="demo-mode-config"
```

## Documentation

Full documentation is at **[opensource.simtabi.com/documentation/laranail/demo-mode](https://opensource.simtabi.com/documentation/laranail/demo-mode/)** — getting started, activation triggers, write protection, per-model/route/feature gating, reset strategies, per-visitor sandboxing, middleware, the Artisan commands, and configuration.

## Contributing & security

Issues and PRs are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities per
[SECURITY.md](SECURITY.md) (opensource@simtabi.com); participation follows the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

MIT © Simtabi LLC. See [LICENSE](LICENSE).
