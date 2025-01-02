<?php

namespace PubNub\Models\Consumer\MessageActions;

use PubNub\Models\Consumer\MessageActions\PNMessageAction;

class PNAddMessageActionResult extends PNMessageAction
{
    public static function fromJson($json): PNMessageAction
    {
        $action = new self($json['data']);
        return $action;
    }
}
