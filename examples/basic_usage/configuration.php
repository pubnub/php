<?php

// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\CryptoModule;

// Create a new configuration instance
$pnConfiguration = new PNConfiguration();

// Set subscribe key (required)
$pnConfig->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");

// Set publish key (only required if publishing)
$pnConfig->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");

// Set UUID (required to connect)
$pnConfiguration->setUserId("php-sdk-example-user");

// Set up cryptography for message encryption (optional)
// Uncomment the line below to enable encryption
// $pnConfiguration->setCryptoModule(CryptoModule::aesCbcCryptor("your-cipher-key", true));

// Set authentication key (optional, required only when using Access Manager)
// $pnConfiguration->setAuthKey("my_auth_key");

// Configure connection timeout in seconds
$pnConfiguration->setConnectTimeout(10);

// Configure subscribe request timeout in seconds
$pnConfiguration->setSubscribeTimeout(310);

// Configure non-subscribe request timeout in seconds
$pnConfiguration->setNonSubscribeRequestTimeout(10);

// Set filter expression (optional)
// $pnConfiguration->setFilterExpression("channel == 'my-channel'");

// Create PubNub instance with the configured settings
$pubnub = new PubNub($pnConfiguration);

// Display configuration information
echo "PubNub Configuration:\n";
echo "Subscribe Key: " . $pnConfiguration->getSubscribeKey() . "\n";
echo "Publish Key: " . $pnConfiguration->getPublishKey() . "\n";
echo "User ID: " . $pnConfiguration->getUserId() . "\n";
echo "Encryption: " . ($pnConfiguration->getCryptoSafe() ? "enabled" : "disabled") . "\n";

// Now you can use this PubNub instance to publish and subscribe

// Example: Create a simple message
$message = ["text" => "Hello from PHP SDK!"];

// Example: Publish the message (uncomment to execute)
/*
$pubnub->publish()
    ->channel("demo-channel")
    ->message($message)
    ->sync();

echo "Message published to 'demo-channel'\n";
*/

// Keep this code running only if you plan to subscribe to messages
// Otherwise, the script will exit after publishing
