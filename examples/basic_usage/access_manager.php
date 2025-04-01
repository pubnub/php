<?php

// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubServerException;

// Create configuration
$pnConfig = new PNConfiguration();
$pnConfig->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");
$pnConfig->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");
$pnConfig->setSecretKey(getenv("SECRET_KEY") ?? "demo"); // Required for Access Manager operations
$pnConfig->setUserId("php-token-granter");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);

try {
    // Grant token with permissions to a channel
    $token = $pubnub->grantToken()
        ->ttl(15) // Time-to-live in minutes (min: 1, max: 43200)
        ->authorizedUuid('php-authorized-user')
        ->addChannelResources([
            'my-channel' => ['read' => true, 'write' => true, 'update' => true],
        ])
        ->sync();

    // Print the token
    echo "Generated token: " . $token . PHP_EOL;

    // Example of how to use the token in a client application
    echo "How to use this token in a client:" . PHP_EOL;
    echo "  1. Initialize a PubNub client without secretKey" . PHP_EOL;
    echo "  2. Set token with: \$pubnub->setToken(\"" . $token . "\");" . PHP_EOL;
    echo "  3. Use the authorized UUID: php-authorized-user" . PHP_EOL;
} catch (PubNubServerException $exception) {
    // Handle errors
    echo "Error generating token: " . $exception->getServerErrorMessage() . PHP_EOL;
    echo "Status Code: " . $exception->getStatusCode() . PHP_EOL;

    if ($exception->getServerErrorSource()) {
        echo "Error Source: " . $exception->getServerErrorSource() . PHP_EOL;
    }

    if ($exception->getServerErrorDetails()) {
        echo "Error Details: " . print_r($exception->getServerErrorDetails(), true) . PHP_EOL;
    }
}
