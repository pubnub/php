<?php

require_once __DIR__ . '/../src/autoloader.php';


$pnconfig = \PubNub\PNConfiguration::demoKeys();
$pubnub = new \PubNub\PubNub($pnconfig);

$result = $pubnub->time()->sync();