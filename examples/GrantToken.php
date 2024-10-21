<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PubNub\Models\Consumer\AccessManager\PNAccessManagerTokenResult;

$pnconfig = new \PubNub\PNConfiguration();
$pnconfig->setPublishKey(getenv("PUBLISH_PAM_KEY"));
$pnconfig->setSubscribeKey(getenv("SUBSCRIBE_PAM_KEY"));
$pnconfig->setSecretKey(getenv("SECRET_PAM_KEY"));
$pnconfig->setUuid('example-uuid');

$pubnub = new \PubNub\PubNub($pnconfig);

try {
    $token = $pubnub->grantToken()
        ->ttl(30)
        ->authorizedUuid('example-uuid')
        ->addChannelResources([
            'my-channel' => ['read' => true]
        ])
        ->sync();

    print("Token granted: $token\n");

    /** @var PNAccessManagerTokenResult */
    $parsedToken = $pubnub->parseToken($token);

    $tokensTTL = $parsedToken->getTtl();
    $tokensMyChannelRead = $parsedToken->getChannelResource('my-channel')->hasRead();
    $tokensMyChannelWrite = $parsedToken->getChannelResource('my-channel')->hasWrite();

    print("Token TTL: $tokensTTL\n");
    print("Token My Channel Read: " . (int)$tokensMyChannelRead . "\n");
    print("Token My Channel Write: " . (int)$tokensMyChannelWrite . "\n");
} catch (\PubNub\Exceptions\PubNubServerException $e) {
    var_dump($e->getBody());
}
