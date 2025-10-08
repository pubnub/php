<?php

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
$pnConfiguration->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?? 'demo');

// Set publish key (only required if publishing)
$pnConfiguration->setPublishKey(getenv('PUBLISH_KEY') ?? 'demo');

// Set UUID (required to connect)
$pnConfiguration->setUserId('php-config-demo-user');
// snippet.end

// snippet.basic_configuration
// Create a new configuration instance
$pnConfiguration = new PNConfiguration();

// Set subscribe key (required)
$pnConfiguration->setSubscribeKey("demo");

// Set publish key (only required if publishing)
$pnConfiguration->setPublishKey("demo");

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

// snippet.init_basic
$pnconf = new PNConfiguration();

$pnconf->setSubscribeKey("my-key");
$pnconf->setPublishKey("my-key");
$pnconf->setSecure(false);
$pnconf->setUserId("myUniqueUserId");
$pubnub = new PubNub($pnconf);
// snippet.end

// snippet.init_access_manager
$pnConfiguration = new PNConfiguration();

$pnConfiguration->setSubscribeKey("my_sub_key");
$pnConfiguration->setPublishKey("my_pub_key");
$pnConfiguration->setSecretKey("my_secret_key");
$pnConfiguration->setUserId("myUniqueUserId");
$pubnub = new PubNub($pnConfiguration);
// snippet.end

// snippet.event_listeners
class MySubscribeCallback extends SubscribeCallback {
    function status($pubnub, $status) {
        if ($status->getCategory() === PNStatusCategory::PNUnexpectedDisconnectCategory) {
        // This event happens when radio / connectivity is lost
        } else if ($status->getCategory() === PNStatusCategory::PNConnectedCategory){
        // Connect event. You can do stuff like publish, and know you'll get it // Or just use the connected event to confirm you are subscribed for // UI / internal notifications, etc
        } else if ($status->getCategory() === PNStatusCategory::PNDecryptionErrorCategory){
        // Handle message decryption error. Probably client configured to // encrypt messages and on live data feed it received plain text.
        }
    }

    function message($pubnub, $message){
    // Handle new message stored in message.message
    }
    function presence($pubnub, $presence){
    // handle incoming presence data
    }
}

$pnconf = new PNConfiguration();
$pubnub = new PubNub($pnconf);

$pnconf->setSubscribeKey("my_sub_key");
$pnconf->setPublishKey("my_pub_key");

$subscribeCallback = new MySubscribeCallback();

$pubnub->addListener($subscribeCallback);

// Subscribe to a channel, this is not async.
$pubnub->subscribe()
->channels("hello_world")
->execute();

// Use the publish command separately from the Subscribe code shown above.
// Subscribe is not async and will block the execution until complete.
$result = $pubnub->publish()
->channel("hello_world")
->message("Hello PubNub")
->sync();

print_r($result);
// snippet.end

// snippet.set_filter_expression
$pnconf = new PNConfiguration();

$pnconf->setSubscribeKey("my_sub_key");
$pnconf->setFilterExpression("userid == 'my_userid'");

$pubnub = new PubNub($pnconf);
// snippet.end

