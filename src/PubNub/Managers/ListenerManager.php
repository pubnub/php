<?php

namespace PubNub\Managers;


use PubNub\Callbacks\SubscribeCallback;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;

class ListenerManager
{
    /** @var  PubNub */
    protected $pubnub;

    /** @var SubscribeCallback[]  */
    protected $listeners = [];

    /**
     * ListenerManager constructor.
     * @param PubNub $pubnub
     */
    public function __construct(PubNub $pubnub)
    {
        $this->pubnub = $pubnub;
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function addListener($listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function removeListener($listener)
    {
        foreach ($this->listeners as $key => $val) {
            if ($val === $listener) {
                unset($this->listeners[$key]);
            }
        }
    }

    /**
     * @param PNStatus $status
     */
    public function announceStatus($status)
    {
        foreach ($this->listeners as $listener) {
            $listener->status($this->pubnub, $status);
        }
    }

    /**
     * // TODO: add PNMessageResult type
     * @param $message
     */
    public function announceMessage($message)
    {
        foreach ($this->listeners as $listener) {
            $listener->message($this->pubnub, $message);
        }
    }

    /**
     * // TODO: add PNPresenceEventResult.php type
     * @param $presence
     */
    public function announcePresence($presence)
    {
        foreach ($this->listeners as $listener) {
            $listener->presence($this->pubnub, $presence);
        }
    }
}