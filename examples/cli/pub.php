<?php

declare(strict_types=1);

set_time_limit(0);

require('../../vendor/autoload.php');


use PubNub\PubNub;
use PubNub\PNConfiguration;
use PubNub\CryptoModule;

$pnUuid = $argv[1] . '-pn-610da4553bb079.92567429';

$pnConfig = new PNConfiguration();
$pnConfig->setPublishKey(getenv('PN_KEY_PUBLISH'));
$pnConfig->setSubscribeKey(getenv('PN_KEY_SUBSCRIBE'));
$pnConfig->setUuid($pnUuid);
if (array_key_exists(4, $argv)) {
    $pnConfig->setCrypto(CryptoModule::aesCbcCryptor($argv[4], true));
}

$pubnub = new PubNub($pnConfig);

$pubResult = $pubnub->publish()->channel($argv[2])->message($argv[3])->sync();
