<?php

namespace PubNub\Models\Consumer\MessageActions;

use PubNub\Models\Consumer\MessageActions\PNMessageAction;

class PNGetMessageActionResult extends PNMessageAction
{
    public array $actions;

    /**
     *
     * @param mixed $json
     * @return PNGetMessageActionResult
          */
    public static function fromJson($json): self
    {

        $actions = [];
        foreach ($json['data'] as $action) {
            $actions[] = new self($action);
        }
        $result = new self();
        $result->actions = $actions;
        return $result;
    }
}
