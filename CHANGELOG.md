# Changelog

All notable changes to `laranail/demo-mode` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Views and translations also answer to `laranail/demo-mode::`**, the composer package name, over
  the same paths as `laranail-demo-mode::`, which stays for Blade tags. This comes from
  `laranail/package-tools` `v0.1.3`.

### Changed

- **The on-demand reset route is named `laranail-demo-mode.reset`**, was `demo-mode.reset`. Route
  names share one flat registry with the host and every other package, so a bare name could be
  silently replaced. The URL (`POST {prefix}/reset`) is unchanged. Requires
  `laranail/package-tools` `^0.1.3` for `hasDeprecatedRouteNames()`.

- The PHP floor is `^8.4.1`, up from `^8.4`. `laranail/package-tools` and `laranail/console`
  are `^8.4.1`, so a resolver that took the manifest at its word and pinned the platform to
  8.4.0 could not install them. Dependabot does exactly that, and had been failing on it.

### Deprecated

- The `demo-mode.reset` route name. It still resolves through `route()` to the same URL, with one
  `E_USER_DEPRECATED` notice per process, until the next minor after 0.1. `Route::has()` and
  `routeIs()` do not see it; use `laranail-demo-mode.reset` there.

## [0.1.0] - 2026-07-11

Initial public release.

[Unreleased]: https://github.com/laranail/demo-mode/compare/v0.1.0...HEAD
