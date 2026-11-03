---
name: run-checks
description: Runs the PubNub PHP SDK verification steps (offline PHPUnit tests, targeted tests, PHPStan level 6 and PHPCS PSR-12 on changed files) and separates real failures from Windows line-ending noise. Use after changing PHP code, before committing or opening a PR, or when asked to run tests, lint or static analysis.
---

# Run checks

Run everything from the repository root with `php vendor/bin/...`, which works in bash and PowerShell. `composer test` and `composer lint` are bash-only. If `vendor/` is missing, run `composer install` first.

## 1. Offline tests (always)

```bash
php vendor/bin/phpunit --do-not-cache-result tests/unit
php vendor/bin/phpunit --do-not-cache-result <each test file you added or changed>
```

- `tests/unit` is not part of the `phpunit.xml` suites, so it must be passed as a path.
- Skips for missing extensions (mcrypt, pthreads) are expected. Everything else must pass.

## 2. Targeted tests for the area you touched

`tests/functional` and `tests/integrational` mix offline and live tests. Run the specific files for your feature, for example:

```bash
php vendor/bin/phpunit --do-not-cache-result tests/integrational/HereNowTest.php
```

Run the whole configured suites (`php vendor/bin/phpunit`) or `composer test` only when the user asks and keys are configured (environment variables, or `.env.dev` based on `.env.dist`). They publish to real keysets.

## 3. Static analysis

```bash
php vendor/bin/phpstan analyze --memory-limit 512M --no-progress <changed src and tests files>
php vendor/bin/phpstan analyze --memory-limit 512M --no-progress
```

The second command analyses the whole project, as CI does. Fix new errors in the code; never add them to `phpstan-baseline.neon`.

## 4. Code style

```bash
php vendor/bin/phpcs --standard=PSR12 <changed .php files>
```

- CI lints the files changed against `origin/master`: `git diff --name-only --diff-filter=d origin/master HEAD`. Include new, uncommitted files yourself.
- `php vendor/bin/phpcbf --standard=PSR12 <files>` fixes issues automatically, but it rewrites the files.

## Line-ending false positives

`.gitattributes` forces LF line endings. A Windows checkout made before that rule existed can still contain CRLF files, while CI checks out LF and sees none of the following:

- phpcs reports `End of line character is invalid` on every file. Re-run with `--exclude=Generic.Files.LineEndings`.
- Full-project PHPStan reports baseline mismatches in files you didn't touch (for example `SetState.php` and `PresenceEnvelope.php`).

Report these as environment noise, not as failures, and tell the user the checkout needs refreshing. Don't run destructive git commands to fix it yourself.

## Report

Summarize:
- which tests ran, with counts
- the static analysis and style results
- anything you didn't run (such as live suites), and why
