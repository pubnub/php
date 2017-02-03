<?php

namespace PubNub\Models\ResponseHelpers;


use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubException;

class PNStatus
{
    /** @var  PubNubException */
    private $exception;

    /** @var  PNStatusCategory */
    private $category;

    /** @var  int */
    private $statusCode;

    /** @var  PNOperationType */
    private $operation;

    /** @var  bool */
    private $tlsEnabled;

    /** @var  string */
    private $uuid;

    /** @var  string */
    private $authKey;

    /** @var  string */
    private $origin;

    /** @var  \Requests_Response */
    private $originalResponse;

    /** @var  null|array */
    private $affectedChannels;

    /** @var  null|array */
    private $affectedChannelGroups;

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->exception !== null;
    }

    /**
     * @return PubNubException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param PubNubException $exception
     */
    public function setException($exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return PNStatusCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param PNStatusCategory $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return PNOperationType
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param int $operation
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
    }

    /**
     * @return bool
     */
    public function isTlsEnabled()
    {
        return $this->tlsEnabled;
    }

    /**
     * @param bool $tlsEnabled
     */
    public function setTlsEnabled($tlsEnabled)
    {
        $this->tlsEnabled = $tlsEnabled;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @param string $authKey
     */
    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * @return \Requests_Response
     */
    public function getOriginalResponse()
    {
        return $this->originalResponse;
    }

    /**
     * @param \Requests_Response $originalResponse
     */
    public function setOriginalResponse($originalResponse)
    {
        $this->originalResponse = $originalResponse;
    }

    /**
     * @return array|null
     */
    public function getAffectedChannels()
    {
        return $this->affectedChannels;
    }

    /**
     * @param array|null $affectedChannels
     */
    public function setAffectedChannels($affectedChannels)
    {
        $this->affectedChannels = $affectedChannels;
    }

    /**
     * @return array|null
     */
    public function getAffectedChannelGroups()
    {
        return $this->affectedChannelGroups;
    }

    /**
     * @param array|null $affectedChannelGroups
     */
    public function setAffectedChannelGroups($affectedChannelGroups)
    {
        $this->affectedChannelGroups = $affectedChannelGroups;
    }
}