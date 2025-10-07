<?php

use PHPUnit\Framework\TestCase;
use PubNub\PubNubCborDecode;

class PubNubCborDecodeTest extends TestCase
{
    // ============================================================================
    // UNSIGNED INTEGER TESTS
    // ============================================================================

    public function testDecodeUnsignedIntegerSmall()
    {
        // Test small integers (0-23) which are encoded directly in the additional byte
        $this->assertEquals(0, PubNubCborDecode::decode('00'));
        $this->assertEquals(1, PubNubCborDecode::decode('01'));
        $this->assertEquals(10, PubNubCborDecode::decode('0A'));
        $this->assertEquals(23, PubNubCborDecode::decode('17'));
    }

    public function testDecodeUnsignedInteger1Byte()
    {
        // Test 1-byte integers (24-255)
        $this->assertEquals(24, PubNubCborDecode::decode('1818'));
        $this->assertEquals(100, PubNubCborDecode::decode('1864'));
        $this->assertEquals(255, PubNubCborDecode::decode('18FF'));
    }

    public function testDecodeUnsignedInteger2Bytes()
    {
        // Test 2-byte integers (256-65535)
        $this->assertEquals(256, PubNubCborDecode::decode('190100'));
        $this->assertEquals(1000, PubNubCborDecode::decode('1903E8'));
        $this->assertEquals(65535, PubNubCborDecode::decode('19FFFF'));
    }

    public function testDecodeUnsignedInteger4Bytes()
    {
        // Test 4-byte integers
        $this->assertEquals(100000, PubNubCborDecode::decode('1A000186A0'));
        $this->assertEquals(1000000, PubNubCborDecode::decode('1A000F4240'));
    }

    public function testDecodeUnsignedInteger8Bytes()
    {
        // Test 8-byte integers
        $this->assertEquals(1000000000, PubNubCborDecode::decode('1B000000003B9ACA00'));
    }

    // ============================================================================
    // NEGATIVE INTEGER TESTS
    // ============================================================================

    public function testDecodeNegativeIntegerSmall()
    {
        // Negative integers: -1 to -24 encoded as 0x20-0x37
        $this->assertEquals(-1, PubNubCborDecode::decode('20'));
        $this->assertEquals(-10, PubNubCborDecode::decode('29'));
        $this->assertEquals(-24, PubNubCborDecode::decode('37'));
    }

    public function testDecodeNegativeInteger1Byte()
    {
        // -25 to -256
        $this->assertEquals(-25, PubNubCborDecode::decode('3818'));
        $this->assertEquals(-100, PubNubCborDecode::decode('3863'));
        $this->assertEquals(-256, PubNubCborDecode::decode('38FF'));
    }

    public function testDecodeNegativeInteger2Bytes()
    {
        // -257 to -65536
        $this->assertEquals(-1000, PubNubCborDecode::decode('3903E7'));
    }

    // ============================================================================
    // BYTE STRING TESTS
    // ============================================================================

    public function testDecodeByteStringEmpty()
    {
        $this->assertEquals('', PubNubCborDecode::decode('40'));
    }

    public function testDecodeByteStringSmall()
    {
        // Byte string "hello" (68656C6C6F in hex)
        $this->assertEquals('hello', PubNubCborDecode::decode('4568656C6C6F'));
    }

    public function testDecodeByteString1ByteLength()
    {
        // Byte string with 1-byte length indicator
        $decoded = PubNubCborDecode::decode('5818' . bin2hex(str_repeat('a', 24)));
        $this->assertEquals(str_repeat('a', 24), $decoded);
    }

    // ============================================================================
    // TEXT STRING TESTS
    // ============================================================================

    public function testDecodeTextStringEmpty()
    {
        $this->assertEquals('', PubNubCborDecode::decode('60'));
    }

    public function testDecodeTextStringSmall()
    {
        // Text string "IETF"
        $this->assertEquals('IETF', PubNubCborDecode::decode('6449455446'));
    }

    public function testDecodeTextStringUnicode()
    {
        // Text string with unicode
        $this->assertEquals('hello', PubNubCborDecode::decode('6568656C6C6F'));
    }

    public function testDecodeTextString1ByteLength()
    {
        // Text string with 1-byte length
        $text = str_repeat('x', 24);
        $decoded = PubNubCborDecode::decode('7818' . bin2hex($text));
        $this->assertEquals($text, $decoded);
    }

    // ============================================================================
    // ARRAY TESTS
    // ============================================================================

    public function testDecodeArrayEmpty()
    {
        $this->assertEquals([], PubNubCborDecode::decode('80'));
    }

