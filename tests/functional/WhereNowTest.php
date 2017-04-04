<?php
namespace Tests\Functional;

use PubNub\Endpoints\Presence\WhereNow;
use PubNub\PubNub;
use PubNub\PubNubUtil;
use PubNubTestCase;


class WhereNowTest extends PubNubTestCase
{
    /** @var  ExposedWhereNow */
    protected $whereNow;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
        $this->whereNow = new ExposedWhereNow($this->pubnub);
    }

    public function testWhereNow()
    {
        $this->whereNow->uuid("person_uuid");

        $this->assertEquals(
            sprintf(WhereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(), "person_uuid"),
            $this->whereNow->buildPath());

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->whereNow->buildParams());
    }

    public function testWhereNowNoUuid()
    {
        $this->assertEquals(
            sprintf(WhereNow::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(),
                $this->pubnub->getConfiguration()->getUuid()),
            $this->whereNow->buildPath()
        );

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub->getConfiguration()->getUuid()
        ], $this->whereNow->buildParams());
    }
}

class ExposedWhereNow extends WhereNow
{
    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }
}