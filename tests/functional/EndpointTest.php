<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use PubNub\Endpoints\Endpoint;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\PubNubException;


class EndpointTest extends TestCase
{
    protected static $channel = 'pubnub_php_test';

    public function testValidatesSubscribeKeyNotSet()
    {
        $pubnub = new PubNub(new PNConfiguration());
        $endpoint = new EndpointImplementation($pubnub);

        try {
            $endpoint->validateSubscribeKey();
            $this->fail("No exception was thrown");
        } catch (PubNubException $exception) {
            $this->assertEquals("ULS configuration failed. Subscribe Key not configured.", $exception->getPubnubError()->getMessage());
        }
    }

    public function testValidatesSubscribeKeyEmptyString()
    {
        $pubnub = new PubNub((new PNConfiguration())->setSubscribeKey(""));
        $endpoint = new EndpointImplementation($pubnub);

        try {
            $endpoint->validateSubscribeKey();
            $this->fail("No exception was thrown");
        } catch (PubNubException $exception) {
            $this->assertEquals("ULS configuration failed. Subscribe Key not configured.", $exception->getPubnubError()->getMessage());
        }
    }

    public function testValidatesPublishKeyNull()
    {
        $pubnub = new PubNub(new PNConfiguration());
        $endpoint = new EndpointImplementation($pubnub);

        try {
            $endpoint->validatePublishKey();
            $this->fail("No exception was thrown");
        } catch (PubNubException $exception) {
            $this->assertEquals("ULS configuration failed. Publish Key not configured.", $exception->getPubnubError()->getMessage());
        }
    }

    public function testValidatesPublishKeyEmptyString()
    {
        $pubnub = new PubNub((new PNConfiguration())->setPublishKey(""));
        $endpoint = new EndpointImplementation($pubnub);

        try {
            $endpoint->validatePublishKey();
            $this->fail("No exception was thrown");
        } catch (PubNubException $exception) {
            $this->assertEquals("ULS configuration failed. Publish Key not configured.", $exception->getPubnubError()->getMessage());
        }
    }
}


class EndpointImplementation extends Endpoint
{

    public function validateSubscribeKey()
    {
        parent::validateSubscribeKey();
    }

    public function validatePublishKey()
    {
        parent::validatePublishKey();
    }

    public function validateParams()
    {
    }

    /**
     * @param array $json Decoded json
     * @return mixed
     */
    protected function createResponse($json)
    {
        return null;
    }

    protected function getOperationType()
    {
        // TODO: Implement getOperationType() method.
    }

    protected function isAuthRequired()
    {
        // TODO: Implement isAuthRequired() method.
    }

    protected function buildPath()
    {
        // TODO: Implement buildPath() method.
    }

    protected function httpMethod()
    {
        // TODO: Implement httpMethod() method.
    }

    /**
     * @return array
     */
    protected function buildParams()
    {
        // TODO: Implement buildParams() method.
    }
}