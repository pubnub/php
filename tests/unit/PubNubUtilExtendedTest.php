<?php

use PHPUnit\Framework\TestCase;
use PubNub\PubNubUtil;

class PubNubUtilExtendedTest extends TestCase
{
    // ============================================================================
    // buildUrl() TESTS
    // ============================================================================

    public function testBuildUrlWithBasicParams()
    {
        $basePath = 'https://ps.pndsn.com';
        $path = '/v2/subscribe/demo/ch1/0';
        $params = ['uuid' => 'test-user', 'pnsdk' => 'PubNub-PHP/8.0.0'];
        
        $url = PubNubUtil::buildUrl($basePath, $path, $params);
        
        $this->assertStringStartsWith($basePath . $path, $url);
        $this->assertStringContainsString('uuid=test-user', $url);
        $this->assertStringContainsString('pnsdk=PubNub-PHP/8.0.0', $url);
    }

    public function testBuildUrlWithEmptyParams()
    {
        $basePath = 'https://ps.pndsn.com';
        $path = '/v2/time/0';
        $params = [];
        
        $url = PubNubUtil::buildUrl($basePath, $path, $params);
        
        $this->assertEquals($basePath . $path . '?', $url);
    }

    public function testBuildUrlWithSpecialCharactersInParams()
    {
        $basePath = 'https://ps.pndsn.com';
        $path = '/publish';
        $params = ['message' => 'hello%20world'];
        
        $url = PubNubUtil::buildUrl($basePath, $path, $params);
        
        $this->assertStringContainsString('message=hello%20world', $url);
    }

    public function testBuildUrlWithMultipleParams()
    {
        $basePath = 'https://ps.pndsn.com';
        $path = '/v2/history';
        $params = [
            'channel' => 'test-channel',
            'count' => '100',
            'reverse' => 'false'
        ];
        
        $url = PubNubUtil::buildUrl($basePath, $path, $params);
        
        $this->assertStringContainsString('channel=test-channel', $url);
        $this->assertStringContainsString('count=100', $url);
        $this->assertStringContainsString('reverse=false', $url);
    }

    // ============================================================================
    // joinChannels() TESTS
    // ============================================================================

    public function testJoinChannelsWithSingleChannel()
    {
        $channels = ['channel1'];
        
        $result = PubNubUtil::joinChannels($channels);
        
        $this->assertEquals('channel1', $result);
    }

    public function testJoinChannelsWithMultipleChannels()
    {
        $channels = ['channel1', 'channel2', 'channel3'];
        
        $result = PubNubUtil::joinChannels($channels);
        
        $this->assertEquals('channel1,channel2,channel3', $result);
    }

    public function testJoinChannelsWithEmptyArray()
    {
        $channels = [];
        
        $result = PubNubUtil::joinChannels($channels);
        
        $this->assertEquals(',', $result);
    }

    public function testJoinChannelsWithSpecialCharacters()
    {
        $channels = ['channel-1', 'channel.2', 'channel_3'];
        
        $result = PubNubUtil::joinChannels($channels);
        
        $this->assertEquals('channel-1,channel.2,channel_3', $result);
    }

    public function testJoinChannelsEncodesSpecialCharacters()
    {
        $channels = ['channel with spaces', 'channel#special'];
        
        $result = PubNubUtil::joinChannels($channels);
        
        $this->assertStringContainsString('channel+with+spaces', $result);
        $this->assertStringContainsString('channel%23special', $result);
    }

    // ============================================================================
    // joinItems() TESTS
    // ============================================================================

    public function testJoinItemsWithSingleItem()
    {
        $items = ['item1'];
        
        $result = PubNubUtil::joinItems($items);
        
        $this->assertEquals('item1', $result);
    }

    public function testJoinItemsWithMultipleItems()
    {
        $items = ['item1', 'item2', 'item3'];
        
        $result = PubNubUtil::joinItems($items);
        
        $this->assertEquals('item1,item2,item3', $result);
    }

