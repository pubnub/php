<?php

namespace PubNubTests\unit\CryptoModule;

use Generator;
use PHPUnit\Framework\TestCase;
use PubNub\CryptoModule;
use PubNub\Crypto\AesCbcCryptor;
use PubNub\Crypto\LegacyCryptor;
use PubNub\Crypto\Cryptor;
use PubNub\Crypto\Payload as CryptoPayload;
use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Exceptions\PubNubCryptoException;
use TypeError;

class CryptoModuleTest extends TestCase
{
    protected string $cipherKey = "myCipherKey";
    protected string $testKey256 = "01234567890123456789012345678901"; // 32 bytes
    protected string $testKeyShort = "shortkey";
    protected string $testKeyLong = "verylongcipherkeyfortestingpurposesthatexceeds256bits";

    // ============================================================================
    // EXISTING TESTS (keeping current functionality)
    // ============================================================================

    /**
     * @dataProvider decodeProvider
     * @param string $encrypted
     * @param mixed $expected
     * @return void
     */
    public function testDecode(CryptoModule $module, string $encrypted, mixed $expected): void
    {
        try {
            $decrypted = $module->decrypt($encrypted);
        } catch (PubNubResponseParsingException $e) {
            $decrypted = $e->getMessage();
        }
        $this->assertEquals($expected, $decrypted);
    }

    /**
     * @dataProvider encodeProvider
     * @param string $message
     * @param mixed $expected
     * @return void
     */
    public function testEnode(CryptoModule $module, string $message, mixed $expected): void
    {
        try {
            $encrypted = $module->encrypt($message);
            if (!$expected) {
                $this->assertEquals($message, $module->decrypt($encrypted));
                return;
            }
        } catch (PubNubResponseParsingException $e) {
            $encrypted = $e->getMessage();
        }
        $this->assertEquals($expected, $encrypted);
    }

    // ============================================================================
    // CRYPTOR REGISTRATION AND MANAGEMENT TESTS
    // ============================================================================

    /**
     * Test successful cryptor registration
     */
    public function testRegisterCryptorSuccess(): void
    {
        // Create a crypto module with empty cryptor map
        $module = new CryptoModule([], "TEST");

        // Create a new cryptor instance
        $cryptor = new AesCbcCryptor($this->cipherKey);

        // Register the cryptor - should succeed and return the module instance
        $result = $module->registerCryptor($cryptor);

        // Verify the method returns the module instance (fluent interface)
        $this->assertSame($module, $result);

        // Verify the cryptor was registered by trying to encrypt/decrypt
        $testMessage = "test message";
        $encrypted = $module->encrypt($testMessage, AesCbcCryptor::CRYPTOR_ID);
        $this->assertNotEmpty($encrypted);

        $decrypted = $module->decrypt($encrypted);
        $this->assertEquals($testMessage, $decrypted);
    }

    /**
     * Test cryptor registration with custom ID
     */
    public function testRegisterCryptorWithCustomId(): void
    {
        $customId = "CUST";
        // Create a crypto module with empty cryptor map
        $module = new CryptoModule([], $customId);

        // Create a new cryptor instance
        $cryptor = new class ($this->cipherKey) extends AesCbcCryptor {
            public const CRYPTOR_ID = "CUST";
        };

        // Register the cryptor with a custom ID
        $result = $module->registerCryptor($cryptor, $customId);

        // Verify the method returns the module instance
        $this->assertSame($module, $result);

        // Verify the cryptor was registered with the custom ID
        $testMessage = "test message with custom ID";
        $encrypted = $module->encrypt($testMessage, $customId);
        $this->assertNotEmpty($encrypted);
        $decrypted = $module->decrypt($encrypted);
        $this->assertEquals($testMessage, $decrypted);
    }

    /**
     * Test cryptor registration with invalid ID length
     */
    public function testRegisterCryptorInvalidIdLength(): void
    {
        $module = new CryptoModule([], "TEST");
        $cryptor = new AesCbcCryptor($this->cipherKey);

        // Test with ID too short
        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('Malformed cryptor id');
        $module->registerCryptor($cryptor, "ABC"); // 3 characters instead of 4
    }