    public function testDecodeArrayWithIntegers()
    {
        // Array [1, 2, 3]
        $this->assertEquals([1, 2, 3], PubNubCborDecode::decode('83010203'));
    }

    public function testDecodeArrayWithMixedTypes()
    {
        // Array [1, "hello"]
        $result = PubNubCborDecode::decode('82016568656C6C6F');
        $this->assertEquals([1, 'hello'], $result);
    }

    public function testDecodeArrayNested()
    {
        // Array [[1, 2], [3, 4]]
        $result = PubNubCborDecode::decode('828201028203 04');
        $this->assertEquals([[1, 2], [3, 4]], $result);
    }

    public function testDecodeArrayWithNegativeNumbers()
    {
        // Array [-1, -2, -3]
        $this->assertEquals([-1, -2, -3], PubNubCborDecode::decode('83202122'));
    }

    // ============================================================================
    // HASHMAP (OBJECT) TESTS
    // ============================================================================

    public function testDecodeHashmapEmpty()
    {
        $this->assertEquals([], PubNubCborDecode::decode('A0'));
    }

    public function testDecodeHashmapWithStrings()
    {
        // Map {"a": "A"}
        $result = PubNubCborDecode::decode('A161616141');
        $this->assertEquals(['a' => 'A'], $result);
    }

    public function testDecodeHashmapWithNumbers()
    {
        // Map {"a": 1, "b": 2}
        $result = PubNubCborDecode::decode('A26161016162 02');
        $this->assertEquals(['a' => 1, 'b' => 2], $result);
    }

    public function testDecodeHashmapNested()
    {
        // Map {"a": {"b": "c"}}
        $result = PubNubCborDecode::decode('A16161A161626163');
        $this->assertEquals(['a' => ['b' => 'c']], $result);
    }

    public function testDecodeHashmapWithArray()
    {
        // Map {"array": [1, 2, 3]}
    $result = PubNubCborDecode::decode('A16561727261798301 0203');
        $this->assertEquals(['array' => [1, 2, 3]], $result);
    }

    // ============================================================================
    // SIMPLE VALUES TESTS
    // ============================================================================

    public function testDecodeSimpleValueFalse()
    {
        $this->assertFalse(PubNubCborDecode::decode('F4'));
    }

    public function testDecodeSimpleValueTrue()
    {
        $this->assertTrue(PubNubCborDecode::decode('F5'));
    }

    public function testDecodeSimpleValueNull()
    {
        $this->assertNull(PubNubCborDecode::decode('F6'));
    }

    public function testDecodeSimpleValueUndefined()
    {
        // Undefined maps to null in PHP
        $this->assertNull(PubNubCborDecode::decode('F7'));
    }

    // ============================================================================
    // FLOAT TESTS
    // ============================================================================

    public function testDecodeFloat16Bit()
    {
        // Test 16-bit float (half-precision)
        // 1.0 in half precision: 0x3C00
        $result = PubNubCborDecode::decode('F93C00');
        $this->assertEquals(1.0, $result);
    }

    public function testDecodeFloat32Bit()
    {
        // Test 32-bit float (single precision)
        // 3.14159 approximately in single precision
        $result = PubNubCborDecode::decode('FA40490FDA');
        $this->assertEqualsWithDelta(3.14159, $result, 0.00001);
    }

    public function testDecodeFloat64Bit()
    {
        // Test 64-bit float (double precision)
        // 1.1 in double precision
        $result = PubNubCborDecode::decode('FB3FF199999999999A');
        $this->assertEqualsWithDelta(1.1, $result, 0.0000001);
    }

    public function testDecodeFloatZero()
    {
        // 0.0 in half precision: 0x0000
        $result = PubNubCborDecode::decode('F90000');
        $this->assertEquals(0.0, $result);
    }

    public function testDecodeFloatNegative()
    {
        // -1.0 in half precision: 0xBC00
        $result = PubNubCborDecode::decode('F9BC00');
        $this->assertEquals(-1.0, $result);
    }

    public function testDecodeFloatInfinity()
    {
        // Positive infinity in half precision: 0x7C00
        $result = PubNubCborDecode::decode('F97C00');
        $this->assertEquals(INF, $result);
    }

    // ============================================================================
    // COMPLEX DATA STRUCTURE TESTS
    // ============================================================================

    public function testDecodeComplexNestedStructure()
    {
        // Complex structure: {"users": [{"name": "Alice", "age": 30}]}
        $cbor = 'A1' . // Map with 1 entry
                '657573657273' . // key: "users"
                '81' . // Array with 1 element
                'A2' . // Map with 2 entries
                '646E616D65' . // key: "name"
                '65416C696365' . // value: "Alice"
                '63616765' . // key: "age"
                '181E'; // value: 30
        
        $result = PubNubCborDecode::decode($cbor);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('users', $result);
        $this->assertIsArray($result['users']);
        $this->assertEquals('Alice', $result['users'][0]['name']);
        $this->assertEquals(30, $result['users'][0]['age']);
    }

