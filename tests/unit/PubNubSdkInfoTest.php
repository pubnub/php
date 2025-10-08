<?php

use PHPUnit\Framework\TestCase;
use PubNub\PubNub;

class PubNubSdkInfoTest extends TestCase
{
    // ============================================================================
    // getSdkVersion() TESTS
    // ============================================================================

    public function testGetSdkVersionReturnsString(): void
    {
        $version = PubNub::getSdkVersion();

        $this->assertIsString($version);
    }

    public function testGetSdkVersionIsNotEmpty(): void
    {
        $version = PubNub::getSdkVersion();

        $this->assertNotEmpty($version);
    }

    public function testGetSdkVersionFollowsSemanticVersioning(): void
    {
        $version = PubNub::getSdkVersion();

        // Should match semantic versioning pattern (e.g., 7.1.0, 7.1.0-beta.1, etc.)
        $pattern = '/^\d+\.\d+\.\d+(-[a-zA-Z0-9\.\-]+)?$/';
        $this->assertMatchesRegularExpression($pattern, $version);
    }

    public function testGetSdkVersionIsConsistent(): void
    {
        $version1 = PubNub::getSdkVersion();
        $version2 = PubNub::getSdkVersion();

        $this->assertEquals($version1, $version2);
    }

    public function testGetSdkVersionStartsWithDigit(): void
    {
        $version = PubNub::getSdkVersion();

        $this->assertMatchesRegularExpression('/^\d/', $version);
    }

    public function testGetSdkVersionContainsMajorMinorPatch(): void
    {
        $version = PubNub::getSdkVersion();

        // Split by '.' and check we have at least 3 parts (major.minor.patch)
        $parts = explode('.', $version);
        $this->assertGreaterThanOrEqual(3, count($parts));
    }

    public function testGetSdkVersionMajorVersionIsNumeric(): void
    {
        $version = PubNub::getSdkVersion();
        $parts = explode('.', $version);

        $this->assertIsNumeric($parts[0]);
    }

    public function testGetSdkVersionMinorVersionIsNumeric(): void
    {
        $version = PubNub::getSdkVersion();
        $parts = explode('.', $version);

        $this->assertIsNumeric($parts[1]);
    }

    public function testGetSdkVersionPatchVersionIsNumeric(): void
    {
        $version = PubNub::getSdkVersion();
        $parts = explode('.', $version);

        // Patch might have a pre-release suffix (e.g., 0-beta)
        // So we extract just the numeric part
        preg_match('/^(\d+)/', $parts[2], $matches);
        $this->assertIsNumeric($matches[1]);
    }

    // ============================================================================
    // getSdkName() TESTS
    // ============================================================================

    public function testGetSdkNameReturnsString(): void
    {
        $name = PubNub::getSdkName();

        $this->assertIsString($name);
    }

    public function testGetSdkNameIsNotEmpty(): void
    {
        $name = PubNub::getSdkName();

        $this->assertNotEmpty($name);
    }

    public function testGetSdkNameIsConsistent(): void
    {
        $name1 = PubNub::getSdkName();
        $name2 = PubNub::getSdkName();

        $this->assertEquals($name1, $name2);
    }

    public function testGetSdkNameContainsPHP(): void
    {
        $name = PubNub::getSdkName();

        // SDK name should indicate it's a PHP SDK
        $this->assertMatchesRegularExpression('/php/i', $name);
    }

    public function testGetSdkNameContainsPubNub(): void
    {
        $name = PubNub::getSdkName();

        // SDK name should contain "PubNub"
        $this->assertMatchesRegularExpression('/pubnub/i', $name);
    }

    public function testGetSdkNameFormat(): void
    {
        $name = PubNub::getSdkName();

        // Should be in format like "PubNub-PHP" or similar
        $this->assertMatchesRegularExpression('/^[a-zA-Z\-]+$/', $name);
    }

    public function testGetSdkNameDoesNotContainVersion(): void
    {
        $name = PubNub::getSdkName();

        // Name should not contain version numbers
        $this->assertDoesNotMatchRegularExpression('/\d+\.\d+/', $name);
    }

    // ============================================================================
    // getSdkFullName() TESTS
    // ============================================================================

    public function testGetSdkFullNameReturnsString(): void
    {
        $fullName = PubNub::getSdkFullName();

        $this->assertIsString($fullName);
    }

    public function testGetSdkFullNameIsNotEmpty(): void
    {
        $fullName = PubNub::getSdkFullName();

        $this->assertNotEmpty($fullName);
    }

    public function testGetSdkFullNameIsConsistent(): void
    {
        $fullName1 = PubNub::getSdkFullName();
        $fullName2 = PubNub::getSdkFullName();

        $this->assertEquals($fullName1, $fullName2);
    }

    public function testGetSdkFullNameContainsSdkName(): void
    {
        $name = PubNub::getSdkName();
        $fullName = PubNub::getSdkFullName();

        $this->assertStringContainsString($name, $fullName);
    }

