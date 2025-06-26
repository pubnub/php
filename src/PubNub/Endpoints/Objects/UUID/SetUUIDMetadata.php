<?php

namespace PubNub\Endpoints\Objects\UUID;

use PubNub\Endpoints\Endpoint;
use PubNub\Endpoints\Objects\MatchesETagTrait;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\UUID\PNSetUUIDMetadataResult;
use PubNub\PubNubUtil;

class SetUUIDMetadata extends Endpoint
{
    use MatchesETagTrait;

    protected const PATH = "/v2/objects/%s/uuids/%s";

    /** @var string */
    protected $uuid;

    /** @var array */
    protected $meta;

    /**
     * @param string $uuid
     * @return $this
     */
    public function uuid($uuid): static
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @param array $meta
     * @return $this
     */
    public function meta($meta): static
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function name(string $name): static
    {
        $this->meta['name'] = $name;

        return $this;
    }

    /**
     * @param string $externalId
     * @return $this
     */
    public function externalId(string $externalId): static
    {
        $this->meta['externalId'] = $externalId;
        return $this;
    }

    /**
     * @param string $profileUrl
     * @return $this
     */
    public function profileUrl(string $profileUrl): static
    {
        $this->meta['profileUrl'] = $profileUrl;
        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function email(string $email): static
    {
        $this->meta['email'] = $email;
        return $this;
    }

    /**
     * @param array<string, mixed> $custom
     * @return $this
     */
    public function custom(array $custom): static
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

        if (!is_string($this->uuid)) {
            throw new PubNubValidationException("uuid missing");
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
            $this->uuid
        );
    }

    public function sync(): PNSetUUIDMetadataResult
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNSetUUIDMetadataResult
     */
    protected function createResponse($result): PNSetUUIDMetadataResult
    {
        return PNSetUUIDMetadataResult::fromPayload($result);
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
        return PNOperationType::PNSetUUIDMetadataOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "SetUUIDMetadata";
    }
}
