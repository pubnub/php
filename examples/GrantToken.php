<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\Models\Access\Permissions;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerTokenResult;


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
    $pubnub->parseToken($token)->toArray()
); die;
