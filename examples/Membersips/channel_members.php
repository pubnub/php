<?php

declare(strict_types=1);

set_time_limit(0);

require('../../vendor/autoload.php');

use PubNub\PubNub;
use PubNub\PNConfiguration;
use PubNub\Models\Consumer\Objects\Member\PNMemberIncludes;
use PubNub\Models\Consumer\Objects\Member\PNChannelMember;

$pnConfig = new PNConfiguration();
$pnConfig->setPublishKey('demo');
$pnConfig->setSubscribeKey('demo');
$pnConfig->setUserId('demo');

$pubnub = new PubNub($pnConfig);

$includes = new PNMemberIncludes();
$includes->user()->userId()->userCustom()->userType()->userStatus()->custom()->status()->type();

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

$set_response = $pubnub->setMembers()->include($includes)->channel("ch")->members($channel_members)->sync();

var_dump($set_response);
