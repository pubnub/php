<?php

use Pubnub\Pubnub;

abstract class TestCase extends PHPUnit_Framework_TestCase {

    /** @var Pubnub pubnub */
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

    /**
     * Remove generated namespaces and groups
     */
    public static function cleanup()
    {
        $pn = new Pubnub(array(
            'publish_key' => 'demo',
            'subscribe_key' => 'demo'
        ));

        $result = $pn->channelGroupListGroups();
        $groups = $result["payload"]["groups"];

        foreach ($groups as $groupName) {
            if (strpos($groupName, 'ptest') !== false) {
                $result = $pn->channelGroupRemoveGroup($groupName);
                if ($result['message'] === "OK") {
                    print_r("Successfully removed group " . $groupName . "\n");
                }
            }
        }
    }
}
 
