<?php

namespace PubNub\Endpoints;

use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNHttpMethod;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\History\PNHistoryResult;
use PubNub\PubNubUtil;

class History extends Endpoint
{
    protected const PATH = "/v2/history/sub-key/%s/channel/%s";
    protected const MAX_COUNT = 100;

    /** @var string */
    protected string $channel;

    /** @var int */
    protected ?int $start;

    /** @var int */
    protected ?int $end;

    /** @var bool */
    protected ?bool $reverse;

    /** @var int */
    protected ?int $count;

    /** @var bool */
    protected ?bool $includeTimetoken;

    protected ?bool $includeCustomMessageType;

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

    /**
     * @param bool $includeCustomMessageType
     * @return $this
     */
    public function includeCustomMessageType(bool $includeCustomMessageType)
    {
        $this->includeCustomMessageType = $includeCustomMessageType;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    public function validateParams()
    {
        $this->validateSubscribeKey();

        if (!isset($this->channel) || strlen($this->channel) === 0) {
            throw new PubNubValidationException("Channel missing");
        }
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if (isset($this->start)) {
            $params['start'] = (string) $this->start;
        }

        if (isset($this->end)) {
            $params['end'] = (string) $this->end;
        }

        if (isset($this->count) && $this->count > 0 && $this->count <= static::MAX_COUNT) {
            $params['count'] = (string) $this->count;
        } else {
            $params['count'] = '100';
        }

        if (isset($this->reverse)) {
            $this->reverse ? $params['reverse'] = "true" : $params['reverse'] = "false";
        }

        if (isset($this->includeTimetoken)) {
            $this->includeTimetoken ? $params['include_token'] = "true" : $params['include_token'] = "false";
        }

        if (isset($this->includeCustomMessageType)) {
            if ($this->includeCustomMessageType) {
                $params['include_custom_message_type'] = "true";
            } else {
                $params['include_custom_message_type'] = "false";
            }
        }

        return $params;
    }

    /**
     * @return null
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
            PubNubUtil::urlEncode($this->channel)
        );
    }

    /**
     * @return PNHistoryResult
     */
    public function sync(): PNHistoryResult
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNHistoryResult
     */
    protected function createResponse($result): PNHistoryResult
    {
        $includeTimetoken = isset($this->includeTimetoken) ? $this->includeTimetoken : null;
        try {
            return PNHistoryResult::fromJson(
                $result,
                $this->pubnub->getConfiguration()->getCryptoSafe(),
                $includeTimetoken,
                $this->pubnub->getConfiguration()->getCipherKey()
            );
        } catch (PubNubValidationException $e) {
            return PNHistoryResult::fromJson(
                $result,
                null,
                $includeTimetoken,
                null
            );
        }
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
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
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNHistoryOperation;
    }

    /**
     * @return string name
     */
    public function getName()
    {
        return "History";
    }
}
