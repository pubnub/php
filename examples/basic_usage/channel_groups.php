<?php

// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubServerException;

// snippet.setup
// Create configuration
$pnConfig = new PNConfiguration();
$pnConfig->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");
$pnConfig->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");
$pnConfig->setUserId("php-channel-group-demo");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);
// snippet.end

try {
    // Add channels to channel group
    $result = $pubnub->addChannelToChannelGroup()
        ->channels(["news", "sports"])
        ->channelGroup("my-group")
        ->sync();

    // Print success message
    echo "Channels added to group successfully!" . PHP_EOL;

    // Example of how to use this channel group for subscription
    echo PHP_EOL . "To subscribe to this channel group:" . PHP_EOL;
    echo '$pubnub->subscribe()->channelGroups(["my-group"])->execute();' . PHP_EOL;
} catch (PubNubServerException $exception) {
    // Handle errors
    echo "Error adding channels to group: " . $exception->getMessage() . PHP_EOL;

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