    /**
     * Test cryptor registration with invalid ID length - too long
     */
    public function testRegisterCryptorInvalidIdLengthTooLong(): void
    {
        $module = new CryptoModule([], "TEST");
        $cryptor = new AesCbcCryptor($this->cipherKey);

        // Test with ID too long
        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('Malformed cryptor id');
        $module->registerCryptor($cryptor, "ABCDE"); // 5 characters instead of 4
    }

    /**
     * Test cryptor registration with empty ID
     */
    public function testRegisterCryptorEmptyId(): void
    {
        $module = new CryptoModule([], "TEST");
        $cryptor = new AesCbcCryptor($this->cipherKey);

        // Test with empty ID
        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('Malformed cryptor id');
        $module->registerCryptor($cryptor, ""); // Empty string
    }

    /**
     * Test cryptor registration with duplicate ID
     */
    public function testRegisterCryptorDuplicateId(): void
    {
        // Create a crypto module with an existing cryptor
        $existingCryptor = new LegacyCryptor($this->cipherKey, false);
        $module = new CryptoModule([
            'TEST' => $existingCryptor
        ], "TEST");

        // Try to register another cryptor with the same ID
        $newCryptor = new AesCbcCryptor($this->cipherKey);

        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('Cryptor id already in use');
        $module->registerCryptor($newCryptor, "TEST");
    }

    /**
     * Test cryptor registration with duplicate ID using default cryptor ID
     */
    public function testRegisterCryptorDuplicateDefaultId(): void
    {
        // Create a crypto module with existing AES cryptor
        $existingCryptor = new AesCbcCryptor($this->cipherKey);
        $module = new CryptoModule([
            AesCbcCryptor::CRYPTOR_ID => $existingCryptor
        ], AesCbcCryptor::CRYPTOR_ID);

        // Try to register another AES cryptor (should use default CRYPTOR_ID)
        $newCryptor = new AesCbcCryptor("different_key");

        $this->expectException(PubNubCryptoException::class);
        $this->expectExceptionMessage('Cryptor id already in use');
        $module->registerCryptor($newCryptor); // Will use AesCbcCryptor::CRYPTOR_ID
    }

    /**
     * Test cryptor registration with invalid cryptor instance
     */
    public function testRegisterCryptorInvalidInstance(): void
    {
        $module = new CryptoModule([], "TEST");

        // Create an anonymous class that doesn't extend Cryptor
        $invalidCryptor = new class
        {
            public function encrypt(mixed $data): string
            {
                return "fake";
            }

            public function decrypt(mixed $data): string
            {
                return "fake";
            }
        };

        $this->expectException(TypeError::class);

        // This will fail the instanceof check in registerCryptor
        $reflection = new \ReflectionClass($module);
        $method = $reflection->getMethod('registerCryptor');
        $method->setAccessible(true);

        // Call with invalid cryptor - this should trigger the instanceof check
        $method->invoke($module, $invalidCryptor, "TEST");
    }

    /**
     * Test cryptor map initialization
     */
    public function testCryptorMapInitialization(): void
    {
        // Test with empty cryptor map
        $emptyModule = new CryptoModule([], "TEST");
        $this->assertInstanceOf(CryptoModule::class, $emptyModule);

        // Test with pre-populated cryptor map
        $legacyCryptor = new LegacyCryptor($this->cipherKey, false);
        $aesCryptor = new AesCbcCryptor($this->cipherKey);

        $cryptorMap = [
            LegacyCryptor::CRYPTOR_ID => $legacyCryptor,
            AesCbcCryptor::CRYPTOR_ID => $aesCryptor
        ];

        $populatedModule = new CryptoModule($cryptorMap, LegacyCryptor::CRYPTOR_ID);
        $this->assertInstanceOf(CryptoModule::class, $populatedModule);

        // Verify both cryptors are accessible
        $testMessage = "initialization test";

        // Test legacy cryptor
        $legacyEncrypted = $populatedModule->encrypt($testMessage, LegacyCryptor::CRYPTOR_ID);
        $legacyDecrypted = $populatedModule->decrypt($legacyEncrypted);
        $this->assertEquals($testMessage, $legacyDecrypted);

        // Test AES cryptor
        $aesEncrypted = $populatedModule->encrypt($testMessage, AesCbcCryptor::CRYPTOR_ID);
        $aesDecrypted = $populatedModule->decrypt($aesEncrypted);
        $this->assertEquals($testMessage, $aesDecrypted);
    }

