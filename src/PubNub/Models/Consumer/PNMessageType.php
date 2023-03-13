<?php

namespace PubNub\Models\Consumer;

final class PNMessageType
{
    private $pnMessageType = null;
    private $messageType = null;
    private $typeMapping = [
        '0' => 'pn_message',
        '1' => 'pn_signal',
        '2' => 'pn_object',
        '3' => 'pn_message_action',
        '4' => 'pn_file',
    ];

    public function __construct($messageType)
    {
        $this->messageType = $messageType;
    }

    public static function createWithPnMessageType($messageType, $pnMessageType)
    {
        $messageType = new self($messageType);
        $messageType->setPnMessageType($pnMessageType);
        return $messageType;
    }

    private function setPnMessageType($pnMessageType)
    {
        $this->pnMessageType = $pnMessageType;
        return $this;
    }

    public function getPnMessageType()
    {
        return $this->typeMapping[$this->pnMessageType ? $this->pnMessageType : '0'];
    }

    public function __toString()
    {
        return $this->messageType ? $this->messageType : $this->getPnMessageType();
    }

    public function getMessageType()
    {
        return $this->messageType;
    }
}
