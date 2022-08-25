<?php

namespace Tests\Functional;

use PubNub\Endpoints\Access\Audit;
use PubNub\Enums\PNHttpMethod;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class AuditTest extends \PubNubTestCase
{
    /** @var  AuditExposed */
    protected $audit;

    public function setUp(): void
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
            'signature' => $this->fakeSignature(
                [
                    'pnsdk' => PubNub::getSdkFullName(),
                    'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
                    'timestamp' => '123',
                    'channel' => 'ch',
                ],
                PNHttpMethod::GET,
                '123',
                $this->pubnub_pam->getConfiguration()->getPublishKey(),
                $this->audit->buildPath(),
                $this->config_pam->getSecretKey()
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
            'channel-group' => PubNubUtil::urlEncode('gr1,gr2'),
            'signature' => $this->fakeSignature(
                [
                    'pnsdk' => PubNub::getSdkFullName(),
                    'uuid' => $this->pubnub_pam->getConfiguration()->getUuid(),
                    'timestamp' => '123',
                    'channel-group' => 'gr1,gr2',
                ],
                PNHttpMethod::GET,
                '123',
                $this->pubnub_pam->getConfiguration()->getPublishKey(),
                $this->audit->buildPath(),
                $this->config_pam->getSecretKey()
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
