<?php

declare(strict_types=1);

set_time_limit(0);

require('vendor/autoload.php');

use PubNub\PubNub;
use PubNub\PNConfiguration;
use PubNub\Models\Consumer\Objects\Member\PNMemberIncludes;
use PubNub\Models\Consumer\Objects\Member\PNChannelMember;

$config = new PNConfiguration();
$config->setPublishKey(getenv('PUBLISH_KEY') ?? 'demo');
$config->setSubscribeKey(getenv('SUBSCRIBE_KEY') ?? 'demo');
$config->setUserId('demo');

$pubnub = new PubNub($config);

$includes = new PNMemberIncludes();
$includes->user()
    ->userCustom()
    ->userType()
    ->userStatus()
    ->custom()
    ->status()
    ->type();

$channel_members = [
    (new PNChannelMember('uuid1'))
        ->setCustom(["a" => "b"])
        ->setStatus("status")
        ->setType("type"),
    (new PNChannelMember('uuid1'))
        ->setCustom(["c" => "d"])
        ->setStatus("status")
        ->setType("type")
];

$set_response = $pubnub->setMembers()
    ->include($includes)
    ->channel("ch")
    ->members($channel_members)
    ->sync();

var_dump($set_response);
