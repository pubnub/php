<?php

// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubServerException;

// Create configuration with demo keys
$pnConfig = new PNConfiguration();
$pnConfig->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");
$pnConfig->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");
$pnConfig->setUserId("php-time-example-user");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);

try {
    // Fetch the current PubNub timetoken
    $result = $pubnub->time()->sync();

    // Display the timetoken in different formats
    echo "PubNub Timetoken: " . $result->getTimetoken() . PHP_EOL;

    // Calculate and display the Unix timestamp (seconds)
    $unixTimestamp = floor($result->getTimetoken() / 10000000);
    echo "Unix Timestamp (seconds): " . $unixTimestamp . PHP_EOL;

    // Convert to a readable date/time format
    $readableDate = date('Y-m-d H:i:s', $unixTimestamp);
    echo "Human Readable Date: " . $readableDate . PHP_EOL;

    // Example: Using timetoken for synchronization
    echo PHP_EOL . "Example usage:" . PHP_EOL;
    echo "- Use this timetoken as a reference point for synchronizing events" . PHP_EOL;
    echo "- Create a channel history request starting from this time" . PHP_EOL;
} catch (PubNubServerException $exception) {
    // Handle PubNub-specific errors
    echo "Error fetching timetoken: " . $exception->getMessage() . PHP_EOL;

    if (method_exists($exception, 'getServerErrorMessage') && $exception->getServerErrorMessage()) {
        echo "Server Error: " . $exception->getServerErrorMessage() . PHP_EOL;
    }

    if (method_exists($exception, 'getStatusCode') && $exception->getStatusCode()) {
        echo "Status Code: " . $exception->getStatusCode() . PHP_EOL;
    }
} catch (Exception $exception) {
    // Handle general exceptions
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
