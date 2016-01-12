<?php

use Pubnub\Pubnub;

abstract class TestCase extends PHPUnit_Framework_TestCase {

    /** @var Pubnub pubnub */
    protected $pubnub;

    protected static $keys = array(
        'subscribe_key' => 'sub-c-8f18abdc-a7d7-11e5-8231-02ee2ddab7fe',
        'publish_key' => 'pub-c-139c0366-9b6a-4a3f-ac03-4f8d31c86df2',
        'origin' => 'pubsub.pubnub.com'
    );

    public function setUp()
    {
        parent::setUp();

        $this->pubnub = new Pubnub(static::$keys);
    }

    /**
     * Remove generated namespaces and groups
     */
    public static function cleanup()
    {
        $pn = new Pubnub(static::$keys);

        $result = $pn->channelGroupListGroups();
        $groups = $result["payload"]["groups"];

        foreach ($groups as $groupName) {
            // WARNING: Check $groups for temporary generated groups if some tests fails.
            if (strpos($groupName, 'ptest') !== false) {
                $result = $pn->channelGroupRemoveGroup($groupName);
                if ($result['message'] === "OK") {
//                    print_r("Successfully removed group " . $groupName . "\n");
                }
            }
        }

        $result = $pn->channelGroupListNamespaces();
        $namespaces = $result["payload"]["namespaces"];

        foreach ($namespaces as $namespace) {
            if (strpos($namespace, 'ptest') !== false) {
                $result = $pn->channelGroupRemoveNamespace($namespace);
                if ($result['message'] === "OK") {
//                    print_r("Successfully removed namespace " . $namespace . "\n");
                }
            }
        }
    }
}
 
