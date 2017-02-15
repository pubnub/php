<?php

namespace PubNub\Endpoints\ChannelGroups;


use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\ChannelGroup\PNChannelGroupsRemoveGroupResult;


class RemoveChannelGroup extends Endpoint
{
    const PATH = "/v1/channel-registration/sub-key/%s/channel-group/%s/remove";

    private $channelGroup;

    /**
     * @param string $channelGroup
     * @return $this
     */
    public function channelGroup($channelGroup)
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
     * @return PNChannelGroupsRemoveGroupResult
     */
    protected function createResponse($json)
    {
        return new PNChannelGroupsRemoveGroupResult();
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNRemoveGroupOperation;
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