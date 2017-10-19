<?php

require_once __DIR__ . '/../src/autoloader.php';
require_once __DIR__ . '/../vendor/autoload.php';


$pnconfig = new \PubNub\PNConfiguration();

$pnconfig->setSecretKey("fake");
$pnconfig->setPublishKey("fake");
$pnconfig->setSubscribeKey("fake");

$pubnub = new \PubNub\PubNub($pnconfig);

$channel_comma_list = ['ch1', 'ch2'];
$auth_key = "blah";
$read = true;
$write = false;
$ttl_mins = 15;

try {
    $result = $pubnub->grant()
        ->channels($channel_comma_list)
        ->authKeys($auth_key)
        ->read($read)
        ->write($write)
        ->ttl($ttl_mins)->sync();
} catch (\PubNub\Exceptions\PubNubServerException $exception) {
    print_r("Message: " . $exception->getMessage() . "\n");
    print_r("Status: " . $exception->getStatusCode() . "\n");
    echo "Original message: ";
    print_r($exception->getBody());
} catch (\PubNub\Exceptions\PubNubException $exception) {
    print_r("Message: " . $exception->getMessage());
}

