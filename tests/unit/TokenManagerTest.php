<?php

namespace PubNubTests\unit;

use PHPUnit\Framework\TestCase;

use PubNub\Managers\TokenManager;

class TokenManagerTest extends TestCase
{
    public function testSetAndGetToken(): void
    {
        $manager = new TokenManager();
        $token = 'test-token-abc123';

        $manager->setToken($token);

        $this->assertEquals($token, $manager->getToken());
    }

    public function testGetTokenReturnsNullByDefault(): void
    {
        $manager = new TokenManager();

        $this->assertNull($manager->getToken());
    }

    public function testSetTokenOverwritesPreviousToken(): void
    {
        $manager = new TokenManager();

        $manager->setToken('first-token');
        $this->assertEquals('first-token', $manager->getToken());

        $manager->setToken('second-token');
        $this->assertEquals('second-token', $manager->getToken());
    }

    public function testSetTokenWithEmptyString(): void
    {
        $manager = new TokenManager();

        $manager->setToken('');

        $this->assertEquals('', $manager->getToken());
    }

    public function testSetTokenWithLongString(): void
    {
        $manager = new TokenManager();
        $longToken = str_repeat('a', 10000);

        $manager->setToken($longToken);

        $this->assertEquals($longToken, $manager->getToken());
    }

    public function testSetTokenWithSpecialCharacters(): void
    {
        $manager = new TokenManager();
        $specialToken = 'token-with-special!@#$%^&*()_+-={}[]|\\:";\'<>?,./';

        $manager->setToken($specialToken);

        $this->assertEquals($specialToken, $manager->getToken());
    }
}
