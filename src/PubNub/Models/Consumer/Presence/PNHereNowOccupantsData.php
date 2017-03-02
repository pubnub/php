<?php

namespace PubNub\Models\Consumer\Presence;

class PNHereNowOccupantsData
{
    protected $uuid;

    protected $state;

    public function __construct($uuid, $state)
    {
        $this->uuid = $uuid;
        $this->state = $state;
    }

    public function __toString()
    {
        return sprintf("HereNow Occupants Data for '%s': %s", $this->uuid, $this->state);
    }
}