    public function testGetSdkFullNameContainsSdkVersion(): void
    {
        $version = PubNub::getSdkVersion();
        $fullName = PubNub::getSdkFullName();

        $this->assertStringContainsString($version, $fullName);
    }

    public function testGetSdkFullNameFormat(): void
    {
        $fullName = PubNub::getSdkFullName();

        // Should be in format like "PubNub-PHP/7.1.0" or "PubNub-PHP-7.1.0"
        $pattern = '/^[a-zA-Z\-]+[\/\-]\d+\.\d+\.\d+/';
        $this->assertMatchesRegularExpression($pattern, $fullName);
    }

    public function testGetSdkFullNameIsConcatenationOfNameAndVersion(): void
    {
        $name = PubNub::getSdkName();
        $version = PubNub::getSdkVersion();
        $fullName = PubNub::getSdkFullName();

        // Full name should be name + separator + version
        $expectedPattern = '/' . preg_quote($name, '/') . '[\/\-]' . preg_quote($version, '/') . '/';
        $this->assertMatchesRegularExpression($expectedPattern, $fullName);
    }

    public function testGetSdkFullNameLongerThanName(): void
    {
        $name = PubNub::getSdkName();
        $fullName = PubNub::getSdkFullName();

        $this->assertGreaterThan(strlen($name), strlen($fullName));
    }

    public function testGetSdkFullNameLongerThanVersion(): void
    {
        $version = PubNub::getSdkVersion();
        $fullName = PubNub::getSdkFullName();

        $this->assertGreaterThan(strlen($version), strlen($fullName));
    }

    // ============================================================================
    // INTEGRATION TESTS
    // ============================================================================

    public function testSdkInfoMethodsAreAllConsistent(): void
    {
        $name = PubNub::getSdkName();
        $version = PubNub::getSdkVersion();
        $fullName = PubNub::getSdkFullName();

        $this->assertStringContainsString($name, $fullName);
        $this->assertStringContainsString($version, $fullName);
    }

    public function testSdkInfoMethodsCanBeCalledMultipleTimes(): void
    {
        // Call each method multiple times
        for ($i = 0; $i < 5; $i++) {
            $this->assertIsString(PubNub::getSdkName());
            $this->assertIsString(PubNub::getSdkVersion());
            $this->assertIsString(PubNub::getSdkFullName());
        }
    }

    public function testSdkInfoMethodsReturnSameValuesAcrossInstances(): void
    {
        $pubnub1 = PubNub::demo();
        $pubnub2 = PubNub::demo();

        // Static methods should return same values regardless of instance
        $this->assertEquals(PubNub::getSdkName(), PubNub::getSdkName());
        $this->assertEquals(PubNub::getSdkVersion(), PubNub::getSdkVersion());
        $this->assertEquals(PubNub::getSdkFullName(), PubNub::getSdkFullName());
    }

    public function testSdkVersionCanBeParsed(): void
    {
        $version = PubNub::getSdkVersion();

        // Should be parsable as a version string
        $parts = explode('.', $version);

        $this->assertGreaterThanOrEqual(3, count($parts));
        $this->assertIsNumeric($parts[0]); // Major
        $this->assertIsNumeric($parts[1]); // Minor

        // Patch might have pre-release suffix, extract numeric part
        preg_match('/^(\d+)/', $parts[2], $matches);
        $this->assertNotEmpty($matches);
        $this->assertIsNumeric($matches[1]); // Patch
    }

    public function testSdkFullNameIsUsableForUserAgent(): void
    {
        $fullName = PubNub::getSdkFullName();

        // Should be a valid format for User-Agent headers (no spaces, special chars)
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9\-\.\/]+$/', $fullName);
    }

    public function testSdkNameIsUsableAsIdentifier(): void
    {
        $name = PubNub::getSdkName();

        // Should be a valid identifier (no spaces, no version numbers)
        $this->assertMatchesRegularExpression('/^[a-zA-Z\-]+$/', $name);
    }

    public function testSdkVersionIsValidSemanticVersion(): void
    {
        $version = PubNub::getSdkVersion();

        // Validate against semantic versioning 2.0.0 spec
        $semverPattern = '/^'
            . '(\d+)\.(\d+)\.(\d+)'                      // Major.Minor.Patch
            . '(-[0-9A-Za-z\-\.]+)?'                     // Pre-release (optional)
            . '(\+[0-9A-Za-z\-\.]+)?'                    // Build metadata (optional)
            . '$/';

        $this->assertMatchesRegularExpression($semverPattern, $version);
    }

    public function testSdkInfoDoesNotChangeAtRuntime(): void
    {
        // Capture initial values
        $name1 = PubNub::getSdkName();
        $version1 = PubNub::getSdkVersion();
        $fullName1 = PubNub::getSdkFullName();

        // Create some PubNub instances
        $pubnub1 = PubNub::demo();
        $pubnub2 = PubNub::demo();

        // Values should remain the same
        $this->assertEquals($name1, PubNub::getSdkName());
        $this->assertEquals($version1, PubNub::getSdkVersion());
        $this->assertEquals($fullName1, PubNub::getSdkFullName());
    }
}
