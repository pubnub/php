<?php

use Pubnub\Pubnub;


class WCValidationTest extends TestCase
{
    protected static $message = 'Hello from uuid() test';
    protected static $channel = 'pubnub_php_test';

    /**
     * @group wc
     */
    public function testPresenceWhenOnlyPresence()
    {
        $this->assertTrue(Pubnub::shouldWildcardMessageBePassedToUserCallback(
            "foo.bar-pnpres", "foo.*", array(), array("foo.*")
        ));
    }

    /**
     * @group wc
     */
    public function testPresenceWhenBothPresenceAndSubscribe()
    {
        $this->assertTrue(Pubnub::shouldWildcardMessageBePassedToUserCallback(
            "foo.bar-pnpres", "foo.*", array("foo.*"), array("foo.*")
        ));
    }

    /**
     * @group wc
     */
    public function testPresenceWhenOnlySubscribe()
    {
        $this->assertFalse(Pubnub::shouldWildcardMessageBePassedToUserCallback(
            "foo.bar-pnpres", "foo.*", array("foo.*"), array()
        ));
    }

    /**
     * @group wc
     */
    public function testSubscribeWhenOnlyPresence()
    {
        $this->assertFalse(Pubnub::shouldWildcardMessageBePassedToUserCallback(
            "foo.bar", "foo.*", array(), array("foo.*")
        ));
    }

    /**
     * @group wc
     */
    public function testSubscribeWhenBothSubscribeAndPresence()
    {
        $this->assertTrue(Pubnub::shouldWildcardMessageBePassedToUserCallback(
            "foo.bar", "foo.*", array("foo.*"), array("foo.*")
        ));
    }

    /**
     * @group wc
     */
    public function testSubscribeWhenOnlySubscribe()
    {
        $this->assertTrue(Pubnub::shouldWildcardMessageBePassedToUserCallback(
            "foo.bar", "foo.*", array("foo.*"), array()
        ));
    }
}
 