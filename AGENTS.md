# AGENTS.md

Guidance for AI coding agents working on the PubNub PHP SDK, and the single source of truth for guidance shared by all agents. Cursor and other agents that read `AGENTS.md` (such as Codex) load it directly, and `CLAUDE.md` imports it for Claude Code. Area-specific rules live in `.cursor/rules/` and reusable workflows in `.claude/skills/` (see [Where to find more](#where-to-find-more)). The contributor guide for humans is `DEVELOPER.md`.

## What this repository is

- **PubNub** is a hosted real-time messaging platform. Clients publish JSON messages to named channels, and other clients receive them through long-lived HTTP subscribe requests. Add-on services cover presence, message history, access control, user and channel metadata, files, mobile push and typed records (Data Sync).
- **This repository** is the `pubnub/pubnub` Composer package: the PubNub SDK for PHP server-side and CLI applications. It requires PHP 8.1+; CI tests 8.1 to 8.5.
- **Everything is synchronous by design.** Each API call is one blocking HTTP request. Subscribe is a blocking long-poll loop that only ends when a listener throws `PubNubUnsubscribeException`. Don't introduce async code, threads or event loops.
- **It is one of many PubNub SDKs** (JavaScript, Swift, Kotlin, C#, Python, Go and others) that call the same REST API. Data written by one SDK is read by the others, so wire formats, crypto headers, request signatures and error semantics must stay compatible. Before adding a feature that exists elsewhere, check how the other SDKs and the public docs behave.
- Public docs: <https://www.pubnub.com/docs/sdks/php>. LLM-oriented doc index: <https://www.pubnub.com/llms-full.txt>.

## PubNub concepts

| Term | Meaning |
| --- | --- |
| Keyset | Publish key, subscribe key and secret key from the PubNub Admin Portal. The secret key is for servers only. `demo`/`demo` keys work for quick tests. |
| User ID | Required client identity, set with `PNConfiguration::setUserId()`. Older APIs and the wire format call it UUID (`uuid` query parameter). |
| Channel, channel group | A named message stream; a server-side named set of channels that can be subscribed to as one. |
| Timetoken | 17-digit timestamp in 100-nanosecond units since the Unix epoch. Orders messages and serves as the subscribe and history cursor. |
| Publish, Signal, Fire | A regular (optionally stored) message; a small, never-stored message; a message delivered only to PubNub Functions, not to subscribers. |
| Presence | Who is subscribed to a channel (`hereNow`, `whereNow`) and per-user state. This SDK sends no heartbeats. |
| Message Persistence | Stored history: `fetchMessages()` (v3), legacy `history()` (v2), `messageCounts()`, `deleteMessages()`. |
| Access Manager (PAM) | A server holding the secret key issues tokens with `grantToken()`; clients send them after `setToken()`. v2 `grant()`/`audit()`/`revoke()` with auth keys is legacy. |
| App Context | Formerly "Objects": user (UUID) and channel metadata plus memberships and members, under `/v2/objects`. |
| Message Actions | Reactions and receipts attached to a published message's timetoken. |
| Files | Upload a file to storage, then publish a file message; list, download and delete by file ID. |
| Mobile Push | Register device tokens (APNs2, FCM) to receive push notifications for channels. |
| Data Sync | Newer typed-record service (entities, users, channels, memberships, relationships) under `/v1/datasync`, with ETags and JSON Patch. |
| Crypto module | Optional message and file encryption. Its binary header format is shared by all SDKs. |

## Repository map

```text
src/PubNub/
  PubNub.php            Client: one factory method per API call; HTTP client, logger, token, crypto
  PNConfiguration.php   Keys, user ID, origin, timeouts, crypto; locked by `new PubNub($config)`
  DataSync.php          `$pubnub->dataSync()` facade with all Data Sync factories
  CryptoModule.php      Cryptor registry and header encoding; cryptors live in Crypto/
  Endpoints/            One class per REST call, grouped by feature; base class Endpoint.php
  Models/Consumer/      Result objects returned to users; Models/Server/ parses subscribe payloads
  Managers/             SubscriptionManager (subscribe loop), ListenerManager, StateManager, TokenManager...
  Builders/, Callbacks/ SubscribeBuilder and the SubscribeCallback listener base class
  Enums/, Exceptions/   PNOperationType, PNStatusCategory, PNHttpMethod...; PubNubException hierarchy
  PubNubUtil.php        URL encoding, request signing, JSON helpers
tests/
  unit/                 Offline tests; not in the phpunit.xml suites, so run them by path
  functional/           Request-building and validation tests, mostly offline
  integrational/        Stubbed-HTTP tests plus live tests against real keysets
  Examples/             Runs the files in examples/ against the live demo keyset
  Acceptance/           Behat contract tests against PubNub's mock server (CI only)
  helpers/              PsrStubClient/PsrStub HTTP stubs and other test helpers
examples/               Documentation snippets marked `// snippet.<name>` ... `// snippet.end`
.github/workflows/      CI: tests, lint, release automation
```

| Feature | Client methods | Code |
| --- | --- | --- |
| Publish, signals | `publish()`, `signal()`, `fire()` | `Endpoints/PubSub/` |
| Subscribe | `subscribe()`, `addListener()`, `removeListener()` | `Builders/`, `Managers/SubscriptionManager.php`, `Endpoints/PubSub/Subscribe.php` |
| Presence | `hereNow()`, `whereNow()`, `getState()`, `setState()` | `Endpoints/Presence/` |
| Message Persistence | `fetchMessages()`, `history()`, `messageCounts()`, `deleteMessages()` | `Endpoints/MessagePersistance/` (sic), `Endpoints/History*.php`, `Endpoints/MessageCount.php` |
| Channel groups | `addChannelToChannelGroup()`, `listChannelsInChannelGroup()`... | `Endpoints/ChannelGroups/` |
| Mobile Push | `addChannelsToPush()`, `listPushProvisions()`... | `Endpoints/Push/` |
| Access Manager | `grantToken()`, `revokeToken()`, `parseToken()`, `setToken()`, `grant()`, `audit()`, `revoke()` | `Endpoints/Access/`, `PubNubCborDecode.php` (token parsing) |
| App Context | `setChannelMetadata()`, `getUUIDMetadata()`, `setMembers()`, `manageMemberships()`... | `Endpoints/Objects/` |
| Message Actions | `addMessageAction()`, `getMessageActions()`, `removeMessageAction()` | `Endpoints/MessageActions/` |
| Files | `sendFile()`, `listFiles()`, `downloadFile()`, `getFileDownloadUrl()`, `deleteFile()`, `publishFileMessage()` | `Endpoints/FileSharing/` |
| Data Sync | `dataSync()->createEntity()`, `->updateUser()`... | `DataSync.php`, `Endpoints/DataSync/` |
| Time | `time()` | `Endpoints/Time.php` |
| Encryption | `PNConfiguration::setCipherKey()`, `setCryptoModule()` | `CryptoModule.php`, `Crypto/` |

## How a request works

```php
$result = $pubnub->publish()->channel('my_channel')->message(['text' => 'hi'])->sync();
```

1. The factory method returns a new endpoint object (`Endpoints\PubSub\Publish`). Its setters return `$this`.
2. `sync()` calls `validateParams()`, which throws `PubNubValidationException` before any I/O.
3. `getRequest()` builds the URL from `buildPath()` and `buildParams()` and the body from `buildData()`. `buildParams()` merges `customParams()` with the defaults `pnsdk`, `uuid` and `auth`, then signs the request with HMAC-SHA256 when a secret key is configured.
4. The request is created by a PSR-17 request factory (`$pubnub->setRequestFactory()`) and sent through a PSR-18 client: Guzzle 7 by default, replaceable with `$pubnub->setClient()`. There are no retries.
5. `parseResponse()` hands decoded JSON to `createResponse()`, which builds a result model from `Models/Consumer/`. HTTP errors become `PubNubServerException`, network failures and timeouts `PubNubConnectionException`, and invalid JSON `PubNubResponseParsingException`.
6. `sync()` throws those exceptions. `envelope()` returns a `PNEnvelope` holding the result and a `PNStatus` instead. There is no `async()`. The envelope is cached on the endpoint object, so calling `sync()` again does not resend until `clear()` is called.

Logging is PSR-3: `$pubnub->setLogger()` accepts any `LoggerInterface`, and the default is `NullLogger`.

## Commands

Run from the repository root, because `tests/bootstrap.php` loads `.env.dev` and the `PubNubTestCase` base class.

| Purpose | Command |
| --- | --- |
| Install dependencies | `composer install` (add `--no-dev` for production dependencies only) |
| Offline unit tests (the default check) | `php vendor/bin/phpunit --do-not-cache-result tests/unit` |
| One file or one test | `php vendor/bin/phpunit --do-not-cache-result --filter testName tests/path/FooTest.php` |
| Functional and integrational suites (need keys) | `php vendor/bin/phpunit --do-not-cache-result` |
| Everything, as CI runs it (needs keys and bash; writes coverage to `coverage.txt` when Xdebug is available) | `composer test` |
| Static analysis (PHPStan level 6) | `php vendor/bin/phpstan analyze --memory-limit 512M` |
| Code style (PSR-12) on changed files | `php vendor/bin/phpcs --standard=PSR12 <files>` |
| Behat acceptance tests (CI only, see below) | `composer acceptance-test` |

- `composer test`, `composer lint` and `composer acceptance-test` are bash scripts. On Windows, use the `php vendor/bin/...` forms.
- **Live tests** read keys from environment variables or a git-ignored `.env.dev`, which you create by copying `.env.dist`:
  - The main keys are `PUBLISH_KEY`, `SUBSCRIBE_KEY`, `SECRET_KEY`, `PUBLISH_PAM_KEY`, `SUBSCRIBE_PAM_KEY` and `SECRET_PAM_KEY`.
  - The Data Sync and category tests use separate keysets (`DATASYNC_*`, `CATEGORY_*`) and skip themselves without them. `.github/workflows/run-tests.yml` lists every variable CI sets.
  - Tests built on `PubNubTestCase` fail rather than skip without keys, with auth or network errors. That is not a regression from your change.
  - Live tests publish to real keysets, so don't run live suites unless asked.
- **`composer acceptance-test`** copies `.feature` files from an `sdk-specifications/` checkout that isn't part of this repository, and needs PubNub's mock server on `localhost:8090`. It normally only runs in CI.
- `.gitattributes` forces LF line endings, but a checkout made before that rule existed can still contain CRLF files. phpcs then reports `Generic.Files.LineEndings` on every file, and full-project PHPStan reports baseline mismatches in untouched files. CI (LF) sees neither. Locally, add `--exclude=Generic.Files.LineEndings`, judge PHPStan by the files you changed, and tell the user the checkout needs refreshing.
- The `run-checks` skill has the full verification workflow.

## Working rules

- **Keep backward compatibility.** This is a published library, and breaking changes need a major version.
  - Don't rename or remove public classes, methods or constants, or change their signatures, including misspelled names such as `MessagePersistance`.
  - Mark replacements with `@deprecated` and keep the old API working.
  - Before changing a method signature, search for overrides and call sites, including in `tests/`.
- **Keep examples current.** New or changed public APIs get a snippet in `examples/`.
- **Test every behavior change.** Prefer offline tests: `tests/unit`, or stubbed HTTP with `PsrStubClient` in `tests/integrational`. Never weaken or delete existing assertions to make a change pass.
- **Pass PHPStan level 6 and PSR-12** for new and changed code: 120-character lines, LF line endings. Don't add entries to `phpstan-baseline.neon`; fix the code.
- **Write new code in the modern style**, even next to older code: typed properties, typed parameters and return types, fluent setters returning `static` (or `self`). The codebase doesn't use `declare(strict_types=1)`; don't add it.
- **Errors:** missing or invalid input throws `PubNubValidationException` from `validateParams()`. All exceptions extend `PubNubException`, except the control-flow `PubNubUnsubscribeException`. Decryption failures use one generic message and must never expose crypto internals.
- **Leave versions and changelogs to the release bot.** Don't edit `SDK_VERSION`, the `version` in `composer.json`, `.pubnub.yml` or `CHANGELOG.md` unless asked. Suggest the changelog line for the PR description instead.
- **Protect secrets.** Never read, print or commit `.env.dev` or real keys. Examples and tests use `demo` keys, `getenv()` or stubs. Secret keys belong on servers only.
- **Don't modify** `vendor/`, `.github/.release/`, generated output such as `coverage.txt`, or local scratch folders (anything named `_TEMP-*`).
- **Keep parity with the other SDKs:** identical wire formats, and shared cross-SDK test vectors when they exist (the `port-cross-sdk-feature` skill).

## Known quirks

These are existing behaviors. Don't copy them into new code, and only fix them when asked, since some fixes change public behavior.

- `new PubNub($config)` locks the configuration, after which setters throw. Ways around the lock:
  - `setUuid()` and `setAuthKey()` skip it, and tests rely on that.
  - `$config->clone()` returns an unlocked copy.
  - `disableImmutableCheck()` turns the lock off, but only when called before the client is created; `PubNubTestCase` does this for `pubnub_demo`.
- `PNConfiguration::setCrypto()` is broken: it is typed for `PubNubCryptoCore`, which `CryptoModule` isn't. Use `setCryptoModule()`. Calling `setCipherKey()` twice, or `setUseRandomIV()` after a cipher key is set, calls methods `CryptoModule` doesn't have, so call `setUseRandomIV()` first.
- `PubNub::setCrypto()` only affects `fetchMessages()` and the file endpoints. Publish, subscribe and `history()` use the configuration's crypto.
- Default query parameters overwrite custom ones with the same key. Only `pnsdk`, `uuid`, `auth`, `channel`, `channel-group` and `category` (plus `meta` for publish and `state` for setState) are URL-encoded centrally. Other values must be pre-encoded, and are then signed double-encoded; `DataSyncEndpoint` encodes after signing to avoid that.
- Several older endpoints put user input into paths without `PubNubUtil::urlEncode()`. New code must encode every user-supplied segment.
- In `PNOperationType`, `PNManageMembersOperation` and `PNManageMembershipsOperation` share the value 56. New constants take the next unused number.
- The `Objects/Member/*` and `Objects/Membership/*` constructors swap the connect and request timeouts.
- In `SubscriptionManager::start()`, the `PubNubResponseParsingException` branch neither returns nor continues.
- Subscribe delivers App Context, message-action and file events to `message()` as plain `PNMessageResult` objects. Only presence, signals and Data Sync events have dedicated callbacks.
- `PubNubCryptoLegacy` needs mcrypt, which PHP no longer ships, so it is dead code.

## Where to find more

Area rules in `.cursor/rules/` are markdown with YAML frontmatter. Cursor attaches them automatically by glob. Other agents should read the matching rule before editing:

| When editing | Rule |
| --- | --- |
| Any PHP file | `.cursor/rules/php-conventions.mdc` |
| `Endpoints/`, `Models/Consumer/`, `PubNub.php`, `DataSync.php`, `PNOperationType.php` | `.cursor/rules/endpoints.mdc` |
| `Managers/`, `Builders/`, `Callbacks/`, `Endpoints/PubSub/Subscribe.php`, `Models/Server/` | `.cursor/rules/subscribe-loop.mdc` |
| `CryptoModule.php`, `Crypto/`, `PubNubCrypto*.php`, crypto tests | `.cursor/rules/crypto.mdc` |
| Data Sync code and tests | `.cursor/rules/data-sync.mdc` |
| `tests/`, `phpunit.xml*`, `behat.yml` | `.cursor/rules/tests.mdc` |
| `examples/`, `tests/Examples/` | `.cursor/rules/examples.mdc` |
| `composer.json`, `.pubnub.yml`, `CHANGELOG.md`, `.github/workflows/` | `.cursor/rules/release-files.mdc` |

Skills in `.claude/skills/`, loaded by both Claude Code and Cursor:

- `add-endpoint`: adds a REST endpoint with its factory method, operation type, result model, tests and example.
- `run-checks`: runs tests, PHPStan and PHPCS, and explains the line-ending false positives on older Windows checkouts.
- `port-cross-sdk-feature`: ports a feature from another PubNub SDK or a cross-SDK spec while keeping parity.

The PubNub MCP server (`https://mcp.pubnub.com`) is configured for this project in `.cursor/mcp.json` (Cursor) and `.mcp.json` (Claude Code). Each user enables it and signs in with their own PubNub account.
- Use it to look up PubNub documentation, REST API details and how other SDKs behave.
- It acts with the signed-in user's account permissions. Don't use its account-changing tools (creating or changing apps, keysets or Functions, publishing messages, deleting data) unless the user explicitly asks.
- Setup help: <https://www.pubnub.com/docs/ai/pubnub-mcp-server>.
