# Developers

Notes for working on the SDK itself. For using the SDK, see [README.md](README.md). AI coding agents should start with [AGENTS.md](AGENTS.md).

## Supported PHP versions

PHP 8.1 or later, as declared in `composer.json`. CI runs the test suite on PHP 8.1, 8.2, 8.3, 8.4 and 8.5.

## Supported platforms

CI runs on Linux through GitHub Actions. Windows, macOS and BSD are not tested in CI.

## Subscription

The subscribe loop in the PHP SDK is synchronous: `$pubnub->subscribe()->...->execute()` blocks while it long-polls for messages. PHP has thread and event-loop libraries, but none is stable enough to build on, and the language wasn't designed for asynchronous work. The main purpose of the PHP SDK is making synchronous endpoint calls.

To leave the loop, throw `PubNubUnsubscribeException` from a `SubscribeCallback` method.

## Third-party libraries

### HTTP

Requests go through a PSR-18 HTTP client. The default is [Guzzle](https://github.com/guzzle/guzzle). `$pubnub->setClient()` accepts any `Psr\Http\Client\ClientInterface`; see `examples/customClient.php`. [Requests](https://github.com/WordPress/Requests) (`rmccue/requests`) is still used to upload files to storage in `SendFile`.

### Logging

`PubNub` logs to a `Psr\Log\NullLogger` by default. Any PSR-3 `Psr\Log\LoggerInterface` can be set with `$pubnub->setLogger(LoggerInterface $logger)`. Monolog is only a development dependency.

## Tests

All PHPUnit tests use PHPUnit 9. Run the commands from the repository root.

| Directory | What it covers | Needs PubNub keys |
| --- | --- | --- |
| `tests/unit` | Pure logic: crypto, models, validation | No |
| `tests/functional` | Request building and validation | Mostly no |
| `tests/integrational` | Requests with stubbed HTTP responses, plus live calls | Only the live tests |
| `tests/Examples` | Runs the snippets in `examples/` | Uses the `demo` keyset |
| `tests/Acceptance` | Behat contract tests against PubNub's mock server | Runs in CI only |

```bash
php vendor/bin/phpunit tests/unit                 # offline unit tests
php vendor/bin/phpunit tests/unit/UtilsTest.php   # one file
php vendor/bin/phpunit                            # functional and integrational suites from phpunit.xml
composer test                                     # everything under tests/, as CI runs it (bash only)
```

`tests/unit` isn't part of the `phpunit.xml` suites, so pass it as a path.

Live tests read keys from environment variables. To run them locally:
1. Copy `.env.dist` to `.env.dev`, which is git-ignored.
2. Fill in your keys. `tests/bootstrap.php` loads the file.
3. For the Data Sync and grant-category tests, also set the `DATASYNC_*` and `CATEGORY_*` variables. Without them those tests skip themselves.

## Static analysis and code style

```bash
php vendor/bin/phpstan analyze --memory-limit 512M   # PHPStan level 6 over src/ and tests/
php vendor/bin/phpcs --standard=PSR12 <files>         # PSR-12
composer lint                                         # both, as CI runs them (bash only)
```

- CI runs PHPCS only on PHP files changed against `origin/master`.
- Fix new PHPStan errors rather than adding them to `phpstan-baseline.neon`.

## Line endings

`.gitattributes` keeps text files LF in the working tree on every platform. If your checkout predates that rule, PHPCS reports `End of line character is invalid`. Fix it by refreshing the checkout once. Commit or stash your work first, because `git reset --hard` discards uncommitted changes:

```bash
git rm -r --cached -q .
git reset --hard
```

## Releases

Releases are automated:
1. The PubNub release bot is triggered from a PR comment.
2. It bumps the version in `composer.json`, `.pubnub.yml` and `SDK_VERSION` in `src/PubNub/PubNub.php`, and writes the changelog entries in `.pubnub.yml` and `CHANGELOG.md`.
3. Merging the PR to `master` publishes the package to Packagist and creates the GitHub release.

Don't bump versions or edit changelog entries by hand.
