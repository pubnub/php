<?php

require_once '../../legacy/Pubnub.php';

abstract class TestCase extends PHPUnit_Framework_TestCase {

    protected $pubnub;

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new Pubnub(array(
            'subscribe_key' => 'demo',
            'publish_key' => 'demo',
            'origin' => 'pubsub.pubnub.com'
        ));
    }

    public static function assertGreaterThan($expected, $actual)
    {
        self::assertTrue(intval($actual) > intval($expected),
            "Failed asserting that actual value is greater than expected.");
    }
}
 