    public function testJoinItemsWithEmptyArray()
    {
        $items = [];
        
        $result = PubNubUtil::joinItems($items);
        
        $this->assertEquals('', $result);
    }

    public function testJoinItemsWithNumericItems()
    {
        $items = ['1', '2', '3'];
        
        $result = PubNubUtil::joinItems($items);
        
        $this->assertEquals('1,2,3', $result);
    }

    // ============================================================================
    // extendArray() TESTS - Already used in tests but testing edge cases
    // ============================================================================

    public function testExtendArrayWithArrays()
    {
        $existing = ['a', 'b'];
        $new = ['c', 'd'];
        
        $result = PubNubUtil::extendArray($existing, $new);
        
        $this->assertEquals(['a', 'b', 'c', 'd'], $result);
    }

    public function testExtendArrayWithString()
    {
        $existing = ['a', 'b'];
        $new = 'c,d';
        
        $result = PubNubUtil::extendArray($existing, $new);
        
        $this->assertEquals(['a', 'b', 'c', 'd'], $result);
    }

    public function testExtendArrayWithEmptyExisting()
    {
        $existing = [];
        $new = ['a', 'b'];
        
        $result = PubNubUtil::extendArray($existing, $new);
        
        $this->assertEquals(['a', 'b'], $result);
    }

    public function testExtendArrayWithEmptyNew()
    {
        $existing = ['a', 'b'];
        $new = [];
        
        $result = PubNubUtil::extendArray($existing, $new);
        
        $this->assertEquals(['a', 'b'], $result);
    }

    public function testExtendArrayWithEmptyString()
    {
        $existing = ['a', 'b'];
        $new = '';
        
        $result = PubNubUtil::extendArray($existing, $new);
        
        $this->assertEquals(['a', 'b'], $result);
    }

    // ============================================================================
    // splitItems() TESTS
    // ============================================================================

    public function testSplitItemsWithSingleItem()
    {
        $items = 'item1';
        
        $result = PubNubUtil::splitItems($items);
        
        $this->assertEquals(['item1'], $result);
    }

    public function testSplitItemsWithMultipleItems()
    {
        $items = 'item1,item2,item3';
        
        $result = PubNubUtil::splitItems($items);
        
        $this->assertEquals(['item1', 'item2', 'item3'], $result);
    }

    public function testSplitItemsWithEmptyString()
    {
        $items = '';
        
        $result = PubNubUtil::splitItems($items);
        
        $this->assertEquals([], $result);
    }

    public function testSplitItemsPreservesSpaces()
    {
        $items = 'item 1,item 2';
        
        $result = PubNubUtil::splitItems($items);
        
        $this->assertEquals(['item 1', 'item 2'], $result);
    }

    public function testSplitItemsWithTrailingComma()
    {
        $items = 'item1,item2,';
        
        $result = PubNubUtil::splitItems($items);
        
        $this->assertEquals(['item1', 'item2', ''], $result);
    }

    // ============================================================================
    // uuid() TESTS
    // ============================================================================

    public function testUuidReturnsString()
    {
        $uuid = PubNubUtil::uuid();
        
        $this->assertIsString($uuid);
    }

    public function testUuidHasCorrectFormat()
    {
        $uuid = PubNubUtil::uuid();
        
        // UUID format: XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX
        $this->assertMatchesRegularExpression(
            '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i',
            $uuid
        );
    }

    public function testUuidIsUnique()
    {
        $uuid1 = PubNubUtil::uuid();
        $uuid2 = PubNubUtil::uuid();
        
        $this->assertNotEquals($uuid1, $uuid2);
    }

    public function testUuidGeneratesMultipleUniqueValues()
    {
        $uuids = [];
        for ($i = 0; $i < 100; $i++) {
            $uuids[] = PubNubUtil::uuid();
        }
        
        // All UUIDs should be unique
        $this->assertEquals(100, count(array_unique($uuids)));
    }

