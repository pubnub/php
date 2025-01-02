<?php

namespace PubNub\Endpoints\MessageActions;

use PubNub\PubNub;
use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\Models\Consumer\MessageActions\PNRemoveMessageActionResult;

/** @package PubNub\Endpoints\MessageActions */
class RemoveMessageAction extends Endpoint
{
    protected bool $endpointAuthRequired = true;
    protected int $endpointConnectTimeout;
    protected int $endpointRequestTimeout;
    protected string $endpointHttpMethod = PNHttpMethod::DELETE;
    protected int $endpointOperationType = PNOperationType::PNGetMessageActionOperation;
    protected string $endpointName = "Get Message Actions";

    protected const DELETE_PATH = "/v1/message-actions/%s/channel/%s/message/%s/action/%s";
    protected string $channel;
    protected int | float $messageTimetoken;
    protected int | float $actionTimetoken;

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
     * @return RemoveMessageAction
     */
    public function channel(string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * The publish timetoken of a parent message.
     *
     * @param int | float $messageTimetoken
     * @return RemoveMessageAction
     */
    public function messageTimetoken(int | float $messageTimetoken): self
    {
        $this->messageTimetoken = $messageTimetoken;
        return $this;
    }

    /**
     * The publish timetoken of the reaction.
     *
     * @param int | float $actionTimetoken
     * @return RemoveMessageAction
     */
    public function actionTimetoken(int | float $actionTimetoken): self
    {
        $this->actionTimetoken = $actionTimetoken;
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

        if (!$this->messageTimetoken) {
            throw new PubNubValidationException("Message Timetoken Missing");
        }

        if (!$this->actionTimetoken) {
            throw new PubNubValidationException("Action Timetoken Missing");
        }
        $this->validateSubscribeKey();
    }

    /**
     * @return array<string, string>
     */
    protected function customParams()
    {
        return [];
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
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        return sprintf(
            self::DELETE_PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channel,
            $this->messageTimetoken,
            $this->actionTimetoken
        );
    }

    /**
     * @return PNRemoveMessageActionResult
     */
    public function sync(): PNRemoveMessageActionResult
    {
        return parent::sync();
    }

    /**
     * @param mixed $json Decoded json
     * @return PNRemoveMessageActionResult
     */
    protected function createResponse($json): PNRemoveMessageActionResult
    {
        return new PNRemoveMessageActionResult();
    }
}
