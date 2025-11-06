<?php

// This file contains code snippets for the Getting Started guide
// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubException;

// snippet.initialize_pubnub
// Create a new configuration instance
$pnConfiguration = new PNConfiguration();

// Set subscribe key (required)
$pnConfiguration->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo"); // Replace with your subscribe key

// Set publish key (only required if publishing)
$pnConfiguration->setPublishKey(getenv("PUBLISH_KEY") ?? "demo"); // Replace with your publish key

// Set UUID (required to connect)
$pnConfiguration->setUserId("php-sdk-user-" . uniqid());

// Set up cryptography for message encryption (optional)
// $pnConfiguration->setCryptoModule(CryptoModule::aesCbcCryptor("your-cipher-key", true));

// Configure connection timeout in seconds
$pnConfiguration->setConnectTimeout(10);

// Create PubNub instance with the configured settings
$pubnub = new PubNub($pnConfiguration);
// snippet.end

// snippet.event_listeners
class MySubscribeCallback extends SubscribeCallback {
    function status($pubnub, $status) {
        if ($status->getCategory() === PNStatusCategory::PNUnexpectedDisconnectCategory) {
            // This event happens when radio / connectivity is lost
            echo "Unexpected disconnect - network may be down" . PHP_EOL;
        } else if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            // Connect event. You can do stuff like publish, and know you'll get it
            echo "Connected to PubNub!" . PHP_EOL;
        } else if ($status->getCategory() === PNStatusCategory::PNDecryptionErrorCategory) {
            // Handle message decryption error
            echo "Decryption error: " . $status->getException() . PHP_EOL;
        }
    } 

    function message($pubnub, $message) {
        // Handle new message stored in message.message
        echo "Received message: " . json_encode($message->getMessage()) . PHP_EOL;
        echo "Publisher: " . $message->getPublisher() . PHP_EOL;
        echo "Channel: " . $message->getChannel() . PHP_EOL;
        echo "Timetoken: " . $message->getTimetoken() . PHP_EOL;
    }

    function presence($pubnub, $presence) {
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
// Subscribe to a channel
$pubnub->subscribe()
    ->channels("hello_world")
    ->withPresence(true)  // Optional: subscribe to presence events
    ->execute();
// snippet.end

// snippet.publish_message
// Assuming $pubnub is already initialized

try {
    // Create message data
    $messageData = [
        "text" => "Hello from PHP SDK!",
        "timestamp" => time(),
        "sender" => [
            "name" => "PHP Publisher",
            "id" => $pnConfiguration->getUserId()
        ]
    ];

    // Publish a message to a channel
    $result = $pubnub->publish()
        ->channel("hello_world")              // Channel to publish to
        ->message($messageData)               // Message content
        ->shouldStore(true)                   // Store in history
        ->sync();                             // Execute synchronously

    // Display success message
    echo "Message published successfully!" . PHP_EOL;
    echo "Timetoken: " . $result->getTimetoken() . PHP_EOL;

    // Convert timetoken to readable date
    $timestamp = floor($result->getTimetoken() / 10000000);
    $readableDate = date('Y-m-d H:i:s', $timestamp);
    echo "Published at: " . $readableDate . PHP_EOL;

    // Display published message
    echo PHP_EOL . "Published message: " . PHP_EOL;
    echo json_encode($messageData, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (PubNubServerException $exception) {
    // Handle PubNub server-specific errors
    echo "Error publishing message: " . $exception->getMessage() . PHP_EOL;

    if (method_exists($exception, 'getServerErrorMessage') && $exception->getServerErrorMessage()) {
        echo "Server Error: " . $exception->getServerErrorMessage() . PHP_EOL;
    }

    if (method_exists($exception, 'getStatusCode') && $exception->getStatusCode()) {
        echo "Status Code: " . $exception->getStatusCode() . PHP_EOL;
    }
} catch (PubNubException $exception) {
    // Handle PubNub-specific errors
    echo "PubNub Error: " . $exception->getMessage() . PHP_EOL;
} catch (Exception $exception) {
    // Handle general exceptions
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
// snippet.end

