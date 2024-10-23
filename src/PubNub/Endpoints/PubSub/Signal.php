<?php

namespace PubNub\Endpoints\PubSub;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\PubSub\PNSignalResult;
use PubNub\PubNubUtil;

class Signal extends Endpoint
{
    protected const SIGNAL_PATH = "/signal/%s/%s/0/%s/0/%s";

    /** @var  mixed $message to send the signal */
    protected $message;

    /** @var  string $channel to send message on*/
    protected $channel;

    /** @var string $customMessageType User defined message type */
    protected ?string $customMessageType;

    /**
     * @param mixed $message
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

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
     * @param string $customMessageType
     * @return $this
     */
    public function customMessageType(?string $customMessageType)
    {
        $this->customMessageType = $customMessageType;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        if ($this->message === null) {
            throw new PubNubValidationException("Message Missing");
        }

        if (!is_string($this->channel) || strlen($this->channel) === 0) {
            throw new PubNubValidationException("Channel Missing");
        }

        $this->validateSubscribeKey();
        $this->validatePublishKey();
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        $stringifiedMessage = PubNubUtil::urlEncode(PubNubUtil::writeValueAsString($this->message));

        return sprintf(
            static::SIGNAL_PATH,
            $this->pubnub->getConfiguration()->getPublishKey(),
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::urlEncode($this->channel),
            $stringifiedMessage
        );
    }


    protected function buildData()
    {
        return [];
    }

    protected function customParams()
    {
        $params = [];

        if (isset($this->customMessageType)) {
            $params['custom_message_type'] = $this->customMessageType;
        }

        return $params;
    }

    /**
     * @return PNSignalResult
     */
    public function sync(): PNSignalResult
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNSignalResult
     */
    protected function createResponse($result): PNSignalResult
    {
        $timetoken = floatval($result[2]);

        return new PNSignalResult($timetoken);
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
    protected function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * @return int
     */
    protected function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    /**
     * @return string
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
        return PNOperationType::PNSignalOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "Signal";
    }
}
