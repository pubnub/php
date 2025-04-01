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
$pnConfig->setUserId("fetch-messages-demo-user");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);

try {
    // Fetch the last message from a channel with all additional data
    $result = $pubnub->fetchMessages()
        ->channels("my_channel")                  // Channel to fetch from
        ->includeMessageActions(true)             // Include reactions to messages
        ->includeMeta(true)                       // Include metadata
        ->includeMessageType(true)                // Include message type
        ->includeCustomMessageType(true)          // Include custom message type
        ->includeUuid(true)                       // Include sender UUID
        ->sync();                                 // Execute synchronously

    // Process and display the results
    if (!empty($result->getChannels())) {
        echo "Successfully fetched message history" . PHP_EOL;

        // Display timetoken range
        echo "Start timetoken: " . $result->getStartTimetoken() . PHP_EOL;
        echo "End timetoken: " . $result->getEndTimetoken() . PHP_EOL . PHP_EOL;

        // Process each channel in the result
        foreach ($result->getChannels() as $channelName => $messages) {
            echo "Channel: " . $channelName . PHP_EOL;
            echo "Number of messages: " . count($messages) . PHP_EOL;
            echo "----------------------------" . PHP_EOL;

            // Process each message in the channel
            foreach ($messages as $index => $item) {
                echo "Message #" . ($index + 1) . ":" . PHP_EOL;

                // Convert timetoken to readable date
                $timestamp = floor($item->getTimetoken() / 10000000);
                $readableDate = date('Y-m-d H:i:s', $timestamp);
                echo "Date: " . $readableDate . PHP_EOL;

                // Display message content
                echo "Content: " . json_encode($item->getMessage(), JSON_PRETTY_PRINT) . PHP_EOL;

                // Display sender UUID if available
                if ($item->getUuid()) {
                    echo "Sender: " . $item->getUuid() . PHP_EOL;
                }

                // Display metadata if available
                if ($item->getMetadata()) {
                    echo "Metadata: " . json_encode($item->getMetadata(), JSON_PRETTY_PRINT) . PHP_EOL;
                }

                // Display message type information if available
                if ($item->getMessageType()) {
                    echo "Message Type: " . $item->getMessageType() . PHP_EOL;
                }
                if ($item->getCustomMessageType()) {
                    echo "Custom Message Type: " . $item->getCustomMessageType() . PHP_EOL;
                }

                // Display message actions (reactions) if available
                if ($item->getActions() && count($item->getActions()) > 0) {
                    echo "Message Actions:" . PHP_EOL;
                    foreach ($item->getActions() as $actionType => $actions) {
                        echo "  " . $actionType . ":" . PHP_EOL;
                        foreach ($actions as $actionValue => $users) {
                            echo "    " . $actionValue . ": " . count($users) . " reactions" . PHP_EOL;
                        }
                    }
                }

                echo "----------------------------" . PHP_EOL;
            }
        }
    } else {
        echo "No messages found in the channel." . PHP_EOL;
    }
} catch (PubNubServerException $exception) {
    // Handle PubNub server-specific errors
    echo "Error fetching messages: " . $exception->getMessage() . PHP_EOL;

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
