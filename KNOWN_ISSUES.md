# Known Issues — laravel-security

_Last checked: 2026-08-02_

## Failing tests

Could not run the test suite. `composer install --no-interaction` fails before `vendor/` is populated:

```
Warning: The lock file is not up to date with the latest changes in composer.json. You may be getting outdated dependencies. It is recommended that you run `composer update` or `composer update <package name>`.
- Required package "spatie/laravel-permission" is not present in the lock file.
This usually happens when composer files are incorrectly merged or the composer.json file is manually edited.
```

`composer.json` requires `spatie/laravel-permission` (`^6.0|^7.0`) but `composer.lock` predates that requirement, so the lock file and manifest are out of sync. `composer update` was intentionally not run (out of scope for this audit — it would change the lock file). As a result, no test/lint/static-analysis step could be executed for this package.

## Style / static-analysis debt

Not checked — no `vendor/` install succeeded, so `composer test`, `pint`, `phpstan`, and `rector` could not be run.

## TODO / FIXME markers

None found (`grep -rn "TODO\|FIXME" --include="*.php" src/ config/ database/` — no matches).

## Open GitHub issues

Not checked — the `gh` CLI is not installed in this environment.
