<?php

namespace PubNub\Models\Consumer\MessageActions;

class PNMessageAction
{
    public ?string $type;
    public ?string $value;
    public ?int $messageTimetoken;
    public ?string $uuid;
    public ?int $actionTimetoken;

    /**
     *
     * @param null|array<string, mixed> $messageAction
     * @return void
     */
    public function __construct(?array $messageAction = null)
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
