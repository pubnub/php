<?php

namespace PubNub;

class PNConfiguration
{
    const EXPOSED_PROPERTIES = ['uuid'];

    /** @var  string Subscribe key provided by PubNub */
    private $subscribeKey;

    /** @var  string Publish key provided by PubNub */
    private $publishKey;

    /** @var  string Secret key provided by PubNub */
    private $secretKey;

    /** @var  string */
    private $uuid;

    /** @var  string */
    private $authKey;

    /** @var  string */
    private $origin;

    /** @var  bool Set to true to switch the client to HTTPS:// based communications. */
    private $secure;

    /**
     * Already configured PNConfiguration object with demo/demo as publish/subscribe keys.
     *
     * @return PNConfiguration config
     */
    public static function demoKeys()
    {
        $config = new static();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");

        return $config;
    }

    /**
     * @param string $subscribeKey
     */
    public function setSubscribeKey($subscribeKey)
    {
        $this->subscribeKey = $subscribeKey;
    }

    /**
     * @param string $publishKey
     */
    public function setPublishKey($publishKey)
    {
        $this->publishKey = $publishKey;
    }

    /**
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return string
     */
    public function getSubscribeKey()
    {
        return $this->subscribeKey;
    }

    /**
     * @return string
     */
    public function getPublishKey()
    {
        return $this->publishKey;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string|null authKey
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @param string|null $authKey
     */
    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;
    }
}