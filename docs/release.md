# Release

How `laranail/demo-mode` releases are cut — tag-driven, with Packagist updating from the tag.

## Steps

1. Update [`CHANGELOG.md`](../CHANGELOG.md): move the unreleased entries under a new
   `## [X.Y.Z] - YYYY-MM-DD` heading (Keep a Changelog format: Added / Changed / Fixed / Removed).
2. Keep every gate green:
   ```bash
   composer test    # pest
   composer lint    # pint + phpstan + rector --dry-run
   ```
3. Commit on `main` (ensure `git config user.email` is set to your GitHub no-reply address), then tag and push:
   ```bash
   git tag vX.Y.Z
   git push origin main --tags
   ```
4. Create the GitHub release from the tag with that version's `CHANGELOG.md` section as the body —
   never a bare stub. Packagist updates from the tag automatically.
5. `.github/workflows/update-changelog.yml` folds the published release notes back into
   `CHANGELOG.md` on `main`.

## Versioning

Semver. While on `0.x`, minor versions may contain breaking changes — pin to a patch range and read
the [CHANGELOG](../CHANGELOG.md) and [UPGRADE guide](../UPGRADE.md) before upgrading. From 1.0,
breaking changes to the public API (the `Demo` facade, the middleware and Blade surfaces, the
`Contracts\*` interfaces, the config schema in [configuration.md](configuration.md)) are a major
bump and must be documented in [`UPGRADE.md`](../UPGRADE.md).

---

[← Docs index](../README.md#documentation)
