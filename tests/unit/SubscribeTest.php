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
        $test = $this;

        $test->pubnub->setSubscribeTimeout(static::$timeout);
        $test->pubnub->subscribe("timeout_test", function ($response) {

        }, 0, false, function ($response) use ($test) {
            $test->assertEquals("cURL", $response['service']);
            $test->assertEquals("request timeout", $response['message']);
        });
    }

    /**
     * @group subscribe
     * @group subscribe-timeouts
     */
    public function testSubscribeTimeoutHandlerReturnsFalse()
    {
        $test = $this;

        $this->pubnub->setSubscribeTimeout(static::$timeout);
        $this->pubnub->subscribe("timeout_test", function () use ($test) {
            $test->fail("Should not be invoked");
        }, 0, false, function ($response) use ($test) {
            $test->assertEquals("cURL", $response['service']);
            $test->assertEquals("request timeout", $response['message']);

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
        $test = $this;
        $this->pubnub->setSubscribeTimeout(static::$timeout);
        $this->pubnub->subscribe("timeout_test", function ($response) use ($test) {
            $test->assertEquals("cURL", $response['service']);
            $test->assertEquals("request timeout", $response['message']);

            return false;
        });
    }

    /**
     * @group subscribe
     * @group subscribe-errors
     */
    public function testSubscribeWithInvalidCredentials()
    {
        $test = $this;

        $pubnub = new Pubnub("invalid", "credentials");

        $pubnub->subscribe("error_test", function ($response) use ($test) {
            $test->assertTrue($response['error']);
            $test->assertEquals("Invalid Subscribe Key", $response['message']);
            return false;
        });
    }
}
