<?php

namespace PubNub\Models\Consumer;

final class PNMessageType
{
    private $internal = null;
    private $custom = null;

    public function __construct($custom)
    {
        $this->custom = $custom;
    }

    public static function createWithInternal($custom, $internal)
    {
        $messageType = new self($custom);
        $messageType->setInternal($internal);
        return $messageType;
    }

    private function setInternal($internal)
    {
        $this->internal = $internal;
        return $this;
    }

    public function getInternal()
    {
        return $this->internal;
    }

    public function __toString()
    {
        return $this->custom;
    }

    public function getCustom()
    {
        return $this->custom;
    }
}
