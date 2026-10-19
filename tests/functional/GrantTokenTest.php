<?php

namespace Tests\Functional;

use PubNub\PubNub;

class GrantTokenTest extends \PubNubTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->pubnub = new PubNub($this->config_pam);
    }

    public function testRequestToken(): void
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

    public function testParseToken(): void
    {
        $token = 'qEF2AkF0GmFtet9DdHRsGDxDcmVzpURjaGFuoWpteS1jaGFubmVsGENDZ3JwoEN1c3KgQ3NwY6BEdXVpZKBDcGF0pURjaGFuoENnc'
            . 'nCgQ3VzcqBDc3BjoER1dWlkoERtZXRhoER1dWlkZ215LXV1aWRDc2lnWCAvUKKYbfc0vvvEhYqepG7-_lN5jh_yaA6eo98nAHV8Ug==';

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

    public function testParseTokenDecodesBase64UrlCharacters(): void
    {
        // "-" and "_" fall inside the CBOR payload of this token, not just its signature.
        $token = 'qEF2AkF0GmFLd-NDdHRsGQWgQ3Jlc6VEY2hhbqFjY2gxGP9DZ3JwoWNjZzEY_0N1c3KgQ3NwY6BEdXVpZKFldXVpZDEY_0Nw'
            . 'YXSlRGNoYW6gQ2dycKBDdXNyoENzcGOgRHV1aWShYl4kAURtZXRho2VzY29yZRhkZWNvbG9yY3JlZGZhdXRob3JlcGFuZHVEdXVp'
            . 'ZGtteWF1dGh1dWlkMUNzaWdYIP2vlxHik0EPZwtgYxAW3-LsBaX_WgWdYvtAXpYbKll3';

        $parsedToken = $this->pubnub->parseToken($token);

        $this->assertSame(1632335843, $parsedToken->getTimestamp());
        $this->assertSame('myauthuuid1', $parsedToken->getUuid());
        $this->assertSame(255, $parsedToken->getChannelResource('ch1')->getRights());
        $this->assertSame(255, $parsedToken->getChannelGroupResource('cg1')->getRights());
        $this->assertSame(255, $parsedToken->getUuidResource('uuid1')->getRights());
    }
}
