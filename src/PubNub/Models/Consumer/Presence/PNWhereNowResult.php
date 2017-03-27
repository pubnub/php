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

    public function getChannels()
    {
        return $this->channels;
    }

    public function __toString()
    {
        return sprintf("User is currently subscribed to %s", PubNubUtil::joinItems($this->channels));
    }

    /**
     * @param array $payload
     * @return PNWhereNowResult
     */
    public static function fromPayload(array $payload)
    {
        return new PNWhereNowResult($payload['channels']);
    }
}
