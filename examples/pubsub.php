<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Enums\PNStatusCategory;
use PubNub\PNConfiguration;
use PubNub\PubNub;

$config = new PNConfiguration();
$config->setPublishKey(getenv('PN_KEY_PUBLISH'))
    ->setSubscribeKey(getenv('PN_KEY_SUBSCRIBE'))
    ->setUserId('tests');

$pubnub = new PubNub($config);

$pubnub->publish()->channel('test')->spaceId('testSpace')->message('message')->sync();

$subscribeCallback = new class extends SubscribeCallback {
    public function status($pubnub, $status)
    {
        var_dump($status);
        $this->callback($pubnub, $status);
    }

    public function message($pubnub, $message)
    {
        var_dump($message);
        $this->callback($pubnub, $message);
    }

    public function presence($pubnub, $presence)
    {
        var_dump($presence);
        $this->callback($pubnub, $presence);
    }

    // Not marked as abstract for backward compatibility reasons.
    public function signal($pubnub, $signal)
    {
        var_dump($signal);
        $this->callback($pubnub, $signal);
    }

    private function callback(PubNub $pubnub, $payload)
    {
        // var_dump($payload);
        // $pubnub->removeListener($this);
    }
};

$pubnub->addListener($subscribeCallback);
$subscribe = $pubnub->subscribe()->channels('test')->execute();

echo("done.\n");
