<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Presence\PNWhereNowResult;

class WhereNow extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/uuid/%s";

    /** @var string */
    protected $uuid;

    public function __construct($pubnubInstance)
    {
        parent::__construct($pubnubInstance);
        $this->uuid = $this->pubnub->getConfiguration()->getUuid();
    }

    /**
     * @param $uuid
     * @return string
     */
    public function uuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if ($this->uuid === null || !is_string($this->uuid)) {
            throw new PubNubValidationException("uuid missing or not a string");
        }
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
        return [];
    }

    /**
     * @return string
     */
    public function buildPath()
    {
        return sprintf(WhereNow::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->uuid
        );
    }

    /**
     * @param array $json Decoded json
     * @return PNWhereNowResult
     */
    protected function createResponse($json)
    {
        return PNWhereNowResult::fromJson($json);
    }


    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNWhereNowOperation;
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
}