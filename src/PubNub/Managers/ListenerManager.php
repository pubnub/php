<?php

namespace PubNub\Managers;


use PubNub\PubNub;

class ListenerManager
{
    /** @var  PubNub */
    protected $pubnub;

    /** @var array  */
    protected $listeners = [];

    /**
     * ListenerManager constructor.
     * @param PubNub $pubnub
     */
    public function __construct(PubNub $pubnub)
    {
        $this->pubnub = $pubnub;
    }

    public function addListener($listener)
    {
        $this->listeners[] = $listener;
    }

    public function removeListener($listener)
    {
        foreach ($this->listeners as $key => $val) {
            if ($val === $listener) {
                unset($this->listeners[$key]);
            }
        }
    }
}