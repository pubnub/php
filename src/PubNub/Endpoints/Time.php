<?php

namespace PubNub\Endpoints;


use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNHttpMethod;
use PubNub\Models\Consumer\PNTimeResult;

class Time extends Endpoint
{
    const TIME_PATH = "/time/0";

    /**
     * @return PNTimeResult
     */
    public function sync()
    {
        return parent::sync();
    }

    protected function validateParams()
    {
        // nothing to validate
    }

    /**
     * @return null
     */
    protected function buildData()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function buildParams()
    {
        return $this->defaultParams();
    }

    /**
     * @param array $json
     * @return PNTimeResult
     */
    protected function createResponse($json)
    {
        $timetoken = (int) $json[0];

        $response = new PNTimeResult($timetoken);

        return $response;
    }

    protected function getOperationType()
    {
        return PNOperationType::PNTimeOperation;
    }

    protected function isAuthRequired()
    {
        return false;
    }

    protected function buildPath()
    {
        return static::TIME_PATH;
    }

    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }
}