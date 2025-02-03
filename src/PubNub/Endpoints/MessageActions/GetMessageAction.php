<?php

namespace PubNub\Endpoints\MessageActions;

use PubNub\PubNub;
use PubNub\Endpoints\MessageActions\GetMessageActions;

// TODO: Remove in 8.0.0
/** @package PubNub\Endpoints\MessageActions */
class GetMessageAction extends GetMessageActions
{
    public function __construct(PubNub $pubnub)
    {
        trigger_error("This class is deprecated. Please use GetMessageActions instead.", E_USER_DEPRECATED);
        parent::__construct($pubnub);
    }
}
