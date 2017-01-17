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
    private $cipherKey;

    /** @var  string */
    private $authKey;

    /** @var  string */
    private $uuid;

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
     * @return $this
     */
    public function setSubscribeKey($subscribeKey)
    {
        $this->subscribeKey = $subscribeKey;

        return $this;
    }

    /**
     * @param string $publishKey
     * @return $this
     */
    public function setPublishKey($publishKey)
    {
        $this->publishKey = $publishKey;

        return $this;
    }

    /**
     * @param string $secretKey
     * @return $this
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCipherKey()
    {
        return $this->cipherKey;
    }

    /**
     * @param string $cipherKey
     */
    public function setCipherKey($cipherKey)
    {
        $this->cipherKey = $cipherKey;
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
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
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