<?php

namespace PubNub\Endpoints\Objects\Channel;

use PubNub\Endpoints\Endpoint;
use PubNub\Endpoints\Objects\MatchesETagTrait;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Channel\PNSetChannelMetadataResult;
use PubNub\PubNubUtil;

class SetChannelMetadata extends Endpoint
{
    use MatchesETagTrait;

    protected const PATH = "/v2/objects/%s/channels/%s";

    /** @var string */
    protected $channel;

    /** @var array */
    protected $meta;

    /**
     * @param string $ch
     * @return $this
     */
    public function channel($ch)
    {
        $this->channel = $ch;

        return $this;
    }

    /**
     * @deprecated use setName, setDescription and setCustom instead
     *
     * @param array $meta
     * @return $this
     */
    public function meta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name): self
    {
        $this->meta['name'] = $name;
        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description): self
    {
        $this->meta['description'] = $description;
        return $this;
    }

    /**
     * @param array $custom
     * @return $this
     */
    public function setCustom($custom): self
    {
        $this->meta['custom'] = $custom;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (!is_string($this->channel)) {
            throw new PubNubValidationException("channel missing");
        }

        if (empty($this->meta)) {
            throw new PubNubValidationException("meta missing");
        }
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildData()
    {
        return PubNubUtil::writeValueAsString($this->meta);
    }

    /**
     * @return string
     */
    protected function buildPath()
    {
        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channel
        );
    }

    /**
     * @param array $result Decoded json
     * @return PNSetChannelMetadataResult
     */
    protected function createResponse($result): PNSetChannelMetadataResult
    {
        return PNSetChannelMetadataResult::fromPayload($result);
    }

    public function sync(): PNSetChannelMetadataResult
    {
        return parent::sync();
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = $this->defaultParams();

        $params['include'] = 'custom';

        return $params;
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
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return PNHttpMethod::PATCH;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNSetChannelMetadataOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "SetChannelMetadata";
    }
}
