<?php

namespace PubNub\Endpoints;


use Enums\PNOperationTypes;
use PubNub\Models\Consumer\PNTimeResult;

class Time extends Endpoint
{
    const TIME_PATH = "/time/0";

    protected function validateParams()
    {
        // do nothing
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
        return PNOperationTypes::PNTimeOperation;
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
        // TODO: Implement httpMethod() method.
    }
}