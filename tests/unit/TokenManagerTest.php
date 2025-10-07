<?php

use PHPUnit\Framework\TestCase;
use PubNub\Managers\TokenManager;

class TokenManagerTest extends TestCase
{
    public function testSetAndGetToken()
    {
        $manager = new TokenManager();
        $token = 'test-token-abc123';
        
        $manager->setToken($token);
        
        $this->assertEquals($token, $manager->getToken());
    }

    public function testGetTokenReturnsNullByDefault()
    {
        $manager = new TokenManager();
        
        $this->assertNull($manager->getToken());
    }

    public function testSetTokenOverwritesPreviousToken()
    {
        $manager = new TokenManager();
        
        $manager->setToken('first-token');
        $this->assertEquals('first-token', $manager->getToken());
        
        $manager->setToken('second-token');
        $this->assertEquals('second-token', $manager->getToken());
    }

    public function testSetTokenWithEmptyString()
    {
        $manager = new TokenManager();
        
        $manager->setToken('');
        
        $this->assertEquals('', $manager->getToken());
    }

    public function testSetTokenWithLongString()
    {
        $manager = new TokenManager();
        $longToken = str_repeat('a', 10000);
        
        $manager->setToken($longToken);
        
        $this->assertEquals($longToken, $manager->getToken());
    }

    public function testSetTokenWithSpecialCharacters()
    {
        $manager = new TokenManager();
        $specialToken = 'token-with-special!@#$%^&*()_+-={}[]|\\:";\'<>?,./';
        
        $manager->setToken($specialToken);
        
        $this->assertEquals($specialToken, $manager->getToken());
    }
}
