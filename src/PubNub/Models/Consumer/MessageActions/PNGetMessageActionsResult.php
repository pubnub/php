<?php

namespace PubNub\Models\Consumer\MessageActions;

use PubNub\Models\Consumer\MessageActions\PNMessageAction;

class PNGetMessageActionsResult extends PNMessageAction
{
    /**
     *
     * @var PNMessageAction[] $actions
     */
    public array $actions;

    /**
     *
     * @param mixed $json
     * @return PNGetMessageActionsResult
          */
    public static function fromJson(mixed $json): self
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
