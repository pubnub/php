<?php

namespace PubNub\Endpoints;

use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNHttpMethod;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\History\PNHistoryResult;
use PubNub\Models\Consumer\PNTimeResult;
use PubNub\PubNubUtil;
use PubNubTestCase;

class History extends Endpoint
{
    const PATH = "/v2/history/sub-key/%s/channel/%s";
    const MAX_COUNT = 100;

    /** @var string */
    protected $channel;

    /** @var int */
    protected $start;

    /** @var int */
    protected $end;

    /** @var bool */
    protected $reverse;

    /** @var int */
    protected $count;

    /** @var bool */
    protected $includeTimetoken;

    /**
     * @param string $channel
     * @return $this
     */
    public function channel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @param int $start
     * @return $this
     */
    public function start($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @param int $end
     * @return $this
     */
    public function end($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @param bool $reverse
     * @return $this
     */
    public function reverse($reverse)
    {
        $this->reverse = $reverse;

        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function count($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @param bool $includeTimetoken
     * @return $this
     */
    public function includeTimetoken($includeTimetoken)
    {
        $this->includeTimetoken = $includeTimetoken;

        return $this;
    }

    public function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateChannel();
    }

    public function validateChannel()
    {
        if ($this->channel !== null || count(PubNubUtil::joinItems($this->channel)) === 0) {
            throw new PubNubValidationException("Channel missing");
        }
    }

    /**
     * @param array $json Decoded json
     * @return mixed
     */
    protected function createResponse($json)
    {
        return PNHistoryResult::fromJson(
            $json,
            $this->pubnub->getConfiguration()->getCrypto(),
            $this->includeTimetoken,
            $this->pubnub->getConfiguration()->getCipherKey()
        );
    }

    /**
     * @return int
     */
    public function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * @return int
     */
    public function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNHistoryOperation;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
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
        return sprintf(static::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), PubNubUtil::urlEncode($this->channel));
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if ($this->start !== null) {
            $params['start'] = (string) $this->start;
        }

        if ($this->end !== null) {
            $params['end'] = (string) $this->end;
        }

        if ($this->count !== null && $this->count > 0 && $this->count <= static::MAX_COUNT) {
            $params['count'] = (string) $this->count;
        } else {
            $params['count'] = '100';
        }

        if ($this->reverse !== null) {
            $this->reverse ? $params['reverse'] = "true" : $params['reverse'] = "false";
        }

        if ($this->includeTimetoken !== null) {
            $this->includeTimetoken ? $params['include_token'] = "true" : $params['include_token'] = "false";
        }

        return $params;
    }

    /**
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }

    /**
     * @return string name
     */
    public function getName()
    {
        return "History";
    }
}