    public function testDecodeArrayOfMixedPrimitives()
    {
        // Array with: integer, string, boolean, null
        // [42, "test", true, null]
        $cbor = '84' . // Array with 4 elements
                '182A' . // 42
                '6474657374' . // "test"
                'F5' . // true
                'F6'; // null
        
        $result = PubNubCborDecode::decode($cbor);
        
        $this->assertEquals([42, 'test', true, null], $result);
    }

    // ============================================================================
    // INPUT SANITIZATION TESTS
    // ============================================================================

    public function testDecodeWithSpaces()
    {
        // Should handle spaces in input
        $this->assertEquals(42, PubNubCborDecode::decode('18 2A'));
        $this->assertEquals([1, 2], PubNubCborDecode::decode('82 01 02'));
    }

    public function testDecodeWithLowerCase()
    {
        // Should handle lowercase hex
        $this->assertEquals(255, PubNubCborDecode::decode('18ff'));
        $this->assertEquals('hello', PubNubCborDecode::decode('6568656c6c6f'));
    }

    public function testDecodeWithMixedCase()
    {
        // Should handle mixed case hex
        $this->assertEquals(1000, PubNubCborDecode::decode('1903E8'));
        $this->assertEquals('Test', PubNubCborDecode::decode('6454657374'));
    }

    // ============================================================================
    // ERROR HANDLING TESTS
    // ============================================================================

    public function testDecodeInvalidHexCharacters()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Input');
        
        PubNubCborDecode::decode('GG');
    }

    public function testDecodeInvalidHexWithSpecialChars()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Input');
        
        PubNubCborDecode::decode('18@A');
    }

    public function testDecodeInvalidHexWithNonHexLetters()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Input');
        
        PubNubCborDecode::decode('18ZZ');
    }

    // ============================================================================
    // EDGE CASES
    // ============================================================================

    public function testDecodeEmptyArray()
    {
        $result = PubNubCborDecode::decode('80');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testDecodeEmptyMap()
    {
        $result = PubNubCborDecode::decode('A0');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testDecodeEmptyString()
    {
        $this->assertEquals('', PubNubCborDecode::decode('60'));
    }

    public function testDecodeLargeInteger()
    {
        // Test maximum values for different byte sizes
        $this->assertEquals(4294967295, PubNubCborDecode::decode('1AFFFFFFFF'));
    }

    public function testDecodeArrayWith1ByteLength()
    {
        // Array with length specified in 1 byte (25+ elements)
        $cbor = '9818'; // Array with length in next byte: 24 elements
        for ($i = 0; $i < 24; $i++) {
            $cbor .= '0' . dechex($i % 16); // Add elements 0-15 repeated
        }
        
        $result = PubNubCborDecode::decode($cbor);
        $this->assertCount(24, $result);
    }

    public function testDecodeMapWith1ByteLength()
    {
        // Map with length specified in 1 byte
        $cbor = 'B818'; // Map with length in next byte: 24 entries
        for ($i = 0; $i < 24; $i++) {
            $key = chr(65 + $i); // A, B, C, ...
            $cbor .= '61' . bin2hex($key); // key
            $cbor .= '0' . dechex($i % 16); // value
        }
        
        $result = PubNubCborDecode::decode($cbor);
        $this->assertCount(24, $result);
    }

    public function testDecodeMultipleNesting()
    {
        // Deep nesting: [[[1]]]
        $result = PubNubCborDecode::decode('818181 01');
        $this->assertEquals([[[1]]], $result);
    }

    public function testDecodeBooleanArray()
    {
        // Array of booleans: [true, false, true]
        $result = PubNubCborDecode::decode('83F5F4F5');
        $this->assertEquals([true, false, true], $result);
    }

    public function testDecodeNullArray()
    {
        // Array with nulls: [null, null]
        $result = PubNubCborDecode::decode('82F6F6');
        $this->assertEquals([null, null], $result);
    }

    public function testDecodeStringWithSpecialCharacters()
    {
        // String with special chars like newline, tab
        $specialString = "test\nwith\ttabs";
        $hex = bin2hex($specialString);
        $length = strlen($specialString);
        $cbor = '6' . dechex($length) . $hex;
        
        $result = PubNubCborDecode::decode($cbor);
        $this->assertEquals($specialString, $result);
    }
}
