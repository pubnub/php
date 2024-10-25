<?php

namespace PubNubTests\Acceptance\Subscribe;

use PubNub\Callbacks\SubscribeCallback;
use PubNubTests\Acceptance\Subscribe\SubscribeContext;

class AcceptanceTestSubscribeCallback extends SubscribeCallback
{
    private SubscribeContext $context;

    public function __construct(SubscribeContext $context)
    {
        $this->context = $context;
    }

    /** @phpstan-ignore-next-line */
    public function status($pubnub, $status)
    {
    }

    /** @phpstan-ignore-next-line */
    public function message($pubnub, $messageResult)
    {
        $this->context->addMessage($messageResult);
    }

    /** @phpstan-ignore-next-line */
    public function presence($pubnub, $presence)
    {
    }

    /** @phpstan-ignore-next-line */
    public function signal($pubnub, $signal)
    {
    }
}
