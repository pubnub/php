<img width="1920" height="600" alt="php-header" src="https://github.com/user-attachments/assets/0b1910d9-9955-49d5-b73a-9eca4142bb6d" />

# PubNub PHP SDK

[![Packagist](https://img.shields.io/packagist/v/pubnub/pubnub.svg)](https://packagist.org/packages/pubnub/pubnub) [![Build Status](https://travis-ci.com/pubnub/php.svg?branch=master)](https://travis-ci.com/pubnub/php) [![codecov](https://codecov.io/gh/pubnub/php/branch/master/graph/badge.svg)](https://codecov.io/gh/pubnub/php)

PubNub provides global infrastructure for real-time, interactive applications.

Publish and receive messages in PHP. Use this SDK for PHP server and command-line applications.

[Documentation](https://www.pubnub.com/docs/sdks/php) · [API reference](https://www.pubnub.com/docs/sdks/php/api-reference/publish-and-subscribe) · [Changelog](https://www.pubnub.com/docs/sdks/php/changelog) · [Support](https://support.pubnub.com/)

## Requirements

| Requirement | Supported version or setup |
| --- | --- |
| PHP | [PHP 8.1 or later](https://packagist.org/packages/pubnub/pubnub) |
| Dependency manager | [PHP environments supported by the current `pubnub/pubnub` package](https://packagist.org/packages/pubnub/pubnub) |
| Composer | Composer must be installed and available to the project. |

## Installation

From the Composer project directory:

```sh
composer require pubnub/pubnub
```

For other installation methods, see [environment setup](https://www.pubnub.com/docs/sdks/php).

## Quickstart

This example runs in PHP 8.1+ CLI. It subscribes to `hello_world`, publishes one message, and prints the received text.

### Get your keys

1. Open the [PubNub Admin Portal](https://admin.pubnub.com/).
2. Create an app and a keyset for development, or select an existing development keyset.
3. Copy its publish key and subscribe key.

If Access Manager is enabled, obtain a token from your trusted backend and call `$pubnub->setToken($token)`. Keep the secret key on the backend.

### Send and receive a message

Create `subscriber.php` and `publisher.php` in the Composer project root. Start `subscriber.php` first.

Replace the key placeholders. Use a User ID that identifies the user or device in your app.

`subscriber.php`:

```php
<?php
// subscriber.php
require __DIR__ . '/vendor/autoload.php';

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\PNConfiguration;
use PubNub\PubNub;

$config = new PNConfiguration();
$config->setSubscribeKey('YOUR_SUBSCRIBE_KEY');
$config->setPublishKey('YOUR_PUBLISH_KEY');
$config->setUserId('hello-world-subscriber');

$pubnub = new PubNub($config);

$listener = new class extends SubscribeCallback {
    public function status($pubnub, $status)
    {
    }

    public function message($pubnub, $message)
    {
        echo $message->getMessage() . PHP_EOL;
        throw (new PubNubUnsubscribeException())
            ->setChannels(['hello_world']);
    }

    public function presence($pubnub, $presence)
    {
    }
};

$pubnub->addListener($listener);
$pubnub->subscribe()
    ->channels(['hello_world'])
    ->execute();
```

Then create `publisher.php`:

```php
<?php
// publisher.php
require __DIR__ . '/vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;

$config = new PNConfiguration();
$config->setSubscribeKey('YOUR_SUBSCRIBE_KEY');
$config->setPublishKey('YOUR_PUBLISH_KEY');
$config->setUserId('hello-world-publisher');

$pubnub = new PubNub($config);

$pubnub->publish()
    ->channel('hello_world')
    ->message('Hello world')
    ->sync();
```

### Run the example

```sh
# Terminal 1
php subscriber.php

# Terminal 2, after the subscriber is running
php publisher.php
```

The subscriber terminal should show:

```text
Hello world
```

PHP Subscribe is blocking. The example throws `PubNubUnsubscribeException` after receiving the expected message so the subscribe loop exits.

For a complete application, see the [getting started guide](https://www.pubnub.com/docs/sdks/php).

## Next steps

| Task | Documentation |
| --- | --- |
| Configure the client | [Configuration](https://www.pubnub.com/docs/sdks/php/api-reference/configuration) |
| Work with subscriptions and messages | [Publish & Subscribe](https://www.pubnub.com/docs/sdks/php/api-reference/publish-and-subscribe) |
| Check channel occupancy | [Presence](https://www.pubnub.com/docs/sdks/php/api-reference/presence) |
| Read message history | [Message Persistence](https://www.pubnub.com/docs/sdks/php/api-reference/storage-and-playback) |

## Build with an AI coding assistant

The [PubNub MCP server](https://www.pubnub.com/docs/ai/pubnub-mcp-server) gives an AI coding assistant access to PubNub SDK documentation and PubNub APIs. Connect the assistant to the hosted server at [`https://mcp.pubnub.com`](https://mcp.pubnub.com), or run `npx @pubnub/mcp@latest` locally.

The [server repository](https://github.com/pubnub/pubnub-mcp-server) has setup steps for VS Code, Cursor, Claude Code, Claude Desktop, Codex, Gemini CLI, and OpenCode.

## Before production

Use [Access Manager](https://www.pubnub.com/docs/sdks/php/api-reference/access-manager) to grant each client the permissions it needs. Keep the secret key on your backend. Never include it in a distributed client.

PHP Subscribe is blocking. Explicitly end an active subscription when its owner is finished; `PubNubUnsubscribeException` can be thrown from a subscribe callback to exit the loop.

Live delivery through PubNub SDKs is at-most-once. A subscriber can miss messages while disconnected or if its buffer overflows. For longer-gap recovery, see [Message Persistence](https://www.pubnub.com/docs/sdks/php/api-reference/storage-and-playback).

## Troubleshooting

| Symptom | Check |
| --- | --- |
| PHP cannot load PubNub classes | Run `composer require pubnub/pubnub`, load `vendor/autoload.php`, and use PHP 8.1+. See the [PHP SDK](https://www.pubnub.com/docs/sdks/php) and [Packagist](https://packagist.org/packages/pubnub/pubnub). |
| A publish succeeds but no message appears | Check the message handler, subscription readiness, keyset, and channel name. |
| The PHP CLI process appears to hang after subscribing | Subscribe is intentionally blocking. Exit the active subscribe loop when the owning workflow is finished; the Quickstart uses `PubNubUnsubscribeException`. See [Publish & Subscribe](https://www.pubnub.com/docs/sdks/php/api-reference/publish-and-subscribe). |
| Extra clients or duplicate messages during development | For long-running PHP workers, reuse the PubNub client rather than creating another subscriber for every job. Do not leave subscribe loops active after their owning worker/workflow ends. |

For setup help, see [troubleshooting](https://www.pubnub.com/docs/sdks/php). Check [network status](https://status.pubnub.com/) for service incidents.

## Releases

Read the [changelog](https://www.pubnub.com/docs/sdks/php/changelog) before upgrading.

For a major version change, review the migration guidance linked from the [PHP SDK documentation](https://www.pubnub.com/docs/sdks/php) and the [PHP SDK changelog](https://www.pubnub.com/docs/sdks/php/changelog).

## Support and contributions

For setup or account help, contact [PubNub Support](https://support.pubnub.com/).

For a reproducible SDK bug, use [GitHub Issues](https://github.com/pubnub/php/issues). Include the SDK version, runtime, and a small reproduction with credentials removed.

Install Composer development dependencies, run the repository test suite, and include tests for behavioral changes before opening a pull request.

## License

See the [PubNub Software Development Kit License](https://github.com/pubnub/php/blob/master/LICENSE).
