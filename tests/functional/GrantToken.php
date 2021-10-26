<?php

namespace Tests\Functional;

use PubNub\PubNub;

class GrantTokenTest extends \PubNubTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->pubnub = new PubNub($this->config_pam);
    }

    public function testRequestToken()
    {
        $token = $this->pubnub->grantToken()
            ->ttl(60)
            ->authorizedUuid('my-uuid')
            ->addChannelResources([
                'my-channel' => ['read' => true, 'write' => true, 'update' => true],
            ])
            ->sync();
        $this->assertNotNull($token, 'Token should be a string');
    }

    public function testParseToken()
    {
        $token = 'qEF2AkF0GmFtet9DdHRsGDxDcmVzpURjaGFuoWpteS1jaGFubmVsGENDZ3JwoEN1c3KgQ3NwY6BEdXVpZKBDcGF0pURjaGFuoENnc'
            . 'nCgQ3VzcqBDc3BjoER1dWlkoERtZXRhoER1dWlkZ215LXV1aWRDc2lnWCAvUKKYbfc0vvvEhYqepG7-_lN5jh_yaA6eo98nAHV8Ug==';

        /** @var PubNub\Models\Consumer\AccessManager\PNAccessManagerTokenResult */
        $parsedToken = $this->pubnub->parseToken($token);

        $this->assertEquals(60, $parsedToken->getTtl());
        $this->assertEquals('my-uuid', $parsedToken->getUuid());
        $this->assertEquals(true, $parsedToken->getChannelResource('my-channel')->hasRead());
        $this->assertEquals(true, $parsedToken->getChannelResource('my-channel')->hasWrite());
        $this->assertEquals(false, $parsedToken->getChannelResource('my-channel')->hasManage());
        $this->assertEquals(false, $parsedToken->getChannelResource('my-channel')->hasDelete());
        $this->assertEquals(false, $parsedToken->getChannelResource('my-channel')->hasCreate());
        $this->assertEquals(false, $parsedToken->getChannelResource('my-channel')->hasGet());
        $this->assertEquals(true, $parsedToken->getChannelResource('my-channel')->hasUpdate());
        $this->assertEquals(false, $parsedToken->getChannelResource('my-channel')->hasJoin());
    }
}
