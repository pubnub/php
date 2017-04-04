<?php

namespace Tests\Functional;

use PubNub\Endpoints\Access\Audit;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class AuditTest extends \PubNubTestCase
{
    /** @var  AuditExposed */
    protected $audit;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub_pam = new PubNubStubbedAudit($this->config_pam);
        $this->audit = new AuditExposed($this->pubnub_pam);
    }

    public function testAuditChannel()
    {
        $this->audit->channels('ch');

        $this->assertEquals(sprintf(Audit::PATH, $this->config_pam->getSubscribeKey()), $this->audit->buildPath());

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
            'timestamp' => '123',
            'channel' => 'ch',
            'signature' => PubNubUtil::signSha256(
                $this->config_pam->getSecretKey(),
                $this->config_pam->getSubscribeKey() . "\n" . $this->config_pam->getPublishKey() . "\n" .
                "audit\n" . PubNubUtil::preparePamParams([
                    "timestamp" => "123",
                    "channel" => "ch",
                    "pnsdk" => PubNub::getSdkFullName(),
                    "uuid" => $this->pubnub_pam->getConfiguration()->getUuid()])
            )
        ], $this->audit->buildParams());
    }

    public function testAuditChannelGroup()
    {
        $this->audit->channelGroups(['gr1', 'gr2']);

        $this->assertEquals(sprintf(Audit::PATH, $this->config_pam->getSubscribeKey()), $this->audit->buildPath());

        $this->assertEquals([
            'pnsdk' => PubNubUtil::urlEncode(PubNub::getSdkFullName()),
            'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
            'timestamp' => '123',
            'channel-group' => 'gr1,gr2',
            'signature' => PubNubUtil::signSha256(
                $this->config_pam->getSecretKey(),
                $this->config_pam->getSubscribeKey() . "\n" . $this->config_pam->getPublishKey() . "\n" .
                "audit\n" . PubNubUtil::preparePamParams([
                    "timestamp" => "123",
                    "channel-group" => "gr1,gr2",
                    "pnsdk" => PubNub::getSdkFullName(),
                    "uuid" => $this->pubnub_pam->getConfiguration()->getUuid()])
            )
        ], $this->audit->buildParams());
    }
}

class PubNubStubbedAudit extends PubNub
{
    public function timestamp()
    {
        return 123;
    }
}

class AuditExposed extends Audit
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
