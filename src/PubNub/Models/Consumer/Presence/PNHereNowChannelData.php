<?php

namespace PubNub\Models\Consumer\Presence;

use PubNub\PubNubUtil;

class PNHereNowChannelData
{
    protected $channelName;

    protected $occupancy;

    protected $occupants;

    public function __construct($channelName, $occupancy, $occupants)
    {
        $this->channelName = $channelName;
        $this->occupancy = $occupancy;
        $this->occupants = $occupants;
    }

    public function __toString()
    {
        return sprintf("HereNow Channel Data for channel '%s': occupancy: %s, occupants: %s",
            $this->channelName, $this->occupancy, $this->occupants);
    }

    public static function fromJson($name, $json)
    {
        if (array_key_exists('uuids', $json)) {
            $occupants = [];

            foreach ($json['uuids'] as $user) {
                if (PubNubUtil::isAssoc($user) && count($user) > 0) {
                    if (array_key_exists('state', $user)) {
                        $occupants[] = new PNHereNowOccupantsData($user['uuids'], $user['state']);
                    } else  {
                        $occupants[] = new PNHereNowOccupantsData($user['uuids'], null);
                    }
                } else {
                    $occupants[] = new PNHereNowOccupantsData($user, null);
                }
            }
        } else {
            $occupants = null;
        }

        return new PNHereNowChannelData(
            $name,
            $json['occupancy'],
            $occupants
        );
    }
}