<?php

namespace PubNub\Models\Consumer\Presence;

use PubNub\PubNubUtil;

class PNWhereNowResult
{
    protected $channels;

    public function __construct($channels)
    {
        $this->channels = $channels;
    }

    public function __toString()
    {
        return sprintf("User is currently subscribed to %s", PubNubUtil::joinItems($this->channels));
    }

    public static function fromJson($json)
    {
        return new PNWhereNowResult($json['payload']['channels']);
    }
}