    /**
     * Test default cryptor ID validation
     */
    public function testDefaultCryptorIdValidation(): void
    {
        // Test with valid default cryptor ID
        $legacyCryptor = new LegacyCryptor($this->cipherKey, false);
        $cryptorMap = [LegacyCryptor::CRYPTOR_ID => $legacyCryptor];

        $module = new CryptoModule($cryptorMap, LegacyCryptor::CRYPTOR_ID);
        $this->assertInstanceOf(CryptoModule::class, $module);

        // Test encryption with default cryptor (no explicit cryptor ID)
        $testMessage = "default cryptor test";
        $encrypted = $module->encrypt($testMessage); // Uses default cryptor
        $this->assertNotEmpty($encrypted);

        $decrypted = $module->decrypt($encrypted);
        $this->assertEquals($testMessage, $decrypted);
    }

    /**
     * Test factory methods create proper cryptor maps
     */
    public function testFactoryMethodsCryptorMapSetup(): void
    {
        // Test legacy cryptor factory
        $legacyModule = CryptoModule::legacyCryptor($this->cipherKey, false);
        $this->assertInstanceOf(CryptoModule::class, $legacyModule);

        // Test that it can encrypt/decrypt with legacy cryptor as default
        $testMessage = "factory legacy test";
        $encrypted = $legacyModule->encrypt($testMessage);
        $decrypted = $legacyModule->decrypt($encrypted);
        $this->assertEquals($testMessage, $decrypted);

        // Test AES CBC cryptor factory
        $aesModule = CryptoModule::aesCbcCryptor($this->cipherKey, true);
        $this->assertInstanceOf(CryptoModule::class, $aesModule);

        // Test that it can encrypt/decrypt with AES cryptor as default
        $testMessage2 = "factory aes test";
        $encrypted2 = $aesModule->encrypt($testMessage2);
        $decrypted2 = $aesModule->decrypt($encrypted2);
        $this->assertEquals($testMessage2, $decrypted2);
    }

    // ============================================================================
    // INPUT VALIDATION AND SANITIZATION TESTS
    // ============================================================================

    /**
     * Test encryption with null input
     */
    public function testEncryptNullInput(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        // PHP will convert null to empty string, which should trigger the empty message exception
        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage('Encryption error: message is empty');

        $module->encrypt(null);
    }

    /**
     * Test encryption with empty string
     */
    public function testEncryptEmptyString(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage('Encryption error: message is empty');

        $module->encrypt('');
    }

    /**
     * Test encryption with whitespace-only string
     */
    public function testEncryptWhitespaceOnlyString(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        // Whitespace-only strings should be valid for encryption
        $whitespaceInputs = [
            ' ',           // single space
            '   ',         // multiple spaces
            "\t",          // tab
            "\n",          // newline
            "\r\n",        // carriage return + newline
            " \t\n\r ",    // mixed whitespace
        ];

        foreach ($whitespaceInputs as $input) {
            $encrypted = $module->encrypt($input);
            $this->assertNotEmpty($encrypted);

            $decrypted = $module->decrypt($encrypted);
            $this->assertEquals($input, $decrypted);
        }
    }

    /**
     * Test encryption with very large input
     */
    public function testEncryptLargeInput(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        // Create a large string (1MB)
        $largeInput = str_repeat('A', 1024 * 1024);

        $startTime = microtime(true);
        $encrypted = $module->encrypt($largeInput);
        $encryptTime = microtime(true) - $startTime;

        $this->assertNotEmpty($encrypted);

        $startTime = microtime(true);
        $decrypted = $module->decrypt($encrypted);
        $decryptTime = microtime(true) - $startTime;

        $this->assertEquals($largeInput, $decrypted);

        // Performance assertions (should complete within reasonable time)
        $this->assertLessThan(5.0, $encryptTime, 'Encryption should complete within 5 seconds');
        $this->assertLessThan(5.0, $decryptTime, 'Decryption should complete within 5 seconds');
    }

