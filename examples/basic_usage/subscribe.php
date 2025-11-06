<?php
// snippet.hide
// Ignoring example because of the "no side effects" rule
// phpcs:ignoreFile
// snippet.show

// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PubNub;
use PubNub\Enums\PNStatusCategory;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\PNConfiguration;

// snippet.initialize_pubnub
// Create a new configuration instance
$pnConfiguration = new PNConfiguration();

// Set subscribe key (required)
$pnConfiguration->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");

// Set publish key (only required if publishing)
$pnConfiguration->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");

// Set UUID (required to connect)
$pnConfiguration->setUserId("php-sdk-subscriber");

// Set up cryptography for message encryption (optional)
// $pnConfiguration->setCryptoModule(CryptoModule::aesCbcCryptor("your-cipher-key", true));

// Configure connection timeout in seconds
$pnConfiguration->setConnectTimeout(10);

// Create PubNub instance with the configured settings
$pubnub = new PubNub($pnConfiguration);
// snippet.end

// snippet.event_listeners
class MySubscribeCallback extends SubscribeCallback
{
    public function status($pubnub, $status)
    {
        if ($status->getCategory() === PNStatusCategory::PNUnexpectedDisconnectCategory) {
            // This event happens when radio / connectivity is lost
            echo "Unexpected disconnect - network may be down" . PHP_EOL;
        } elseif ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            // Connect event. You can do stuff like publish, and know you'll get it
            echo "Connected to PubNub!" . PHP_EOL;
        } elseif ($status->getCategory() === PNStatusCategory::PNDecryptionErrorCategory) {
            // Handle message decryption error
            echo "Decryption error: " . $status->getException() . PHP_EOL;
        }
    }

    public function message($pubnub, $message)
    {
        // Handle new message stored in message.message
        echo "Received message: " . json_encode($message->getMessage()) . PHP_EOL;
        echo "Publisher: " . $message->getPublisher() . PHP_EOL;
        echo "Channel: " . $message->getChannel() . PHP_EOL;
        echo "Timetoken: " . $message->getTimetoken() . PHP_EOL;
    }

    public function presence($pubnub, $presence)
    {
        // Handle incoming presence data
        echo "Presence event: " . $presence->getEvent() . PHP_EOL;
        echo "UUID: " . $presence->getUuid() . PHP_EOL;
        echo "Channel: " . $presence->getChannel() . PHP_EOL;
        echo "Occupancy: " . $presence->getOccupancy() . PHP_EOL;
    }
}

// Add listener
$subscribeCallback = new MySubscribeCallback();
$pubnub->addListener($subscribeCallback);
// snippet.end

// snippet.create_subscription
// Subscribe to a channel, this will block execution
$pubnub->subscribe()
    ->channels("hello_world")
    ->withPresence(true)  // Optional: subscribe to presence events
    ->execute();
// snippet.end
