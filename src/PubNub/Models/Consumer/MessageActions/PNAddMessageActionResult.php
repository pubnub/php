<?php

namespace PubNub\Models\Consumer\MessageActions;

use PubNub\Models\Consumer\MessageActions\PNMessageAction;

class PNAddMessageActionResult extends PNMessageAction
{
    /**
     *
     * @param mixed $json
     * @return PNAddMessageActionResult
     */
    public static function fromJson(mixed $json): self
    {
        $action = new self($json['data']);
        return $action;
    }
}
