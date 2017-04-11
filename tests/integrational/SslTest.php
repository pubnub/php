<?php

namespace Tests\Integrational;


class SslTest extends \PubNubTestCase
{
    /**
     * @group ssl
     * @group ssl-integrational
     */
    public function testSslIsSetByDefault()
    {
        $transport = new CheckSslTransport();
        $this->pubnub->getConfiguration()->setTransport($transport);
        $this->pubnub->time()->envelope();

        $this->assertTrue($transport->isRequestedSecureOrigin());
    }

    /**
     * @group ssl
     * @group ssl-integrational
     */
    public function testSslCanBeDisabled()
    {
        $transport = new CheckSslTransport();
        $this->pubnub->getConfiguration()->setTransport($transport);
        $this->pubnub->getConfiguration()->setSecure(false);
        $this->pubnub->time()->envelope();

        $this->assertFalse($transport->isRequestedSecureOrigin());
    }
}


class CheckSslTransport implements \Requests_Transport {
    protected $requestedThroughHttps;

    public function isRequestedSecureOrigin()
    {
        return $this->requestedThroughHttps;
    }

    public function request($url, $headers = array(), $data = array(), $options = array())
    {
        $this->requestedThroughHttps = substr($url, 0, 5) === 'https';

        return "HTTP/1.1 OK Content-Type: text/plain\r\nConnection: close\r\n\r\n[123]";
    }

    public function request_multiple($requests, $options)
    {
    }

    public static function test()
    {
    }
}