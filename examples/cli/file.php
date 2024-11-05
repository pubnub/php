<?php

declare(strict_types=1);

set_time_limit(0);

require('../../vendor/autoload.php');


use PubNub\PubNub;
use PubNub\PNConfiguration;

if ($argc < 3) {
    echo "Usage: php file.php <uuid> <channel> <message> <custom_message_type>\n";
    exit(1);
}

$pnUuid = $argv[1] . '-pn-610da4553bb079.92567429';

$pnConfig = new PNConfiguration();
$pnConfig->setPublishKey(getenv('PN_KEY_PUBLISH'));
$pnConfig->setSubscribeKey(getenv('PN_KEY_SUBSCRIBE'));
$pnConfig->setUuid($pnUuid);

$pubnub = new PubNub($pnConfig);
$file = fopen('pn.jpg', 'rb');
$pubResult = $pubnub->sendFile()
    ->channel($argv[2])
    ->message($argv[3])
    ->customMessageType($argv[4])
    ->fileHandle($file)
    ->fileName('pn.jpg')
    ->sync();

print("Published file");
