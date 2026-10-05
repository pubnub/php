<?php

namespace PubNub\Callbacks;

use PubNub\Models\Consumer\DataSync\PNDataSyncEventResult;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;

abstract class SubscribeCallback
{
    /**
     * @param PubNub $pubnub
     * @param PNStatus $status
     */
    abstract public function status($pubnub, $status);

    // TODO: add annotation
    abstract public function message($pubnub, $message);

    // TODO: add annotation
    abstract public function presence($pubnub, $presence);

    // Not marked as abstract for backward compatibility reasons.
    public function signal($pubnub, $signal)
    {
    }

    /**
     * Not marked as abstract for backward compatibility reasons.
     *
     * @param PubNub $pubnub
     * @param PNDataSyncEventResult $event
     * @return void
     */
    public function dataSyncEvent($pubnub, $event)
    {
    }
}
