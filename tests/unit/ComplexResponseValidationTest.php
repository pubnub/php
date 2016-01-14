<?php

use Pubnub\Pubnub;


class ComplexResponseValidationTest extends TestCase
{
    protected static $message = 'Hello from uuid() test';
    protected static $channel = 'pubnub_php_test';
    private $logger;

    public function setUp() {
        $this->logger = new \Pubnub\PubnubLogger("WCValidationTest");
    }

    /**
     * @group complex-parsing
     */
    public function testPresenceWhenOnlyPresence()
    {
        $this->assertTrue(Pubnub::shouldComplexMessageBePassedToUserCallback(
            "foo.bar-pnpres", "foo.*", array(), array("foo.*"), array(), $this->logger
        ));
    }

    /**
     * @group complex-parsing
     */
    public function testPresenceWhenBothPresenceAndSubscribe()
    {
        $this->assertTrue(Pubnub::shouldComplexMessageBePassedToUserCallback(
            "foo.bar-pnpres", "foo.*", array("foo.*"), array("foo.*"), array(), $this->logger
        ));
    }

    /**
     * @group complex-parsing
     */
    public function testPresenceWhenOnlySubscribe()
    {
        $this->assertFalse(Pubnub::shouldComplexMessageBePassedToUserCallback(
            "foo.bar-pnpres", "foo.*", array("foo.*"), array(), array(), $this->logger
        ));
    }

    /**
     * @group complex-parsing
     */
    public function testSubscribeWhenOnlyPresence()
    {
        $this->assertFalse(Pubnub::shouldComplexMessageBePassedToUserCallback(
            "foo.bar", "foo.*", array(), array("foo.*"), array(), $this->logger
        ));
    }

    /**
     * @group complex-parsing
     */
    public function testSubscribeWhenBothSubscribeAndPresence()
    {
        $this->assertTrue(Pubnub::shouldComplexMessageBePassedToUserCallback(
            "foo.bar", "foo.*", array("foo.*"), array("foo.*"), array(), $this->logger
        ));
    }

    /**
     * @group complex-parsing
     */
    public function testSubscribeWhenOnlySubscribe()
    {
        $this->assertTrue(Pubnub::shouldComplexMessageBePassedToUserCallback(
            "foo.bar", "foo.*", array("foo.*"), array(), array(), $this->logger
        ));
    }

    /**
     * @group complex-parsing
     */
    public function testSubscribeChannelGroupMessage()
    {
        $this->assertTrue(Pubnub::shouldComplexMessageBePassedToUserCallback(
            "foo", "bar", array(), array(), array("bar"), $this->logger
        ));
    }

}
 