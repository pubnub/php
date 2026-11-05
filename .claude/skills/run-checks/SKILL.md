---
name: run-checks
description: Runs the PubNub PHP SDK verification steps (offline PHPUnit tests, targeted tests, PHPStan level 6 and PHPCS PSR-12 on changed files) and separates real failures from Windows line-ending noise. Use after changing PHP code, before committing or opening a PR, or when asked to run tests, lint or static analysis.
---

# Run checks

Run one script from the repository root. It prints a one-line summary per step and only the first lines of any failure, which keeps the output small.

```bash
scripts/run-checks.sh [--full] [extra phpunit test paths...]
```

- Always runs `tests/unit`, then PHPStan and PHPCS (PSR-12) on PHP files changed against `origin/master` plus untracked ones.
- Pass the test files you added or changed as extra arguments, and targeted `tests/functional` or `tests/integrational` files for the area you touched.
- `--full` also runs PHPStan on the whole project, as CI does. Use it before a PR.
- If `vendor/` is missing, run `composer install` first.

## Reading the result

- Exit code 0 means every step passed. Skips for missing extensions (mcrypt, pthreads) are expected.
- Fix new PHPStan errors in the code; never add them to `phpstan-baseline.neon`.
- For more output, re-run with `MAX_LINES=200`, or run the failing tool alone (see "Commands" in `AGENTS.md`).
- `php vendor/bin/phpcbf --standard=PSR12 <files>` fixes style issues, but it rewrites the files.
- The script reports CRLF checkouts itself. Treat those findings as environment noise, tell the user the checkout needs refreshing, and don't run destructive git commands to fix it.
- Don't run the full suites (`php vendor/bin/phpunit`, `composer test`) unless the user asks and keys are configured. They publish to real keysets.

## Report

Summarize which tests ran (with counts), the static analysis and style results, and anything not run (such as live suites), and why.
