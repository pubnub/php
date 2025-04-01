<?php

// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Models\Consumer\MessageActions\PNMessageAction;
use PubNub\Exceptions\PubNubServerException;

// Create configuration
$pnConfig = new PNConfiguration();
$pnConfig->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");
$pnConfig->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");
$pnConfig->setUserId("php-message-action-demo");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);

try {
    // First publish a message to react to
    $channelName = "pizza_talks";
    $publishResult = $pubnub->publish()
        ->channel($channelName)
        ->message(["text" => "Chicago deep dish is the best pizza!"])
        ->sync();

    // Get the timetoken of the published message
    $messageTimetoken = $publishResult->getTimetoken();
    echo "Message published with timetoken: " . $messageTimetoken . PHP_EOL;

    // Create a message action (reaction) for the published message
    $messageAction = new PNMessageAction([
        "type" => "reaction",
        "value" => "drooling_face",
        "messageTimetoken" => $messageTimetoken
    ]);

    // Add the message action
    $result = $pubnub->addMessageAction()
        ->channel($channelName)
        ->messageAction($messageAction)
        ->sync();

    // Print the result
    echo "Message action added successfully!" . PHP_EOL;
    echo "Type: " . $result->type . PHP_EOL;
    echo "Value: " . $result->value . PHP_EOL;
    echo "UUID: " . $result->uuid . PHP_EOL;
    echo "Action Timetoken: " . $result->actionTimetoken . PHP_EOL;
    echo "Message Timetoken: " . $result->messageTimetoken . PHP_EOL;

    // Example of how to remove this action
    echo PHP_EOL . "To remove this action, use:" . PHP_EOL;
    echo '$pubnub->removeMessageAction()' . PHP_EOL;
    echo '    ->channel("' . $channelName . '")' . PHP_EOL;
    echo '    ->messageTimetoken(' . $result->messageTimetoken . ')' . PHP_EOL;
    echo '    ->actionTimetoken(' . $result->actionTimetoken . ')' . PHP_EOL;
    echo '    ->sync();' . PHP_EOL;
} catch (PubNubServerException $exception) {
    // Handle PubNub-specific errors
    echo "Error adding message action: " . $exception->getMessage() . PHP_EOL;

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
