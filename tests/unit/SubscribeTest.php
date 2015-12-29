<?php

use Pubnub\Pubnub;


class SubscribeTest extends TestCase
{
    protected static $timeout = 2;

    /**
     * @group subscribe
     * @group subscribe-timeouts
     */
    public function testSubscribeTimeoutHandlerReturnsNothing()
    {
        $this->pubnub->setSubscribeTimeout(static::$timeout);
        $this->pubnub->subscribe("timeout_test", function ($response) {

        }, 0, false, function ($response) {
            $this->assertEquals("cURL", $response['service']);
            $this->assertEquals("request timeout", $response['message']);
        });
    }

    /**
     * @group subscribe
     * @group subscribe-timeouts
     */
    public function testSubscribeTimeoutHandlerReturnsFalse()
    {
        $this->pubnub->setSubscribeTimeout(static::$timeout);
        $this->pubnub->subscribe("timeout_test", function ($response) {

        }, 0, false, function ($response) {
            $this->assertEquals("cURL", $response['service']);
            $this->assertEquals("request timeout", $response['message']);

            return false;
        });
    }

    /**
     * @group subscribe
     * @group subscribe-timeouts
     *
     */
    public function testSubscribeResponseHandlerActsAsTimeoutHandler()
    {
        $this->pubnub->setSubscribeTimeout(static::$timeout);
        $this->pubnub->subscribe("timeout_test", function ($response) {
            $this->assertEquals("cURL", $response['service']);
            $this->assertEquals("request timeout", $response['message']);

            return false;
        });
    }

    /**
     * @group subscribe
     * @group subscribe-errors
     */
    public function testSubscribeWithInvalidCredentials()
    {
        $pubnub = new Pubnub("invalid", "credentials");

        $pubnub->subscribe("error_test", function ($response) {
            $this->assertTrue($response['error']);
            $this->assertEquals("Invalid Subscribe Key", $response['message']);
            return false;
        });
    }
}
