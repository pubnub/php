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
$pnConfig->setUserId("php-presence-demo-user");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);

try {
    // Get presence information for specified channels
    $result = $pubnub->hereNow()
        ->channels(["my_channel", "demo"])
        ->includeUuids(true)  // Include the UUIDs of connected clients
        ->includeState(false) // Don't include state information
        ->sync();

    // Display total counts
    echo "Total channels with presence: " . $result->getTotalChannels() . PHP_EOL;
    echo "Total users online: " . $result->getTotalOccupancy() . PHP_EOL;

    // Iterate through each channel
    foreach ($result->getChannels() as $channelData) {
        echo PHP_EOL . "Channel: " . $channelData->getChannelName() . PHP_EOL;
        echo "Occupancy: " . $channelData->getOccupancy() . " users" . PHP_EOL;

        // List all users in the channel
        if ($channelData->getOccupancy() > 0) {
            echo "Users present:" . PHP_EOL;

            foreach ($channelData->getOccupants() as $index => $occupant) {
                echo ($index + 1) . ". UUID: " . $occupant->getUuid() . PHP_EOL;

                // Display state if available and requested
                if ($occupant->getState()) {
                    echo "   State: " . json_encode($occupant->getState()) . PHP_EOL;
                }
            }
        } else {
            echo "No users currently in this channel" . PHP_EOL;
        }
    }

    // Example: How to use the result in your application
    echo PHP_EOL . "Example usage:" . PHP_EOL;
    echo "- Track how many users are in each channel" . PHP_EOL;
    echo "- Display a list of active users" . PHP_EOL;
    echo "- Check if a specific user is online" . PHP_EOL;
} catch (PubNubServerException $exception) {
    // Handle PubNub-specific errors
    echo "Error getting presence information: " . $exception->getMessage() . PHP_EOL;

    if (method_exists($exception, 'getServerErrorMessage') && $exception->getServerErrorMessage()) {
        echo "Server Error: " . $exception->getServerErrorMessage() . PHP_EOL;
    }

    if (method_exists($exception, 'getStatusCode') && $exception->getStatusCode()) {
        echo "Status Code: " . $exception->getStatusCode() . PHP_EOL;
    }
} catch (PubNubException $exception) {
    // Handle general PubNub exceptions
    echo "PubNub Error: " . $exception->getMessage() . PHP_EOL;
} catch (Exception $exception) {
    // Handle other exceptions
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
