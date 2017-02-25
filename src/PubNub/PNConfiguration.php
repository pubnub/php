<?php

namespace PubNub;

use PubNub\Exceptions\PubNubValidationException;

class PNConfiguration
{
    /** @var  string Subscribe key provided by PubNub */
    private $subscribeKey;

    /** @var  string Publish key provided by PubNub */
    private $publishKey;

    /** @var  string Secret key provided by PubNub */
    private $secretKey;

    /** @var  string */
    private $authKey;

    /** @var  string */
    private $uuid;

    /** @var  string */
    private $origin;

    /** @var  bool Set to true to switch the client to HTTPS:// based communications. */
    private $secure;

    /** @var  PubNubCryptoCore */
    private $crypto;

    /** @var  string */
    private $filterExpression;

    /** @var int */
    protected $nonSubscribeRequestTimeout = 10;

    /** @var int */
    protected $connectedTimeout = 5;

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
        return $this->getCrypto()->getCipherKey();
    }

    public function isAesEnabled()
    {
        return !!$this->crypto;
    }

    /**
     * @param string $cipherKey
     * @return $this
     */
    public function setCipherKey($cipherKey)
    {
        if ($this->crypto == null) {
            $this->crypto = new PubNubCrypto($cipherKey);
        } else {
            $this->getCrypto()->setCipherKey($cipherKey);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getNonSubscribeRequestTimeout()
    {
        return $this->nonSubscribeRequestTimeout;
    }

    /**
     * @return int
     */
    public function getConnectTimeout()
    {
        return $this->connectedTimeout;
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
        if (empty($this->uuid)) {
            $this->uuid = PubNubUtil::uuid();
        }

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
     * @return $this
     */
    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;

        return $this;
    }

    /**
     * @return PubNubCryptoCore
     * @throws \Exception
     */
    public function getCrypto()
    {
        if (!$this->crypto) {
            throw new PubNubValidationException("You should set up either a cipher key or a crypto instance before");
        }

        return $this->crypto;
    }

    /**
     * @return null|PubNubCryptoCore
     */
    public function getCryptoSafe()
    {
        try {
            return $this->getCrypto();
        } catch (PubNubValidationException $e) {
            return null;
        }
    }

    /**
     * @param PubNubCryptoCore $crypto
     * @return $this
     */
    public function setCrypto($crypto)
    {
        $this->crypto = $crypto;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilterExpression()
    {
        return $this->filterExpression;
    }

    /**
     * @param string $filterExpression
     * @return $this
     */
    public function setFilterExpression($filterExpression)
    {
        $this->filterExpression = $filterExpression;

        return $this;
    }

}
