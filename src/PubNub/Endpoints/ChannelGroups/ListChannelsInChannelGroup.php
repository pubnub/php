<?php

namespace PubNub\Endpoints\ChannelGroups;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\ChannelGroup\PNChannelGroupsListChannelsResult;

class ListChannelsInChannelGroup extends Endpoint
{
    const PATH = "/v1/channel-registration/sub-key/%s/channel-group/%s";

    private $channelGroup;

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

        if ($this->channelGroup === null || empty($this->channelGroup)) {
            throw new PubNubValidationException("Channel group missing");
        }
    }

    /**
     * @param array $json Decoded json
     * @return PNChannelGroupsListChannelsResult
     */
    protected function createResponse($json)
    {
        if (array_key_exists('payload', $json) && array_key_exists('channels', $json['payload'])) {
            return new PNChannelGroupsListChannelsResult($json['payload']['channels']);
        } else {
            return new PNChannelGroupsListChannelsResult([]);
        }
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNChannelsForGroupOperation;
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
            self::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channelGroup
        );
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = $this->defaultParams();

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