    public function testUuidLength()
    {
        $uuid = PubNubUtil::uuid();
        
        // UUID with dashes is 36 characters
        $this->assertEquals(36, strlen($uuid));
    }

    // ============================================================================
    // fetchPamPermissionsFrom() TESTS
    // ============================================================================

    public function testFetchPamPermissionsFromWithAllPermissions()
    {
        $input = [
            'r' => 1,
            'w' => 1,
            'm' => 1,
            'd' => 1,
            'g' => 1,
            'u' => 1,
            'j' => 1,
            'ttl' => 1440
        ];
        
        $result = PubNubUtil::fetchPamPermissionsFrom($input);
        
        $this->assertEquals([true, true, true, true, true, true, true, 1440], $result);
    }

    public function testFetchPamPermissionsFromWithNoPermissions()
    {
        $input = [
            'r' => 0,
            'w' => 0,
            'm' => 0,
            'd' => 0,
            'g' => 0,
            'u' => 0,
            'j' => 0,
            'ttl' => 0
        ];
        
        $result = PubNubUtil::fetchPamPermissionsFrom($input);
        
        $this->assertEquals([false, false, false, false, false, false, false, 0], $result);
    }

    public function testFetchPamPermissionsFromWithPartialPermissions()
    {
        $input = [
            'r' => 1,
            'w' => 0,
            'm' => 1,
            'ttl' => 60
        ];
        
        $result = PubNubUtil::fetchPamPermissionsFrom($input);
        
        $this->assertEquals([true, false, true, null, null, null, null, 60], $result);
    }

    public function testFetchPamPermissionsFromWithEmptyInput()
    {
        $input = [];
        
        $result = PubNubUtil::fetchPamPermissionsFrom($input);
        
        $this->assertEquals([null, null, null, null, null, null, null, null], $result);
    }

    public function testFetchPamPermissionsFromWithOnlyTTL()
    {
        $input = ['ttl' => 120];
        
        $result = PubNubUtil::fetchPamPermissionsFrom($input);
        
        $this->assertEquals([null, null, null, null, null, null, null, 120], $result);
    }

    // ============================================================================
    // isAssoc() TESTS
    // ============================================================================

    public function testIsAssocWithIndexedArray()
    {
        $array = ['a', 'b', 'c'];
        
        $result = PubNubUtil::isAssoc($array);
        
        $this->assertFalse($result);
    }

    public function testIsAssocWithAssociativeArray()
    {
        $array = ['key1' => 'value1', 'key2' => 'value2'];
        
        $result = PubNubUtil::isAssoc($array);
        
        $this->assertTrue($result);
    }

    public function testIsAssocWithNumericKeys()
    {
        $array = [0 => 'a', 1 => 'b', 2 => 'c'];
        
        $result = PubNubUtil::isAssoc($array);
        
        $this->assertFalse($result);
    }

    public function testIsAssocWithMixedKeys()
    {
        $array = [0 => 'a', 'key' => 'b', 2 => 'c'];
        
        $result = PubNubUtil::isAssoc($array);
        
        $this->assertTrue($result);
    }

    public function testIsAssocWithEmptyArray()
    {
        $array = [];
        
        $result = PubNubUtil::isAssoc($array);
        
        // Empty array returns true because array_keys([]) !== range(0, count([]) - 1)
        // array_keys([]) = [], range(0, -1) = []
        // But the comparison returns true (not equal)
        $this->assertTrue($result);
    }

    public function testIsAssocWithNonArray()
    {
        $result = PubNubUtil::isAssoc('not an array');
        
        $this->assertFalse($result);
    }

    public function testIsAssocWithNonSequentialKeys()
    {
        $array = [1 => 'a', 3 => 'b', 5 => 'c'];
        
        $result = PubNubUtil::isAssoc($array);
        
        $this->assertTrue($result);
    }

