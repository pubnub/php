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

$pnConfig->setUuid($pnUuid);

$pubnub = new PubNub($pnConfig);

$ts = $pubnub->time()->sync();

$channelName = $argv[1];
$history = $pubnub->fetchMessages()
    ->channels($channelName)
    ->end((int)($ts->getTimetoken() - 3000000000))
    ->includeCustomMessageType(true)
    ->count(50)
    ->sync();

$messages = $history->getChannels()[$channelName];
print("Received " . count($messages) . " messages\n");

foreach ($messages as $key => $message) {
    printf("Message %d:\n", $key + 1);
    printf(
        "\n %s\n   Timetoken: %s\n   Custom message type: %s\n",
        $message->getMessage(),
        $message->getTimetoken(),
        $message->getCustomMessageType(),
    );
}
