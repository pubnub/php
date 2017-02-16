<?php

namespace Tests\Functional;

use PubNub\Endpoints\Access\Grant;
use PubNub\Exceptions\PubNubValidationException;


class GrantTest extends \PubNubTestCase
{
    /** @var  GrantExposed */
    protected $grant;

    public function setUp()
    {
        $this->grant = new GrantExposed($this->pubnub_pam);
    }

    public function testValidatesFlags()
    {
        try {
            $this->grant->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException $exception) {
            $this->assertEquals("Secret key is not configured", $exception->getMessage());
        }
    }

    public function testReadAndWriteToChannel()
    {

    }
}


class GrantExposed extends Grant
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
