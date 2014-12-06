<?php

use Pubnub\PubnubPAM;

class PAMTest extends TestCase {

    /** @var  PubnubPAM */
    protected $pam;

    protected static $publish = 'pub-c-81d9633a-c5a0-4d6c-9600-fda148b61648';
    protected static $subscribe = 'sub-c-35ffee42-e763-11e3-afd8-02ee2ddab7fe';
    protected static $secret = 'sec-c-NDNlODA0ZmItNzZhMC00OTViLWI5NWMtM2M4MzA4ZWM2ZjIz';
    protected static $pnsdk = 'Pubnub-PHP/0.0.0.test';

    public function setUp()
    {
        $this->pam = new PubnubPAM(self::$publish, self::$subscribe, self::$secret, self::$pnsdk);
    }

    /**
     * Calculate a signature by secret key and message.
     *
     * @group pams
     */
    public function testSign()
    {
        $this->assertEquals('1RCVAFtHaQ0iQUPJTMNj4WB7ZIun38ROAHzvuYYRfHw=', $this->pam->sign('some message to sign'));
    }

    /**
     * @group pam
     */
    public function testGetRequestParams(){
        $getRequestParamsMethod = new \ReflectionMethod('\Pubnub\PubnubPAM', 'getRequestParams');
        $getRequestParamsMethod->setAccessible(true);

        $params = $getRequestParamsMethod->invoke($this->pam, array(
            'method' => 'some_method',
            'channel' => 'some_channel',
            'auth_key' => 'some_auth_key',
            'read' => 1,
            'write' => 0,
            'ttl' => 3600));

        $this->assertEquals('v1/auth/some_method/sub-key/' . self::$subscribe, join('/', $params['url']));
    }

    /**
     * @group pam
     */
    public function testParamsToString()
    {
        $arrayToStringify = array(
            'year' => 2014,
            'month' => 'december',
            'day' => 24
        );

        $expectedString = "year=2014&month=december&day=24";

        $paramsToStringMethod = new \ReflectionMethod('\Pubnub\PubnubPAM', 'paramsToString');
        $paramsToStringMethod->setAccessible(true);

        $this->assertEquals($expectedString, $paramsToStringMethod->invoke($this->pam, $arrayToStringify));
    }
}
