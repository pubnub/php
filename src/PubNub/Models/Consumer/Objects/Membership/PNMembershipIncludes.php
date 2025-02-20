<?php

namespace PubNub\Models\Consumer\Objects\Membership;

use PubNub\Models\Consumer\Objects\PNIncludes;

class PNMembershipIncludes extends PNIncludes
{
    public bool $channel = false;
    public bool $channelCustom = false;
    public bool $channelType = false;
    public bool $channelStatus = false;

    public function __construct()
    {
        $this->mapping = array_merge($this->mapping, [
            'channel' => 'channel.id',
            'channelCustom' => 'channel.custom',
            'channelType' => 'channel.type',
            'channelStatus' => 'channel.status',
        ]);
    }

    public function channel(bool $channel = true): self
    {
        $this->channel = $channel;
        return $this;
    }

    public function channelCustom(bool $channelCustom = true): self
    {
        $this->channelCustom = $channelCustom;
        return $this;
    }

    public function channelType(bool $channelType = true): self
    {
        $this->channelType = $channelType;
        return $this;
    }

    public function channelStatus(bool $channelStatus = true): self
    {
        $this->channelStatus = $channelStatus;
        return $this;
    }
}
