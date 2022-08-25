<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\Models\Consumer\PNTimeResult;

$pnconfig = \PubNub\PNConfiguration::demoKeys();
$pubnub = new \PubNub\PubNub($pnconfig);

$result = $pubnub->time()->sync();

printf("Server Time is: %s", date("Y-m-d H:i:s", $result->getTimetoken()));
