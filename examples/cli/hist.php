<?php

declare(strict_types=1);

set_time_limit(0);

require('../../vendor/autoload.php');

use PubNub\PubNub;
use PubNub\PNConfiguration;
use PubNub\CryptoModule;

$pnUuid = 'pn-610da4553bb079.92567429'; //uniqid('pn-', true);

$pnConfig = new PNConfiguration();
$pnConfig->setPublishKey(getenv('PN_KEY_PUBLISH'));
$pnConfig->setSubscribeKey(getenv('PN_KEY_SUBSCRIBE'));
// $pnConfig->setSecretKey('sec-c-YjZiZWQzZWItMmZlNS00NjBlLTkyNTUtOGFhZjZiY2E1ZDc1');
$pnConfig->setUuid($pnUuid);
if (array_key_exists(2, $argv)) {
    $pnConfig->setCrypto(CryptoModule::aesCbcCryptor($argv[2], true));
}

$pubnub = new PubNub($pnConfig);

$ts = $pubnub->time()->sync();

$channelName = $argv[1];
$history = $pubnub->history()
    ->channel($channelName)
    ->reverse(false)
    ->end((int)($ts->getTimetoken() - 3000000000))
    ->count(50)
    ->sync();

foreach ($history->getMessages() as $key => $message) {
    print($message);
}
