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
        $time = $this->pubnub->time()->sync();

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
        $this->pubnub->time()->sync();

        $this->assertFalse($transport->isRequestedSecureOrigin());
    }
}

class CheckSslTransport implements \WpOrg\Requests\Transport
{
    protected $requestedThroughHttps;

    public function isRequestedSecureOrigin()
    {
        return $this->requestedThroughHttps;
    }

    public function request($url, $headers = array(), $data = array(), $options = array())
    {
        $this->requestedThroughHttps = substr($url, 0, 5) === 'https';

        return "HTTP/1.1 200 OK\r\n"
            . "Content-Type: text/javascript; charset=\"UTF-8\"\r\n"
            . "Connection: Closed\r\n\r\n"
            . "[16614599133417872]";
    }

    public function request_multiple($requests, $options)
    {
    }

    public static function test($capabilities = [])
    {
    }
}
