<?php

namespace Tests\Functional;

use PubNub\Endpoints\Presence\GetState;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNub\Models\Consumer\PNPublishResult;
use PubNubTestCase;

class FireTest extends PubNubTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
    }

    public function testFireSingleChannel()
    {
        $fireResponse = $this->pubnub->fire()->channel('test')->message('hello')->meta(['env' => 'testing'])->sync();

        $this->assertTrue($fireResponse instanceof PNPublishResult);
    }
}
