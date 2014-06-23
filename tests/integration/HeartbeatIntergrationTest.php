<?php

use Pubnub\Pubnub;
use \Pubnub\PubnubException;


class HeartbeatIntegrationTest extends \TestCase
{
    protected $pubnub_enc;
    protected $pubnub_sec;
    protected static $message = 'Hello from publish() test!';
    protected static $channel = 'pubnub_php_test';

    /**
     * @group heartbeat
     */
    public function testHeartBeatNeverCalled()
    {
        $this->pubnub->subscribe('demo', function ($result) {
            print_r($result);
            return true;
        });
        return;
        $expectedParams = array();
        $expectedQuery = array();

        $client = $this->getMock(
            '\Pubnub\Clients\DefaultClient',
            array('add'),
            array('blah.pubnub.com', false, false, false)
        );

        $pubnub = $this->getMock('\Pubnub\Pubnub', array(), array('demo', 'demo'));

//        $client->expects($this->once())
//                ->method('add')
//                ->with($this->callback(function ($subject) {
//                    print_r($subject);
//                    echo 'hi';
//                }));
//
        $pubnub->expects($this->any())
                ->method('getDefaultClient')
                ->withAnyParameters()
                ->will($this->returnValue($client));

        $result = $pubnub->getDefaultClient();

//        print_r($pubnub);
        print_r($result);
    }

}