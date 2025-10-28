<?php

// @phpstan-ignore-file
// phpcs:ignoreFile
require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\CryptoModule;
use PubNub\Enums\PNStatusCategory;
use PubNub\Callbacks\SubscribeCallback;

// snippet.setup
// Create a new configuration instance
$pnConfiguration = new PNConfiguration();

// Set subscribe key (required)
$pnConfiguration->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?: 'demo');

// Set publish key (only required if publishing)
$pnConfiguration->setPublishKey(getenv('PUBLISH_KEY') ?: 'demo');

// Set UUID (required to connect)
$pnConfiguration->setUserId('php-config-demo-user');
// snippet.end

// Verify configuration was set correctly
assert($pnConfiguration->getSubscribeKey() === (getenv('SUBSCRIBE_KEY') ?: 'demo'));
assert($pnConfiguration->getPublishKey() === (getenv('PUBLISH_KEY') ?: 'demo'));
assert($pnConfiguration->getUserId() === 'php-config-demo-user');

// snippet.basic_configuration
// Create a new configuration instance
$pnConfiguration = new PNConfiguration();

// Set subscribe key (required)
$pnConfiguration->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?: 'demo');

// Set publish key (only required if publishing)
$pnConfiguration->setPublishKey(getenv('PUBLISH_KEY') ?: 'demo');

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
// snippet.end

// Verify configuration values
assert($pnConfiguration->getSubscribeKey() === (getenv('SUBSCRIBE_KEY') ?: 'demo'));
assert($pnConfiguration->getPublishKey() === (getenv('PUBLISH_KEY') ?: 'demo'));
assert($pnConfiguration->getUserId() === "php-sdk-example-user");
assert($pnConfiguration->getConnectTimeout() === 10);
assert($pnConfiguration->getSubscribeTimeout() === 310);
assert($pnConfiguration->getNonSubscribeRequestTimeout() === 10);

// Verify PubNub instance was created
assert($pubnub instanceof PubNub);

// snippet.init_basic
$pnconf = new PNConfiguration();

$pnconf->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?: 'demo');
$pnconf->setPublishKey(getenv('PUBLISH_KEY') ?: 'demo');
$pnconf->setSecure(false);
$pnconf->setUserId("myUniqueUserId");
$pubnub = new PubNub($pnconf);

// snippet.end

// Verify configuration
assert($pnconf->getSubscribeKey() === (getenv('SUBSCRIBE_KEY') ?: 'demo'));
assert($pnconf->getPublishKey() === (getenv('PUBLISH_KEY') ?: 'demo'));
assert($pnconf->getUserId() === "myUniqueUserId");
assert($pubnub instanceof PubNub);

// snippet.init_access_manager
$pnConfiguration = new PNConfiguration();

$pnConfiguration->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?: 'demo');
$pnConfiguration->setPublishKey(getenv('PUBLISH_KEY') ?: 'demo');
//NOTE: only server side should have secret key
$pnConfiguration->setSecretKey(getenv('SECRET_KEY') ?: 'demo');
$pnConfiguration->setUserId("myUniqueUserId");
$pubnub = new PubNub($pnConfiguration);
// snippet.end

// Verify configuration
assert($pnConfiguration->getSubscribeKey() === (getenv('SUBSCRIBE_KEY') ?: 'demo'));
assert($pnConfiguration->getPublishKey() === (getenv('PUBLISH_KEY') ?: 'demo'));
assert($pnConfiguration->getSecretKey() === (getenv('SECRET_KEY') ?: 'demo'));
assert($pnConfiguration->getUserId() === "myUniqueUserId");
assert($pubnub instanceof PubNub);

// snippet.event_listeners
class MySubscribeCallback extends SubscribeCallback
{
    function status($pubnub, $status)
    {
        if ($status->getCategory() === PNStatusCategory::PNUnexpectedDisconnectCategory) {
        // This event happens when connectivity is lost
        } elseif ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
        // Connect event. You can do stuff like publish, and know you'll get it
        } elseif ($status->getCategory() === PNStatusCategory::PNDecryptionErrorCategory) {
        // Handle message decryption error.
        }
    }

    function message($pubnub, $message)
    {
    // Handle new message stored in message.message
    }
    function presence($pubnub, $presence)
    {
    // handle incoming presence data
    }
}

$pnconf = new PNConfiguration();

$pnconf->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?: 'demo');
$pnconf->setPublishKey(getenv('PUBLISH_KEY') ?: 'demo');
$pnconf->setUserId("event-listener-demo-user");

$pubnub = new PubNub($pnconf);

$subscribeCallback = new MySubscribeCallback();

$pubnub->addListener($subscribeCallback);

// Subscribe to a channel, this is not async.
// Note: This would block
// $pubnub->subscribe()
// ->channels("hello_world")
// ->execute();

// Use the publish command separately from the Subscribe code shown above.
// Subscribe is not async and will block the execution until complete.
// Note: Commented out for testing to avoid network calls
// $result = $pubnub->publish()
// ->channel("hello_world")
// ->message("Hello PubNub")
// ->sync();
//
// // Verify publish result
// assert($result->getTimetoken() > 0);
//
// print_r($result);
// snippet.end

// snippet.set_filter_expression
$pnconf = new PNConfiguration();

$pnconf->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?: 'demo');
$pnconf->setUserId("filter-demo-user");
$pnconf->setFilterExpression("userid == 'my_userid'");

$pubnub = new PubNub($pnconf);
// snippet.end

// Verify configuration
assert($pnconf->getSubscribeKey() === (getenv('SUBSCRIBE_KEY') ?: 'demo'));
assert($pnconf->getUserId() === "filter-demo-user");
assert($pnconf->getFilterExpression() === "userid == 'my_userid'");
assert($pubnub instanceof PubNub);
