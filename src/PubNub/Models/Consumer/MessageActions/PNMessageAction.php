<?php

namespace PubNub\Models\Consumer\MessageActions;

class PNMessageAction
{
    public $type;
    public $value;
    public $messageTimetoken;
    public $uuid;
    public $actionTimetoken;

    public function __construct($messageAction = null)
    {
        if ($messageAction != null) {
            $this->type = $messageAction['type'];
            $this->value = $messageAction['value'];
            $this->messageTimetoken = $messageAction['messageTimetoken'];
            $this->uuid = $messageAction['uuid'] ?? null;
            $this->actionTimetoken = $messageAction['actionTimetoken'] ?? null;
        } else {
            $this->type = null;
            $this->value = null;
            $this->messageTimetoken = null;
            $this->uuid = null;
            $this->actionTimetoken = null;
        }
    }
}
