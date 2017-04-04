<?php

namespace Tests\Functional;

use PubNub\Endpoints\Presence\SetState;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class testSetState extends \PubNubTestCase
{
    /** @var  SetStateExposed */
    protected $setState;

    /** @var  string[]|string state */
    protected $state;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
        $this->setState = new SetStateExposed($this->pubnub);
        $this->setState->state([
            "name" => "Alex",
            "count" => 5
        ]);
    }

    public function testSetStateSingleChannel()
    {
        $this->setState->channels('ch')->state($this->state);

        $this->assertEquals(
            sprintf(
                SetState::PATH,
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                "ch",
                $this->pubnub->getConfiguration()->getUuid()
            ), $this->setState->buildPath());

        $params = $this->setState->buildParams();

        $this->assertEquals(
            PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            $params['pnsdk']);
        $this->assertEquals(
            $this->pubnub->getConfiguration()->getUuid(),
            $params['uuid']);
        $this->assertEquals(
            json_decode("%7B%22count%22%3A%205%2C%20%22name%22%3A%20%22Alex%22%7D"),
            json_decode($params['state']));

        $this->assertEquals(['ch'], $this->setState->getChannels());
    }

    public function testSetStateSingleGroups()
    {
        $this->setState->channelGroups('gr')->state($this->state);

        $this->assertEquals(
            sprintf(
                SetState::PATH,
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                ",",
                $this->pubnub->getConfiguration()->getUuid()
            ), $this->setState->buildPath());

        $params = $this->setState->buildParams();

        $this->assertEquals($params['pnsdk'], PubNubUtil::urlEncode(PubNub::getSdkFullName()));
        $this->assertEquals($params['uuid'], $this->pubnub->getConfiguration()->getUuid());
        $this->assertEquals($params['channel-group'], 'gr');
        $this->assertEquals(
            json_decode("%7B%22count%22%3A%205%2C%20%22name%22%3A%20%22Alex%22%7D"),
            json_decode($params['state']));
        $this->assertEquals(0, count($this->setState->getChannels()));
        $this->assertEquals(['gr'], $this->setState->getGroups());
    }
}

class SetStateExposed extends SetState
{
    public function buildParams()
    {
        return parent::buildParams();
    }

    public function buildPath()
    {
        return parent::buildPath();
    }

    /**
     * @return \string[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return \string[]
     */
    public function getGroups()
    {
        return $this->groups;
    }
}
