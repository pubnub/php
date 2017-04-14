<?php

namespace Tests\Functional;

use PubNub\Endpoints\Access\Grant;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class GrantTest extends \PubNubTestCase
{
    /** @var  GrantExposed */
    protected $grant;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub_pam = new PubNubStubbed($this->config_pam);
        $this->grant = new GrantExposed($this->pubnub_pam);
    }

    public function testValidatesFlags()
    {
        $grant = new GrantExposed($this->pubnub);

        try {
            $grant->sync();
            $this->fail("No exception was thrown");
        } catch (PubNubValidationException $exception) {
            $this->assertEquals("Secret key not configured", $exception->getMessage());
        }
    }

    public function testReadAndWriteToChannel()
    {
        $this->grant->channels('ch')->read(true)->write(true)->ttl(7);

        $this->assertEquals(
                sprintf(Grant::PATH, $this->config_pam->getSubscribeKey()),
                $this->grant->buildPath()
        );

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
            'r' => '1',
            'w' => '1',
            'ttl' => '7',
            'timestamp' => '123',
            'channel' => 'ch',
            'signature' => PubNubUtil::signSha256(
                $this->config_pam->getSecretKey(),
                $this->config_pam->getSubscribeKey() . "\n" . $this->config_pam->getPublishKey() . "\n" .
                "grant\n" . PubNubUtil::preparePamParams([
                    "r" => "1",
                    "w" => "1",
                    "ttl" => "7",
                    "timestamp" => "123",
                    "channel" => "ch",
                    "pnsdk" => PubNub::getSdkFullName(),
                    "uuid" => $this->pubnub_pam->getConfiguration()->getUuid()])
                )
        ], $this->grant->buildParams());
    }

    public function testReadAndWriteToChannelGroup()
    {
        $this->grant->channelGroups(['gr1', 'gr2'])
            ->read(true)
            ->write(false);

        $this->assertEquals(
            sprintf(Grant::PATH, $this->config_pam->getSubscribeKey()),
            $this->grant->buildPath()
        );

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
            'r' => '1',
            'w' => '0',
            'timestamp' => '123',
            'channel-group' => 'gr1,gr2',
            'signature' => PubNubUtil::signSha256(
                $this->config_pam->getSecretKey(),
                $this->config_pam->getSubscribeKey() . "\n" . $this->config_pam->getPublishKey() . "\n" .
                "grant\n" . PubNubUtil::preparePamParams([
                    "r" => "1",
                    "w" => "0",
                    "timestamp" => "123",
                    "channel-group" => "gr1,gr2",
                    "pnsdk" => PubNub::getSdkFullName(),
                    "uuid" => $this->pubnub_pam->getConfiguration()->getUuid()])
            )
        ], $this->grant->buildParams());
    }
}

class PubNubStubbed extends PubNub
{
    public function timestamp()
    {
        return 123;
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
