<?php

namespace PubNub\Endpoints\MessageActions;

use PubNub\PubNub;
use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\Models\Consumer\MessageActions\PNMessageAction;
use PubNub\Models\Consumer\MessageActions\PNAddMessageActionResult;
use PubNub\PubNubUtil;

class AddMessageAction extends Endpoint
{
    protected bool $endpointAuthRequired = true;
    protected int $endpointConnectTimeout;
    protected int $endpointRequestTimeout;
    protected string $endpointHttpMethod = PNHttpMethod::POST;
    protected int $endpointOperationType = PNOperationType::PNAddMessageActionOperation;
    protected string $endpointName = "Set Message Actions";

    protected const POST_PATH = "/v1/message-actions/%s/channel/%s/message/%s";
    protected string $channel;
    protected PNMessageAction $messageAction;

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
     * @return AddMessageAction
     */
    public function channel(string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Set the message action with instance of PNMessageAction
     *
     * @param PNMessageAction $messageAction
     * @return AddMessageAction
     */
    public function messageAction(PNMessageAction $messageAction): self
    {
        $this->messageAction = $messageAction;
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
        $this->validateMessageAction();
        $this->validateSubscribeKey();
        $this->validatePublishKey();
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateMessageAction(): void
    {
        if (!isset($this->messageAction)) {
            throw new PubNubValidationException("Message Action Missing");
        }
        if (!isset($this->messageAction->type)) {
            throw new PubNubValidationException("Message Action Type Missing");
        }
        if (!isset($this->messageAction->value)) {
            throw new PubNubValidationException("Message Action Value Missing");
        }
        if (!$this->messageAction->messageTimetoken) {
            throw new PubNubValidationException("Message Action Message Timetoken Missing");
        }
    }

    /**
     * @return array<string, string>
     */
    protected function customParams()
    {
        return [
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function customHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }

    /**
     * @return string | null
     */
    protected function buildData()
    {
        return PubNubUtil::writeValueAsString([
            'type' => $this->messageAction->type,
            'value' => $this->messageAction->value,
        ]);
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        return sprintf(
            self::POST_PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channel,
            (int)$this->messageAction->messageTimetoken
        );
    }

    /**
     * @return PNAddMessageActionResult
     */
    public function sync(): PNAddMessageActionResult
    {
        return parent::sync();
    }

    /**
     * @param array<string, string> $json Decoded json
     * @return PNAddMessageActionResult
     */
    protected function createResponse($json): PNAddMessageActionResult
    {
        return PNAddMessageActionResult::fromJson($json);
    }
}
