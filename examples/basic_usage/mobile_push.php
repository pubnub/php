<?php

// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubServerException;

// Create configuration with demo keys
$pnConfig = new PNConfiguration();
$pnConfig->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");
$pnConfig->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");
$pnConfig->setUserId("php-push-demo-user");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);

// Define channels to enable push notifications on
$channelsForPush = ["news", "alerts", "promotions"];

// FCM/GCM device token (typically a long string generated by Firebase)
$deviceId = "fcm-device-registration-token-from-firebase";

try {
    // Register the device to receive push notifications on the specified channels
    $result = $pubnub->addChannelsToPush()
        ->pushType(PNPushType::FCM)  // Use GCM/FCM for Android devices
        ->channels($channelsForPush)
        ->deviceId($deviceId)
        ->sync();

    echo "Device successfully registered for push notifications!" . PHP_EOL;
    echo "Device ID: " . $deviceId . PHP_EOL;
    echo "Channels: " . implode(", ", $channelsForPush) . PHP_EOL;

    // Example: Verify channels were added by listing current push registrations
    echo PHP_EOL . "To verify which channels this device is registered for, use:" . PHP_EOL;
    echo '$result = $pubnub->listPushProvisions()' . PHP_EOL;
    echo '    ->pushType(PNPushType::FCM)' . PHP_EOL;
    echo '    ->deviceId("' . $deviceId . '")' . PHP_EOL;
    echo '    ->sync();' . PHP_EOL;
    echo 'print_r($result->getChannels());' . PHP_EOL;
} catch (PubNubServerException $exception) {
    // Handle PubNub-specific errors
    echo "Error registering device for push: " . $exception->getMessage() . PHP_EOL;

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
