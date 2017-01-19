<?php

namespace Tests\Integrational;

use PubNub\Endpoints\PubSub\Publish;
use PubNub\Models\Consumer\PNPublishResult;
use PubNub\Models\ResponseHelpers\PNEnvelope;
use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\PubNubException;
use PubNub\PubNubUtil;
use ReflectionMethod;
use VCR\VCR;


class PublishTest extends \PubNubTestCase
{
    public static function setUpBeforeClass()
    {
//        static::setupVCR();
    }

    /**
     * @param Publish $publish
     */
    private function assertSuccess($publish)
    {
        $result = $publish->sync();

        $this->assertInstanceOf(PNPublishResult::class, $result);
        $this->assertGreaterThan(14847319130820201, $result->getTimetoken());

        $envelope = $publish->envelope();
        $this->assertInstanceOf(PNEnvelope::class, $envelope);
        $this->assertInstanceOf(PNPublishResult::class, $envelope->getResult());
        $this->assertGreaterThan(14847319130820201, $envelope->getResult()->getTimetoken());

        $this->assertEquals($result->getTimetoken(), $envelope->getResult()->getTimetoken());

        $publish->clear();

        $result2 = $publish->sync();
        $this->assertNotEquals($result->getTimetoken(), $result2->getTimetoken());
    }

    /**
     * @param PubNub $pubnub
     * @param $message
     */
    private function assertSuccessPublishGet($pubnub, $message)
    {
        $this->assertSuccess($pubnub->publish()->setChannel('blah')->setMessage($message));
    }

    /**
     * @vcr blah
     */
    public function testPublishMixedViaGet()
    {
        $this->assertSuccessPublishGet($this->pubnub, 'hi');
        $this->assertSuccessPublishGet($this->pubnub, 5);
        $this->assertSuccessPublishGet($this->pubnub, 3.14);
        $this->assertSuccessPublishGet($this->pubnub, false);
        $this->assertSuccessPublishGet($this->pubnub, ['hey', 'hey2', 'hey3']);
        $this->assertSuccessPublishGet($this->pubnub, ['hey' => 31, 'hey2' => true, 'hey3' =>['ok']]);
    }
}
