<?php

use PubNub\Endpoints\Presence\SetState;
use PubNub\PubNub;

class testSetState extends PubNubTestCase
{
    protected $setState;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new PubNub($this->config);
        $this->setState = new ExposedSetState($this->pubnub);
    }
}

class ExposedSetState extends SetState
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