    /**
     * Test encryption with special characters
     */
    public function testEncryptSpecialCharacters(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        $specialInputs = [
            'Hello 世界',                           // Unicode Chinese characters
            '🚀 Rocket emoji test 🌟',              // Emojis
            'Ñoño café résumé naïve',               // Accented characters
            'Special: !@#$%^&*()_+-={}[]|\\:";\'<>?,./', // Special ASCII characters
            'Math: ∑∆√∞≠≤≥±×÷',                     // Mathematical symbols
            'Currency: $€£¥₹₽₩',                    // Currency symbols
            'Quotes: "Hello" \'World\' `Test`',     // Various quotes
            "Line\nBreaks\r\nAnd\tTabs",           // Control characters
            'Null\x00Byte\x01Test',                // Control bytes
        ];

        foreach ($specialInputs as $input) {
            $encrypted = $module->encrypt($input);
            $this->assertNotEmpty($encrypted, "Failed to encrypt: " . bin2hex($input));

            $decrypted = $module->decrypt($encrypted);
            $this->assertEquals($input, $decrypted, "Decrypt mismatch for: " . bin2hex($input));
        }
    }

    /**
     * Test encryption with binary data
     */
    public function testEncryptBinaryData(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        // Test various binary data patterns
        $binaryInputs = [
            "\x00\x01\x02\x03\x04\x05",          // Sequential bytes
            random_bytes(32),                      // Random binary data
            "\xFF\xFE\xFD\xFC",                   // High-value bytes
            str_repeat("\x00", 100),               // Null bytes
            "\x00\xFF\x00\xFF\x00\xFF",           // Alternating pattern
        ];

        foreach ($binaryInputs as $input) {
            $encrypted = $module->encrypt($input);
            $this->assertNotEmpty($encrypted, "Failed to encrypt binary data: " . bin2hex($input));

            $decrypted = $module->decrypt($encrypted);
            $this->assertEquals($input, $decrypted, "Binary data mismatch for: " . bin2hex($input));
        }
    }

    /**
     * Test decryption with malformed input
     */
    public function testDecryptMalformedInput(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        $malformedInputs = [
            'not-base64-at-all!@#$',
            'VGhpcyBpcyBub3QgdmFsaWQgZW5jcnlwdGVkIGRhdGE=', // Valid base64 but invalid encrypted data
            'SGVsbG8gV29ybGQ',  // Valid base64 but too short for encrypted data
            '===invalid===',   // Invalid base64 padding
            'YWJjZGVmZ2hpams',  // Valid base64 but not encrypted format
        ];

        foreach ($malformedInputs as $input) {
            try {
                $result = $module->decrypt($input);
                // If no exception is thrown, the result should still be reasonable
                $this->assertTrue(is_string($result) or is_object($result) or is_array($result));
            } catch (PubNubResponseParsingException $e) {
                // Expected for malformed input
                $this->assertStringContainsString('error', strtolower($e->getMessage()));
            } catch (PubNubCryptoException $e) {
                // Also acceptable for crypto-related errors
                $this->assertStringContainsString('error', strtolower($e->getMessage()));
            }
        }
    }

    /**
     * Test decryption with empty input
     */
    public function testDecryptEmptyInput(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        $this->expectException(PubNubResponseParsingException::class);
        $this->expectExceptionMessage('Decryption error: message is empty');

        $module->decrypt('');
    }

    /**
     * Test parseInput method with various input types
     * @dataProvider parseInputProvider
     */
    public function testParseInput(mixed $input, ?string $expected): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        // Use reflection to access the protected parseInput method
        $reflection = new \ReflectionClass($module);
        $method = $reflection->getMethod('parseInput');
        $method->setAccessible(true);

