<?php

use PubNub\Models\Access\Permissions;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerTokenResult;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/autoloader.php';



$pnconfig = new \PubNub\PNConfiguration();
$pnconfig->setPublishKey('my-publish-key');
$pnconfig->setSubscribeKey('my-subscribe-key');
$pnconfig->setSecretKey('my-secret-key');

$pubnub = new \PubNub\PubNub($pnconfig);

try {
    $token = $pubnub->grantToken()
        ->ttl(30)
        ->authorizedUuid('my-uuid')
        ->addChannelResources([
            'my-channel' => ['read' => true]
        ])
        ->sync();

        /** @var PNAccessManagerTokenResult */
        $parsedToken = $pubnub->parseToken($token);
        $parsedToken->getTtl();
        $parsedToken->getChannelResource('my-channel')
            ->hasRead();
} catch (\PubNub\Exceptions\PubNubServerException $e) {
    var_dump($e->getBody());
}

var_dump(
    $pubnub->parseToken(
        'qEF2AkF0GmFt5QxDdHRsGB5DcmVzpURjaGFuoWpteS1jaGFubmVsAUNncnCgQ3VzcqBDc3BjoER1dWlkoENwYXSlRGNoYW6gQ2dycKBDdXNyoE'
        . 'NzcGOgRHV1aWSgRG1ldGGgRHV1aWRnbXktdXVpZENzaWdYICAa27C5EVKsWZDdGT8PP21discwdT8v7yfwmsp0VJ_E'
    )->toArray()
); die;
