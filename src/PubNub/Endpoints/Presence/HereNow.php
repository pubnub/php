<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Models\Consumer\Presence\PNHereNowResult;
use PubNub\PubNubUtil;
use PubNub\PubNub;

class HereNow extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/channel/%s";
    const GLOBAL_PATH = "/v2/presence/sub-key/%s";

    /**  @var string[] */
    protected $channels = [];

    /**  @var string[] */
    protected $groups = [];

    /**  @var bool */
    protected $includeState = false;

    /**  @var bool */
    protected $includeUuids = true;

    /**  @var  PubNub */
    protected $pubnub;

    /**
     * @param string|array $channels
     * @return $this
     */
    public function channels($channels)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $channels);

        return $this;
    }

    /**
     * @param string|array $channelGroups
     * @return $this
     */
    public function channelGroups($channelGroups)
    {
        $this->groups = PubNubUtil::extendArray($this->groups, $channelGroups);

        return $this;
    }

    /**
     * @param bool $shouldIncludeState
     * @return $this
     */
    public function includeState($shouldIncludeState)
    {
        $this->includeState = $shouldIncludeState;

        return $this;
    }

    /**
     * @param bool $includeUuids
     * @return $this
     */
    public function includeUuids($includeUuids)
    {
        $this->includeUuids = $includeUuids;

        return $this;
    }

    protected function validateParams()
    {
        $this->validateSubscribeKey();
    }

    /**
     * @return null|string
     */
    protected function buildData()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if (count($this->groups) > 0) {
            $params['channel-group'] = PubNubUtil::joinItems($this->groups);
        }

        if ($this->includeState) {
            $params['state'] = "1";
        }

        if (!$this->includeUuids) {
            $params['disable-uuids'] = "1";
        }

        return $params;
    }

    /**
     * @return string
     */
    public function buildPath()
    {
        if (count($this->channels) === 0 && count($this->groups) === 0) {
            return sprintf(HereNow::GLOBAL_PATH, $this->pubnub->getConfiguration()->getSubscribeKey());
        } else {
            return sprintf(HereNow::PATH,
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                PubNubUtil::joinChannels($this->channels)
            );
        }
    }

    /**
     * @param array $json Decoded json
     * @return PNHereNowResult
     */
    protected function createResponse($json)
    {
        return PNHereNowResult::fromJson($json, $this->channels);
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNHereNowOperation;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    /**
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }
}