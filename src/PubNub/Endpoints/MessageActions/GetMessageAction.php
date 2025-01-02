<?php

namespace PubNub\Endpoints\MessageActions;

use PubNub\PubNub;
use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\Models\Consumer\MessageActions\PNGetMessageActionResult;

/** @package PubNub\Endpoints\MessageActions */
class GetMessageAction extends Endpoint
{
    protected bool $endpointAuthRequired = true;
    protected int $endpointConnectTimeout;
    protected int $endpointRequestTimeout;
    protected string $endpointHttpMethod = PNHttpMethod::GET;
    protected int $endpointOperationType = PNOperationType::PNGetMessageActionOperation;
    protected string $endpointName = "Get Message Actions";

    protected const GET_PATH = "/v1/message-actions/%s/channel/%s";
    protected string $channel;
    protected ?string $start;
    protected ?string $end;
    protected ?int $limit;

    public function __construct(PubNub $pubnub)
    {
        parent::__construct($pubnub);
        $this->endpointConnectTimeout = $this->pubnub->getConfiguration()->getConnectTimeout();
        $this->endpointRequestTimeout = $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * Set a channel for the message action
     *
     * @param string $channel
     * @return GetMessageAction
     */
    public function channel(string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Reaction timetoken denoting the start of the range requested. Returned values will be less than start.
     *
     * @param string $start
     * @return GetMessageAction
     */
    public function setStart(string $start): self
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Reaction timetoken denoting the end of the range requested. Returned values will be greater than or equal to end.
     *
     * @param string $end
     * @return GetMessageAction
     */
    public function setEnd(string $end): self
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Number of reactions to return in response.
     *
     * @param int $limit
     * @return GetMessageAction
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams(): void
    {
        if (!$this->channel) {
            throw new PubNubValidationException("Channel Missing");
        }
        $this->validateSubscribeKey();
        $this->validatePublishKey();
    }

    /**
     * @return array<string, string>
     */
    protected function customParams()
    {
        $params = [];

        if (isset($this->start)) {
            $params['start'] = $this->start;
        }
        if (isset($this->end)) {
            $params['end'] = $this->end;
        }
        if (isset($this->limit)) {
            $params['limit'] = $this->limit;
        }

        return $params;
    }

    /**
     * @return string | null
     */
    protected function buildData()
    {
        return null;
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        return sprintf(
            self::GET_PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channel
        );
    }

    /**
     * @return PNGetMessageActionResult
     */
    public function sync(): PNGetMessageActionResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, string> $json Decoded json
     * @return PNGetMessageActionResult
     */
    protected function createResponse($json): PNGetMessageActionResult
    {
        return PNGetMessageActionResult::fromJson($json);
    }
}
