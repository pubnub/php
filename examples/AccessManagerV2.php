<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use PubNub\PubNub;
use PubNub\PNConfiguration;

// snippet.setup
$config = new PNConfiguration();
$config->setSecretKey(getenv('SECRET_KEY') ?: 'demo');
$config->setPublishKey(getenv('PUBLISH_KEY') ?: 'demo');
$config->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?: 'demo');
$config->setUserId('example');

$pubnub = new PubNub($config);
// snippet.end

// snippet.grant_channel_authkey
$channels = ["ch1", "ch2"];
$auth_key = "blah";
$read = true;
$write = false;
$ttl_mins = 15;
try {
    $result = $pubnub->grant()
        ->channels($channels)
        ->authKeys($auth_key)
        ->read($read)
        ->write($write)
        ->ttl($ttl_mins)
        ->sync();
    print("Grant successful\n");
} catch (\PubNub\Exceptions\PubNubServerException $exception) {
    print_r("Message: " . $exception->getMessage() . "\n");
    print_r("Status: " . $exception->getStatusCode() . "\n");
    echo "Original message: ";
    print_r($exception->getBody());
} catch (\PubNub\Exceptions\PubNubException $exception) {
    print_r("Message: " . $exception->getMessage());
}
// snippet.end

// snippet.grant_all_channels
$pubnub->grant()
    ->read(true)
    ->write(true)
    ->sync();
// snippet.end

// snippet.grant_specific_channel
$pubnub->grant()
    ->channels("my_channel")
    ->read(true)
    ->write(true)
    ->sync();
// snippet.end

// snippet.grant_channel_with_authkey
$pubnub->grant()
    ->channels("my_channel")
    ->read(true)
    ->write(false)
    ->authKeys("my_ro_authkey")
    ->ttl(5)
    ->sync();
// snippet.end

// snippet.grant_presence_channel
$pubnub->grant()
    ->channels("my_channel-pnpres")
    ->read(true)
    ->write(true)
    ->sync();
// snippet.end

// snippet.grant_channel_group
$result = $pubnub->grant()
    ->channelGroups(["cg1", "cg2", "cg3"])
    ->authKeys(["key1", "key2", "auth3"])
    ->read(true)
    ->write(true)
    ->manage(true)
    ->ttl(12237)
    ->sync();
// snippet.end

// snippet.grant_application_level
try {
    $result = $pubnub->grant()
        ->read(true)
        ->write(true)
        ->sync();

    print_r($result);
} catch (\PubNub\Exceptions\PubNubServerException $exception) {
    print_r($exception->getMessage() . "\n");
    print_r($exception->getStatusCode() . "\n");
    print_r($exception->getBody());
} catch (\PubNub\Exceptions\PubNubException $exception) {
    print_r($exception->getMessage());
}
// snippet.end

// snippet.grant_channel_level
$result = $pubnub->grant()
    ->channels("my_channel")
    ->read(true)
    ->write(true)
    ->sync();
// snippet.end

// snippet.grant_user_level
$result = $pubnub->grant()
    ->channels("my_channel")
    ->authKeys("my_authkey")
    ->read(true)
    ->write(true)
    ->ttl(5)
    ->sync();
// snippet.end
