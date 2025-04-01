<?php

// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubException;

// Create configuration with demo keys
$pnConfig = new PNConfiguration();
$pnConfig->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");
$pnConfig->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");
$pnConfig->setUserId("php-publish-demo-user");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);

try {
    // Create message data
    $messageData = [
        "text" => "Hello from PHP SDK!",
        "timestamp" => time(),
        "sender" => [
            "name" => "PHP Publisher",
            "id" => "php-demo"
        ]
    ];

    // Publish a message to a channel
    $result = $pubnub->publish()
        ->channel("my_channel")               // Channel to publish to
        ->message($messageData)               // Message content
        ->shouldStore(true)                   // Store in history
        ->ttl(15)                             // Time to live (hours)
        ->usePost(true)                       // Use POST method
        ->customMessageType("text-message")   // Custom message type
        ->sync();                             // Execute synchronously

    // Display success message with timetoken
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
