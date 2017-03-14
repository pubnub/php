<?php

namespace PubNub\Endpoints\PubSub;


use PubNub\Builders\PubNubErrorBuilder;
use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\PNPublishResult;
use PubNub\Models\Consumer\PubSub\SubscribeEnvelope;
use PubNub\PubNubException;
use PubNub\PubNubUtil;

class Subscribe extends Endpoint
{
    const PATH = "/v2/subscribe/%s/%s/0";

    /** @var  array */
    protected $channels = [];

    /** @var  array */
    protected $channelGroups = [];

    /** @var  string */
    protected $region;

    /** @var  string */
    protected $filterExpression;

    /** @var  int */
    protected $timetoken;

    /** @var  bool */
    protected $withPresence;

    /**
     * @return PNPublishResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param string|array $ch
     * @return $this
     */
    public function channels($ch)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $ch);

        return $this;
    }

    /**
     * @param string|array $cgs
     * @return $this
     */
    public function groups($cgs)
    {
        $this->channelGroups = PubNubUtil::extendArray($this->channelGroups, $cgs);

        return $this;
    }

    /**
     * @param string $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @param string $filterExpression
     * @return $this
     */
    public function setFilterExpression($filterExpression)
    {
        $this->filterExpression = $filterExpression;

        return $this;
    }

    /**
     * @param int $timetoken
     * @return $this
     */
    public function setTimetoken($timetoken)
    {
        $this->timetoken = $timetoken;

        return $this;
    }

    /**
     * @param bool $withPresence
     * @return $this
     */
    public function setWithPresence($withPresence)
    {
        $this->withPresence = $withPresence;

        return $this;
    }

    public function getChannel()
    {

    }

    protected function validateParams()
    {
        if (count($this->channels) === 0 && count($this->channelGroups) === 0) {
            throw new PubNubValidationException("At least one channel or channel group should be specified");
        }

        $this->validateSubscribeKey();
        $this->validatePublishKey();
    }

    protected function buildData()
    {
        return null;
    }

    protected function customParams()
    {
        $params = [];

        if (count($this->channelGroups) > 0) {
            $params['channel-group'] = PubNubUtil::joinChannels($this->channelGroups);
        }

        if (strlen($this->filterExpression)) {
            $params['filter-expr'] = PubNubUtil::urlEncode($this->filterExpression);
        }

        if ($this->timetoken !== null) {
            $params['tt'] = (string) $this->timetoken;
        }

        if ($this->region !== null) {
            $params['tr'] = $this->region;
        }

        return $params;
    }

    protected function buildPath()
    {
        $channels = PubNubUtil::joinChannels($this->channels);

        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $channels
        );
    }

    /**
     * @param array $json Decoded json
     * @return SubscribeEnvelope
     */
    protected function createResponse($json)
    {
        return SubscribeEnvelope::fromJson($json);
    }

    protected function getOperationType()
    {
        return PNOperationType::PNSubscribeOperation;
    }

    protected function isAuthRequired()
    {
        return true;
    }

    protected function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getSubscribeTimeout();
    }

    protected function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }
}
