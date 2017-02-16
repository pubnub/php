<?php

namespace PubNub\Endpoints\ChannelGroups;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\ChannelGroup\PNChannelGroupsRemoveChannelResult;
use PubNub\PubNubUtil;

class RemoveChannelFromChannelGroup extends Endpoint
{
    const PATH = "/v1/channel-registration/sub-key/%s/channel-group/%s";

    private $channelGroup;

    private $channels = [];

    public function channels($ch)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $ch);

        return $this;
    }

    /**
     * @param string $channelGroup
     * @return $this
     */
    public function group($channelGroup)
    {
        $this->channelGroup = $channelGroup;

        return $this;
    }

    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (count($this->channels) === 0) {
            throw new PubNubValidationException("Channels missing");
        }

        if (strlen($this->channelGroup) === 0) {
            throw new PubNubValidationException("Channel group missing");
        }
    }

    /**
     * @param array $json Decoded json
     * @return PNChannelGroupsRemoveChannelResult
     */
    protected function createResponse($json)
    {
        return new PNChannelGroupsRemoveChannelResult();
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNRemoveChannelsFromGroupOperation;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return True;
    }

    /**
     * @return null|string
     */
    protected function buildData()
    {
        return null;
    }

    /**
     * @return string
     */
    protected function buildPath()
    {
        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channelGroup
        );
    }

    /**
     * @return array
     */
    protected function buildParams()
    {
        $params = $this->defaultParams();

        $params['remove'] = PubNubUtil::joinItems($this->channels);

        return $params;
    }

    /**
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }
}