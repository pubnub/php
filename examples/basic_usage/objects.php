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
$pnConfig->setUserId("php-app-context-demo");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);

try {
    // Fetch metadata for all users with custom fields and total count
    $response = $pubnub->getAllUUIDMetadata()
        ->includeFields([
            "totalCount" => true,
            "customFields" => true
        ])
        ->limit(10)  // Limit results to 10 users per page
        ->sync();

    // Display total count if available
    if ($response->getTotalCount() !== null) {
        echo "Total users: " . $response->getTotalCount() . PHP_EOL;
    }

    // Display user data
    echo "Retrieved " . count($response->getData()) . " users:" . PHP_EOL;

    foreach ($response->getData() as $index => $userData) {
        echo PHP_EOL . ($index + 1) . ". User ID: " . $userData->getId() . PHP_EOL;

        if ($userData->getName()) {
            echo "   Name: " . $userData->getName() . PHP_EOL;
        }

        if ($userData->getEmail()) {
            echo "   Email: " . $userData->getEmail() . PHP_EOL;
        }

        // Display custom data if available
        if ($userData->getCustom()) {
            echo "   Custom data:" . PHP_EOL;
            foreach (get_object_vars($userData->getCustom()) as $key => $value) {
                echo "     - $key: " . (is_scalar($value) ? $value : json_encode($value)) . PHP_EOL;
            }
        }
    }

    // Show pagination information if available
    if ($response->getNext()) {
        echo PHP_EOL . "For next page, use:" . PHP_EOL;
        echo '$pubnub->getAllUUIDMetadata()' . PHP_EOL;
        echo '    ->page(["next" => "' . $response->getNext() . '"])' . PHP_EOL;
        echo '    ->sync();' . PHP_EOL;
    }

    if ($response->getPrev()) {
        echo PHP_EOL . "For previous page, use:" . PHP_EOL;
        echo '$pubnub->getAllUUIDMetadata()' . PHP_EOL;
        echo '    ->page(["prev" => "' . $response->getPrev() . '"])' . PHP_EOL;
        echo '    ->sync();' . PHP_EOL;
    }
} catch (PubNubServerException $exception) {
    // Handle PubNub-specific errors
    echo "Error fetching user metadata: " . $exception->getMessage() . PHP_EOL;

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
