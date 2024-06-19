<?php

namespace Tests\Integrational;

use PubNub\PubNub;

class SslTest extends \PubNubTestCase
{
    /**
     * @group ssl
     * @group ssl-integrational
     */
    public function testSslIsSetByDefault()
    {
        $transport = new CheckSslTransport();
        $config = $this->config->clone();
        $config->setTransport($transport);
        $pubnub = new PubNub($config);

        $pubnub->time()->sync();

        $this->assertTrue($transport->isRequestedSecureOrigin());
    }

    /**
     * @group ssl
     * @group ssl-integrational
     */
    public function testSslCanBeDisabled()
    {
        $transport = new CheckSslTransport();
        $config = $this->config->clone();
        $config->setTransport($transport);
        $config->setSecure(false);
        $pubnub = new PubNub($config);
        $pubnub->time()->sync();

        $this->assertFalse($transport->isRequestedSecureOrigin());
    }
}

//phpcs:ignore PSR1.Classes.ClassDeclaration
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

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName
    public function request_multiple($requests, $options)
    {
    }

    public static function test($capabilities = [])
    {
    }
}