    // ============================================================================
    // tokenEncode() TESTS
    // ============================================================================

    public function testTokenEncodeWithBasicString()
    {
        $token = 'mytoken123';
        
        $result = PubNubUtil::tokenEncode($token);
        
        $this->assertEquals('mytoken123', $result);
    }

    public function testTokenEncodeConvertsSpacesToPercent20()
    {
        $token = 'token with spaces';
        
        $result = PubNubUtil::tokenEncode($token);
        
        $this->assertStringContainsString('%20', $result);
        $this->assertStringNotContainsString('+', $result);
    }

    public function testTokenEncodeWithSpecialCharacters()
    {
        $token = 'token!@#$%';
        
        $result = PubNubUtil::tokenEncode($token);
        
        $this->assertIsString($result);
    }

    public function testTokenEncodeWithPlusSign()
    {
        $token = 'token+with+plus';
        
        $result = PubNubUtil::tokenEncode($token);
        
        // Plus signs are first URL encoded to %2B, then the str_replace doesn't affect them
        // because str_replace looks for literal '+' which is already encoded
        $this->assertStringContainsString('%2B', $result);
    }

    // ============================================================================
    // convertIso8859ToUtf8() TESTS
    // ============================================================================

    public function testConvertIso8859ToUtf8WithAscii()
    {
        $input = 'Hello World';
        
        $result = PubNubUtil::convertIso8859ToUtf8($input);
        
        $this->assertEquals('Hello World', $result);
    }

    public function testConvertIso8859ToUtf8WithExtendedCharacters()
    {
        // Test with some ISO-8859-1 characters
        $input = chr(0xA9); // Copyright symbol in ISO-8859-1
        
        $result = PubNubUtil::convertIso8859ToUtf8($input);
        
        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }

    public function testConvertIso8859ToUtf8WithEmptyString()
    {
        $input = '';
        
        $result = PubNubUtil::convertIso8859ToUtf8($input);
        
        $this->assertEquals('', $result);
    }

    public function testConvertIso8859ToUtf8PreservesAsciiCharacters()
    {
        $input = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        
        $result = PubNubUtil::convertIso8859ToUtf8($input);
        
        $this->assertEquals($input, $result);
    }

    public function testConvertIso8859ToUtf8WithNumbers()
    {
        $input = '1234567890';
        
        $result = PubNubUtil::convertIso8859ToUtf8($input);
        
        $this->assertEquals($input, $result);
    }

    // ============================================================================
    // INTEGRATION TESTS
    // ============================================================================

    public function testChannelWorkflow()
    {
        // Split channels from string
        $channelString = 'channel1,channel2,channel3';
        $channels = PubNubUtil::splitItems($channelString);
        
        $this->assertCount(3, $channels);
        
        // Join channels back
        $joined = PubNubUtil::joinChannels($channels);
        
        $this->assertEquals('channel1,channel2,channel3', $joined);
    }

    public function testArrayExtensionWorkflow()
    {
        $existing = ['channel1', 'channel2'];
        $newString = 'channel3,channel4';
        
        $extended = PubNubUtil::extendArray($existing, $newString);
        
        $this->assertCount(4, $extended);
        $this->assertEquals(['channel1', 'channel2', 'channel3', 'channel4'], $extended);
    }

    public function testUrlBuildingWorkflow()
    {
        $basePath = 'https://ps.pndsn.com';
        $path = '/v2/subscribe/demo/my-channel/0';
        $params = [
            'uuid' => 'user-123',
            'tt' => '0',
            'pnsdk' => 'PubNub-PHP/8.0.0'
        ];
        
        $url = PubNubUtil::buildUrl($basePath, $path, $params);
        
        $this->assertStringStartsWith('https://ps.pndsn.com', $url);
        $this->assertStringContainsString('uuid=user-123', $url);
        $this->assertStringContainsString('&', $url);
    }
}
