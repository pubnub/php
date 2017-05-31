<?php

use PHPUnit\Framework\TestCase;
use PubNub\PubNub;


class UtilsTest extends TestCase
{
    /**
     * @group publish
     * @group publish-unit
     */
    public function testSequenceCounterRestartsAfterMaxReached()
    {
        $pubnub = PubNub::Demo();
        $this->assertEquals(1, $pubnub->getSequenceId());

        for ($i = 0; $i < PubNub::$MAX_SEQUENCE; $i++) {
            $pubnub->getSequenceId();
        }

        $this->assertEquals(2, $pubnub->getSequenceId());
    }
}