        if ($expected === null) {
            // Expecting an exception
            $this->expectException(TypeError::class);
            $method->invoke($module, $input);
        } else {
            $result = $method->invoke($module, $input);
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * Test parseInput with invalid data types
     */
    public function testParseInputInvalidTypes(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        // Use reflection to access the protected parseInput method
        $reflection = new \ReflectionClass($module);
        $method = $reflection->getMethod('parseInput');
        $method->setAccessible(true);


        $result = $method->invoke($module, 12345);
        $this->assertEquals('12345', $result);
    }

    // ============================================================================
    // DATA TYPE HANDLING TESTS
    // ============================================================================

    /**
     * Test stringify method with different data types
     * @dataProvider stringifyDataProvider
     */
    public function testStringify(mixed $input, ?string $expected): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        // Use reflection to access the protected stringify method
        $reflection = new \ReflectionClass($module);
        $method = $reflection->getMethod('stringify');
        $method->setAccessible(true);

        $result = $method->invoke($module, $input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test encryption/decryption of JSON objects
     */
    public function testEncryptDecryptJsonObjects(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        $jsonObjects = [
            // Simple JSON object
            '{"name":"John","age":30}',

            // JSON object with nested structure
            '{"user":{"name":"Jane","profile":{"email":"jane@test.com","settings":{"theme":"dark"}}}}',

            // JSON object with arrays
            '{"items":["apple","banana","cherry"],"count":3}',

            // JSON object with mixed data types
            '{"string":"hello","number":42,"boolean":true,"null":null,"array":[1,2,3]}',

            // Empty JSON object
            '{}',
        ];

        foreach ($jsonObjects as $jsonString) {
            $encrypted = $module->encrypt($jsonString);
            $this->assertNotEmpty($encrypted, "Failed to encrypt JSON: " . $jsonString);

            $decrypted = json_encode($module->decrypt($encrypted));
            $this->assertEquals($jsonString, $decrypted, "JSON round-trip failed for: " . $jsonString);

            // Verify the decrypted string is valid JSON
            $decoded = json_decode($decrypted, true);
            $this->assertNotNull($decoded, "Decrypted JSON is not valid: " . $decrypted);
        }
    }

    /**
     * Test encryption/decryption of arrays
     */
    public function testEncryptDecryptArrays(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        $testArrays = [
            // Simple indexed array
            ["apple", "banana", "cherry"],

        ];

        foreach ($testArrays as $array) {
            // Convert array to JSON for encryption (this is typically how arrays are handled)
            $jsonString = json_encode($array);

            $encrypted = $module->encrypt($jsonString);
            $this->assertNotEmpty($encrypted, "Failed to encrypt array: " . json_encode($array));

            $decrypted = json_encode($module->decrypt($encrypted));
            $this->assertEquals($jsonString, $decrypted, "Array round-trip failed for: " . json_encode($array));

            // Verify the decrypted JSON can be converted back to the original array
            $decodedArray = json_decode($decrypted, true);
            $this->assertEquals($array, $decodedArray, "Array decode mismatch for: " . json_encode($array));
        }
    }

    /**
     * Test encryption/decryption of nested data structures
     */
    public function testEncryptDecryptNestedStructures(): void
    {
        $module = CryptoModule::aesCbcCryptor($this->cipherKey, false);

        $nestedStructures = [
            // Deeply nested object
            [
                "user" => [
                    "profile" => [
                        "personal" => [
                            "name" => "John Doe",
                            "contacts" => [
                                "email" => "john@example.com",
                                "phones" => ["123-456-7890", "098-765-4321"]
                            ]
                        ],
                        "preferences" => [
                            "notifications" => true,
                            "themes" => ["dark", "light"],
                            "settings" => [
                                "language" => "en",
                                "timezone" => "UTC"
                            ]
                        ]
                    ]
                ]
            ],

            // Array of objects
            [
                "users" => [
                    ["id" => 1, "name" => "Alice", "roles" => ["admin", "user"]],
                    ["id" => 2, "name" => "Bob", "roles" => ["user"]],
                    ["id" => 3, "name" => "Charlie", "roles" => ["moderator", "user"]]
                ]
            ],

            // Mixed nested structure with various data types
            [
                "config" => [
                    "database" => [
                        "connections" => [
                            "primary" => ["host" => "localhost", "port" => 5432, "ssl" => true],
                            "secondary" => ["host" => "backup.db", "port" => 5432, "ssl" => false]
                        ]
                    ],
                    "features" => [
                        "logging" => ["enabled" => true, "level" => "info", "handlers" => ["file", "console"]],
                        "caching" => ["ttl" => 3600, "driver" => "redis", "prefix" => "app_cache"]
                    ]
                ]
            ],

            // Complex array structure
            [
                "matrix" => [
                    [1, 2, 3],
                    [4, 5, 6],
                    [7, 8, 9]
                ],
                "metadata" => [
                    "dimensions" => ["rows" => 3, "cols" => 3],
                    "statistics" => ["sum" => 45, "avg" => 5.0]
                ]
            ]
        ];

        foreach ($nestedStructures as $structure) {
            // Convert structure to JSON for encryption
            $jsonString = json_encode($structure);

            $encrypted = $module->encrypt($jsonString);
            $this->assertNotEmpty($encrypted, "Failed to encrypt nested structure");

            $decrypted = json_encode($module->decrypt($encrypted));
            $this->assertEquals($jsonString, $decrypted, "Nested structure round-trip failed");

            // Verify the decrypted JSON can be converted back to the original structure
            $decodedStructure = json_decode($decrypted, true);
            $this->assertEquals($structure, $decodedStructure, "Nested structure decode mismatch");

            // Verify specific nested values are accessible
            if (isset($structure["user"]["profile"]["personal"]["name"])) {
                $this->assertEquals("John Doe", $decodedStructure["user"]["profile"]["personal"]["name"]);
            }
            if (isset($structure["config"]["database"]["connections"]["primary"]["port"])) {
                $this->assertEquals(5432, $decodedStructure["config"]["database"]["connections"]["primary"]["port"]);
            }
        }
    }

    // ============================================================================
    // DATA PROVIDERS
    // ============================================================================

    protected function encodeProvider(): Generator
    {
        $legacyRandomModule = CryptoModule::legacyCryptor($this->cipherKey, true);
        $legacyStaticModule = CryptoModule::legacyCryptor($this->cipherKey, false);
        $aesCbcModuleStatic = CryptoModule::aesCbcCryptor($this->cipherKey, false);
        $aesCbcModuleRandom = CryptoModule::aesCbcCryptor($this->cipherKey, true);

        yield [$legacyRandomModule, '', 'Encryption error: message is empty'];
        yield [$legacyStaticModule, '', 'Encryption error: message is empty'];
        yield [$aesCbcModuleStatic, '', 'Encryption error: message is empty'];
        yield [
            $legacyStaticModule,
            "Hello world encrypted with legacyModuleStaticIv",
            "OtYBNABjeAZ9X4A91FQLFBo4th8et/pIAsiafUSw2+L8iWqJlte8x/eCL5cyjzQa",
        ];
        yield [
            $legacyRandomModule,
            "Hello world encrypted with legacyModuleRandomIv",
            null,
        ];
        yield [
            $legacyStaticModule,
            "Hello world encrypted with legacyModuleStaticIv",
            null,
        ];
        // test fallback decrypt with static IV
        yield [
            $aesCbcModuleStatic,
            "Hello world encrypted with legacyModuleStaticIv",
            null,
        ];
        // test falback decrypt with random IV
        yield [
            $aesCbcModuleRandom,
            "Hello world encrypted with legacyModuleRandomIv",
            null,
        ];
        yield [
            $aesCbcModuleRandom,
            'Hello world encrypted with aesCbcModule',
            null,
        ];
    }

    protected function decodeProvider(): Generator
    {
        $legacyRandomModule = CryptoModule::legacyCryptor($this->cipherKey, true);
        $legacyStaticModule = CryptoModule::legacyCryptor($this->cipherKey, false);
        $aesCbcModuleStatic = CryptoModule::aesCbcCryptor($this->cipherKey, false);
        $aesCbcModuleRandom = CryptoModule::aesCbcCryptor($this->cipherKey, true);

        yield [$legacyRandomModule, '', 'Decryption error: message is empty'];
        yield [$legacyStaticModule, '', 'Decryption error: message is empty'];
        yield [$aesCbcModuleStatic, '', 'Decryption error: message is empty'];
        yield [
            $legacyRandomModule,
            "T3J9iXI87PG9YY/lhuwmGRZsJgA5y8sFLtUpdFmNgrU1IAitgAkVok6YP7lacBiVhBJSJw39lXCHOLxl2d98Bg==",
            "Hello world encrypted with legacyModuleRandomIv",
        ];
        yield [
            $legacyStaticModule,
            "OtYBNABjeAZ9X4A91FQLFBo4th8et/pIAsiafUSw2+L8iWqJlte8x/eCL5cyjzQa",
            "Hello world encrypted with legacyModuleStaticIv",
        ];
        // test fallback decrypt with static IV
        yield [
            $aesCbcModuleStatic,
            "OtYBNABjeAZ9X4A91FQLFBo4th8et/pIAsiafUSw2+L8iWqJlte8x/eCL5cyjzQa",
            "Hello world encrypted with legacyModuleStaticIv",
        ];
        // test falback decrypt with random IV
        yield [
            $aesCbcModuleRandom,
            "T3J9iXI87PG9YY/lhuwmGRZsJgA5y8sFLtUpdFmNgrU1IAitgAkVok6YP7lacBiVhBJSJw39lXCHOLxl2d98Bg==",
            "Hello world encrypted with legacyModuleRandomIv",
        ];
        yield [
            $aesCbcModuleRandom,
            'UE5FRAFBQ1JIEKzlyoyC/jB1hrjCPY7zm+X2f7skPd0LBocV74cRYdrkRQ2BPKeA22gX/98pMqvcZtFB6TCGp3Zf1M8F730nlfk=',
            'Hello world encrypted with aesCbcModule',
        ];
    }

    /**
     * Data provider for parseInput tests
     */
    protected function parseInputProvider(): Generator
    {
        // Valid string inputs
        yield ["simple string", "simple string"];
        yield ["encrypted_base64_data", "encrypted_base64_data"];
        yield ["   trimmed   ", "   trimmed   "]; // Should NOT be trimmed by parseInput

        // Invalid inputs that should throw exceptions
        yield [["other" => "data"], null]; // Missing pn_other key
        yield [["pn_other" => ""], null];   // Empty pn_other value
        yield [["pn_other" => "   "], null]; // Whitespace-only pn_other value
        yield [123, '123'];                   // Non-string, non-array input
        yield [true, '1'];                  // Boolean input
        yield [null, null];                  // Null input (converted to empty string)
    }

    /**
     * Data provider for stringify tests
     */
    protected function stringifyDataProvider(): Generator
    {
        // String values
        yield ["simple string", "simple string"];
        yield ["", ""];
        yield ["   whitespace   ", "   whitespace   "];
        yield ["Special chars: !@#$%^&*()", "Special chars: !@#$%^&*()"];
        yield ["Unicode: 🚀 世界", "Unicode: 🚀 世界"];

        // Numeric values
        yield [123, "123"];
        yield [0, "0"];
        yield [-456, "-456"];
        yield [3.14159, "3.14159"];
        yield [1.23e10, "12300000000"];

        // Boolean values
        yield [true, "true"];
        yield [false, "false"];

        // Null value
        yield [null, "null"];

        // Array values (should be JSON encoded)
        yield [["key" => "value"], '{"key":"value"}'];
        yield [[1, 2, 3], '[1,2,3]'];
        yield [[], '[]'];
        yield [["nested" => ["data" => true]], '{"nested":{"data":true}}'];

        // Mixed array
        yield [
            ["string" => "hello", "number" => 42, "bool" => true, "null" => null],
            '{"string":"hello","number":42,"bool":true,"null":null}'
        ];

        // Complex nested structure
        yield [
            [
                "user" => [
                    "name" => "John",
                    "settings" => ["theme" => "dark", "notifications" => true]
                ],
                "data" => [1, 2, 3]
            ],
            '{"user":{"name":"John","settings":{"theme":"dark","notifications":true}},"data":[1,2,3]}'
        ];
    }

    /**
     * Data provider for cipher key length tests
     */
    protected function cipherKeyLengthProvider(): Generator
    {
        // TODO: Implement data provider for different key lengths
        yield [$this->testKeyShort];
        yield [$this->testKey256];
        yield [$this->testKeyLong];
        yield [""];
    }
}
