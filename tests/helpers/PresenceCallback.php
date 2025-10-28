<?php

namespace PubNubTests\helpers;

use PubNub\PubNub;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\Consumer\PubSub\PNPresenceEventResult;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubUnsubscribeException;

/**
 * Simple callback that maintains presence for a specified duration
 */
class PresenceCallback extends SubscribeCallback
{
    private int $startTime;
    private int $duration;

    public function __construct(int $duration)
    {
        $this->startTime = time();
        $this->duration = $duration;
    }

    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
     * @return void
     */
    public function status($pubnub, $status): void
    {
        // Exit if connected and duration exceeded
        if ($status->getCategory() === PNStatusCategory::PNConnectedCategory) {
            fwrite(STDOUT, "Connected: {$pubnub->getConfiguration()->getUuid()}\n");
            flush();
        }

        // Check if we should exit
        if (time() - $this->startTime > $this->duration) {
            throw new PubNubUnsubscribeException();
        }
    }

    /**
     * @param PubNub $pubnub
     * @param PNMessageResult $message
     * @return void
     */
    public function message($pubnub, $message): void
    {
        // Check if we should exit
        if (time() - $this->startTime > $this->duration) {
            throw new PubNubUnsubscribeException();
        }
    }

    /**
     * @param PubNub $pubnub
     * @param PNPresenceEventResult $presence
     * @return void
     */
    public function presence($pubnub, $presence): void
    {
        // Do nothing
    }
}
