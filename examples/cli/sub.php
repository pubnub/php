<?php

declare(strict_types=1);

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);

set_time_limit(0);

require('../../vendor/autoload.php');

use PubNub\Callbacks\SubscribeCallback;
use PubNub\Exceptions\PubNubException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\Consumer\PubSub\PNPresenceEventResult;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;
use PubNub\PNConfiguration;
use PubNub\CryptoModule;


$pnUuid = 'pn-610da4553bb079.92567429';

$pnConfig = new PNConfiguration();
$pnConfig->setPublishKey(getenv('PN_KEY_PUBLISH'));
$pnConfig->setSubscribeKey(getenv('PN_KEY_SUBSCRIBE'));
if (array_key_exists(2, $argv)) {
    $pnConfig->setCrypto(CryptoModule::aesCbcCryptor($argv[2], true));
}
$pnConfig->setUuid($pnUuid);

$pubnub = new PubNub($pnConfig);

$channelName = $argv[1];

// phpcs:ignore
class MySubscribeCallback extends SubscribeCallback
{
    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
     * @return void
     * @throws PubNubException
     */
    public function status($pubnub, $status)
    {
        printf(
            "Category: %s\nPublisher user_id: %s",
            $status->getCategory(),
            $status->getUuid()
        );
    }

    /**
     * @param PubNub $pubnub
     * @param PNMessageResult $messageResult
     * @return void
     */
    public function message($pubnub, $messageResult)
    {
        printf(
            "\nMessage %s\n Channel: %s\n Timetoken: %s\n Publisher: %s\n",
            $messageResult->getMessage(),
            $messageResult->getChannel(),
            $messageResult->getTimetoken(),
            $messageResult->getPublisher(),
        );
        if ($messageResult->isError()) {
            printf('\nError occured during parsing the message: %s', $messageResult->getError()->getMessage());
        }
    }

    /**
     * @param PubNub $pubnub
     * @param PNPresenceEventResult $presence
     * @return void
     */
    public function presence($pubnub, $presence)
    {
        print("{$presence->getEvent()}: {$presence->getUuid()}");
    }

    /**
     * @param PubNub $pubnub
     * @param PNSignalMessageResult $signal
     * @return void
     */
    public function signal($pubnub, $signal)
    {
    }
}

$subscribeCallback = new MySubscribeCallback();

$pubnub->addListener($subscribeCallback);

echo "subscribing to: $channelName\n";

$subResult = $pubnub->subscribe()
    ->channels($channelName)
    ->withTimetoken(true)
    ->withPresence(true)
    ->execute();

echo "done.\n";
