<?php
 
namespace Pubnub\Clients;

use Pubnub\PubnubException;

abstract class Client
{
    /** @var bool SSL enabled */
    protected $ssl = false;

    /** @var string proxy address*/
    protected $proxy = null;

    /** @var string pem path */
    protected $pem_path = null;

    /** @var string pubnub sdk version */
    protected $pnsdk = null;

    /** @var string origin */
    protected $origin = 'pubsub.pubnub.com'; // Change this to your custom origin, or IUNDERSTAND.pubnub.com

    /** @var array of requests */
    protected $requests = array();

    /** @var int timeout for non-subscribe requests in seconds */
    protected $curlTimeout = 30;

    /** @var int timeout for subscribe requests in seconds */
    protected $curlSubscribeTimeout = 310;

    /** @var bool check for peer certificate validity */
    protected $verifyPeer = true;

    public function __construct($origin, $ssl, $proxy, $pem, $verifyPeer)
    {
        $this->ssl = (bool) $ssl;

        if (!empty($proxy)) {
            $this->proxy = $proxy;
        }

        if (!empty($pem)) {
            $this->pem_path = $pem;
        }

        if (!empty($origin)) {
            $this->origin = $origin;
        }

        $this->verifyPeer = $verifyPeer;

        // TODO: review origin
        if ($origin == "PHP.pubnub.com") {
            trigger_error("Before running in production, please contact support@pubnub.com for your custom origin.\nPlease set the origin from PHP.pubnub.com to IUNDERSTAND.pubnub.com to remove this warning.\n", E_USER_NOTICE);
        }

        if ($ssl) $this->origin = 'https://' . $this->origin;
        else      $this->origin = 'http://' . $this->origin;
    }

    public function add(array $path, array $query)
    {
        $this->requests[] = array($path, $query);
    }

    public function setTimeout($timeout)
    {
        $this->curlTimeout = $timeout;
    }

    public function setSubscribeTimeout($timeout)
    {
        $this->curlSubscribeTimeout = $timeout;
    }

    protected function bootstrapOptions()
    {
        $options = array(
            CURLOPT_USERAGENT => "PHP",
            CURLOPT_RETURNTRANSFER => 1
        );

        if ($this->proxy) {
            $options[CURLOPT_PROXY] = $this->proxy;
        }

        if ($this->ssl && $this->verifyPeer) {
            $options[CURLOPT_SSL_VERIFYPEER] = true;
            $options[CURLOPT_SSL_VERIFYHOST] = 2;

            $pemPathAndFilename = $this->pem_path . "/pubnub.com.pem";

            if (file_exists($pemPathAndFilename)) {
                $options[CURLOPT_CAINFO] = $pemPathAndFilename;
            } else {
                throw new PubnubException("Can't find PEM file. Please set pem_path in initializer.");
            }
        } else if ($this->ssl && !$this->verifyPeer) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }

        return $options;
    }

    /**
     * Return array of curl handlers for all requests
     * @return array
     */
    protected function chs()
    {
        $chs = array();
        $options = $this->bootstrapOptions();

        foreach ($this->requests as $requestArray) {
            $request = $requestArray[0];
            $query = $requestArray[1];
            $timeout = static::requestIsSubscribe($request) ? $this->curlSubscribeTimeout : $this->curlTimeout;

            $ch = curl_init();

            array_unshift($request, $this->origin);

            $url = implode('/', $request) . $this->glue($query);

            curl_setopt_array($ch, $options);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_URL, $url);

            $chs[] = $ch;
        }

        return $chs;
    }

    private static function requestIsSubscribe(array $request) {
        return $request[0] == "subscribe";
    }

    protected static function glue(array $query)
    {
        $result = array();

        foreach($query as $key => $value) {
            $result[] = "$key=$value";
        }

        return "?" . join('&', $result);
    }
}

