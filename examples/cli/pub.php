<?php

declare(strict_types=1);

set_time_limit(0);

require('../../vendor/autoload.php');


use PubNub\PubNub;
use PubNub\PNConfiguration;

if ($argc < 5) {
    echo "Usage: php pub.php <uuid> <channel> <message> <custom_message_type>\n";
    exit(1);
}

$pnUuid = $argv[1] . '-pn-610da4553bb079.92567429';

$pnConfig = new PNConfiguration();
$pnConfig->setPublishKey(getenv('PN_KEY_PUBLISH'));
$pnConfig->setSubscribeKey(getenv('PN_KEY_SUBSCRIBE'));
$pnConfig->setUuid($pnUuid);

$pubnub = new PubNub($pnConfig);

$pubResult = $pubnub->publish()
    ->channel($argv[2])
    ->message($argv[3])
    ->customMessageType($argv[4])
    ->sync();

printf("Published message at timetoken: %d\n", $pubResult->getTimetoken());
