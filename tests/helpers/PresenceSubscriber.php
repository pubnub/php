<?php

/**
 * Helper script to subscribe a client to a channel for presence testing.
 * This script is meant to be run as a background process.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\Consumer\PubSub\PNPresenceEventResult;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubUnsubscribeException;

if ($argc < 3) {
    fwrite(STDERR, "Usage: php PresenceSubscriber.php <channel> <uuid> [duration_seconds]\n");
    exit(1);
}

$channel = $argv[1];
$uuid = $argv[2];
$duration = isset($argv[3]) ? (int)$argv[3] : 30; // Default 30 seconds

$config = new PNConfiguration();
$config->setSubscribeKey(getenv("SUBSCRIBE_KEY"));
$config->setPublishKey(getenv("PUBLISH_KEY"));
$config->setUuid($uuid);

$pubnub = new PubNub($config);

// Simple callback that just maintains presence
class PresenceCallback extends SubscribeCallback
{
    private int $startTime;
    private int $duration;

    public function __construct(int $duration)
    {
        $this->startTime = time();
        $this->duration = $duration;
    }

    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
     * @return void
     */
    public function status($pubnub, $status): void
    {
        // Exit if connected and duration exceeded
        if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            fwrite(STDOUT, "Connected: {$pubnub->getConfiguration()->getUuid()}\n");
            flush();
        }

        // Check if we should exit
        if (time() - $this->startTime > $this->duration) {
            throw new PubNubUnsubscribeException();
        }
    }

    /**
     * @param PubNub $pubnub
     * @param PNMessageResult $message
     * @return void
     */
    public function message($pubnub, $message): void
    {
        // Check if we should exit
        if (time() - $this->startTime > $this->duration) {
            throw new PubNubUnsubscribeException();
        }
    }

    /**
     * @param PubNub $pubnub
     * @param PNPresenceEventResult $presence
     * @return void
     */
    public function presence($pubnub, $presence): void
    {
        // Do nothing
    }
}

try {
    fwrite(STDOUT, "Starting subscription for $uuid on channel $channel\n");
    flush();

    $pubnub->addListener(new PresenceCallback($duration));

    fwrite(STDOUT, "Listener added, calling subscribe...\n");
    flush();

    $pubnub->subscribe()->channels($channel)->withPresence()->execute();

    fwrite(STDOUT, "Subscribe returned (should not reach here normally)\n");
    flush();
} catch (PubNubUnsubscribeException $e) {
    fwrite(STDOUT, "Unsubscribed: $uuid\n");
    exit(0);
} catch (Exception $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Stack trace: " . $e->getTraceAsString() . "\n");
    exit(1);
}
