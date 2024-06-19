<?php

namespace PubNub;

use PubNub\Exceptions\PubNubConfigurationException;
use PubNub\Exceptions\PubNubValidationException;
use WpOrg\Requests\Transport;
use PubNub\CryptoModule;

class PNConfiguration
{
    private const DEFAULT_NON_SUBSCRIBE_REQUEST_TIMEOUT = 10;
    private const DEFAULT_SUBSCRIBE_TIMEOUT = 310;
    private const DEFAULT_CONNECT_TIMEOUT = 10;
    private const DEFAULT_USE_RANDOM_IV = true;

    private bool $disableImmutableCheck = false;
    private bool $isLocked = false;

    /** @var  string Subscribe key provided by PubNub */
    private string $subscribeKey;

    /** @var  string Publish key provided by PubNub */
    private ?string $publishKey = null;

    /** @var  string Secret key provided by PubNub */
    private ?string $secretKey = null;

    /** @var  string */
    private ?string $authKey = null;

    /** @var  string */
    private string $userId;

    /** @var  string */
    private ?string $origin = null;

    /** @var  bool Set to true to switch the client to HTTPS:// based communications. */
    private bool $secure = true;

    /** @var  CryptoModule */
    private ?CryptoModule $crypto = null;

    /** @var  string */
    private ?string $filterExpression = null;

    /** @var int */
    protected int $nonSubscribeRequestTimeout;

    /** @var int */
    protected int $connectTimeout;

    /** @var  int */
    protected int $subscribeTimeout;

    /** @var  Transport */
    protected ?Transport $transport = null;

    /** @var bool */
    protected bool $useRandomIV;

    private ?bool $usingUserId = null;

    /**
     * PNConfiguration constructor.
     */
    public function __construct()
    {
        $this->nonSubscribeRequestTimeout = static::DEFAULT_NON_SUBSCRIBE_REQUEST_TIMEOUT;
        $this->connectTimeout = static::DEFAULT_CONNECT_TIMEOUT;
        $this->subscribeTimeout = static::DEFAULT_SUBSCRIBE_TIMEOUT;
        $this->useRandomIV = static::DEFAULT_USE_RANDOM_IV;
    }

    /**
     * Already configured PNConfiguration object with demo/demo as publish/subscribe keys.
     *
     * @return PNConfiguration config
     */
    public static function demoKeys(): PNConfiguration
    {
        $config = new self();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("demo");

        return $config;
    }


    /**
     * Returns a unlocked clone of the current configuration.
     * This is useful when you want to create a new configuration based on an existing one.
     *
     * @return PNConfiguration
     */
    public function clone(): PNConfiguration
    {
        $lockState = $this->isLocked;
        $this->isLocked = false;
        $result = clone $this;
        $this->isLocked = $lockState;
        return $result;
    }

    /**
     * @param string $subscribeKey
     * @return $this
     */
    public function setSubscribeKey(string $subscribeKey): self
    {
        $this->checkLock();
        $this->subscribeKey = $subscribeKey;

        return $this;
    }

    /**
     * @param string $publishKey
     * @return $this
     */
    public function setPublishKey(string $publishKey): self
    {
        $this->checkLock();
        $this->publishKey = $publishKey;

        return $this;
    }

    /**
     * @param string $secretKey
     * @return $this
     */
    public function setSecretKey(string $secretKey): self
    {
        $this->checkLock();
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCipherKey(): string
    {
        return $this->getCrypto()->getCipherKey();
    }

    public function isAesEnabled(): bool
    {
        return !!$this->crypto;
    }

    /**
     * @param string $cipherKey
     * @return $this
     */
    public function setCipherKey(string $cipherKey): self
    {
        $this->checkLock();
        if (!isset($this->crypto)) {
            $this->crypto = CryptoModule::legacyCryptor($cipherKey, $this->getUseRandomIV());
        } else {
            $this->getCrypto()->setCipherKey($cipherKey);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getNonSubscribeRequestTimeout(): int
    {
        return $this->nonSubscribeRequestTimeout;
    }

    /**
     * @return int
     */
    public function getSubscribeTimeout(): int
    {
        return $this->subscribeTimeout;
    }

    /**
     * @return int
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * @return string
     */
    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return $this
     */
    public function setOrigin($origin): self
    {
        $this->checkLock();
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubscribeKey(): string
    {
        if (!isset($this->subscribeKey)) {
            throw new PubNubValidationException("Subscribe Key not configured");
        }
        return $this->subscribeKey;
    }

    /**
     * @return string
     */
    public function getPublishKey(): string | null
    {
        return $this->publishKey;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string | null
    {
        return $this->secretKey;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param $secure
     * @return $this
     */
    public function setSecure(bool $secure = true): self
    {
        $this->checkLock();
        $this->secure = $secure;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        if (!isset($this->userId)) {
            throw new PubNubConfigurationException('UUID should not be empty');
        }
        return $this->userId;
    }

    /**
     * @param string $uuid
     * @return $this
     */
    public function setUuid(string $uuid): self
    {
        if (!is_null($this->usingUserId) && $this->usingUserId) {
            throw new PubNubConfigurationException("Cannot use UserId and UUID simultaneously");
        }
        if (!$this->isNotEmptyString($uuid)) {
            throw new PubNubConfigurationException("UUID should not be empty");
        }
        $this->usingUserId = false;
        $this->userId = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        if (!isset($this->userId)) {
            throw new PubNubConfigurationException('UUID should not be empty');
        }
        return $this->userId;
    }

    /**
     * @param string $userId
     * @return $this
     */
    public function setUserId(string $userId): self
    {
        $this->checkLock();
        if (!is_null($this->usingUserId) && !$this->usingUserId) {
            throw new PubNubConfigurationException("Cannot use UserId and UUID simultaneously");
        }
        if (!$this->isNotEmptyString($userId)) {
            throw new PubNubConfigurationException("UserID should not be empty");
        }
        $this->usingUserId = true;
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string|null authKey
     */
    public function getAuthKey(): ?string
    {
        return $this->authKey;
    }

    /**
     * @param string|null $authKey
     * @return $this
     */
    public function setAuthKey(string $authKey): self
    {
        $this->authKey = $authKey;

        return $this;
    }

    /**
     * @return CryptoModule
     * @throws \Exception
     */
    public function getCrypto(): CryptoModule
    {
        if (!$this->crypto) {
            throw new PubNubValidationException("You should set up either a cipher key or a crypto instance before");
        }

        return $this->crypto;
    }

    /**
     * @return null | CryptoModule
     */
    public function getCryptoSafe(): CryptoModule | null
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
    public function setCrypto(PubNubCryptoCore $crypto): self
    {
        $this->checkLock();
        $this->crypto = $crypto;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilterExpression(): string | null
    {
        return $this->filterExpression;
    }

    /**
     * @param string $filterExpression
     * @return $this
     */
    public function setFilterExpression(string $filterExpression): self
    {
        $this->checkLock();
        $this->filterExpression = $filterExpression;

        return $this;
    }

    /**
     * @param int $nonSubscribeRequestTimeout
     * @return $this
     */
    public function setNonSubscribeRequestTimeout(int $nonSubscribeRequestTimeout): self
    {
        $this->checkLock();
        $this->nonSubscribeRequestTimeout = $nonSubscribeRequestTimeout;

        return $this;
    }

    /**
     * @param int $connectTimeout
     * @return $this
     */
    public function setConnectTimeout(int $connectTimeout): self
    {
        $this->checkLock();
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    /**
     * @param int $subscribeTimeout
     * @return $this
     */
    public function setSubscribeTimeout(int $subscribeTimeout): self
    {
        $this->checkLock();
        $this->subscribeTimeout = $subscribeTimeout;

        return $this;
    }

    /**
     * @return Transport
     */
    public function getTransport(): Transport | null
    {
        return $this->transport;
    }

    /**
     * @param Transport $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->checkLock();
        $this->transport = $transport;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseRandomIV(): bool
    {
        return $this->useRandomIV;
    }

    /**
     * @param bool $useRandomIV
     * @return $this
     */
    public function setUseRandomIV($useRandomIV): self
    {
        $this->checkLock();
        $this->useRandomIV = $useRandomIV;

        if ($this->crypto != null) {
            $this->crypto->setUseRandomIV($this->useRandomIV);
        }

        return $this;
    }

    private function isNotEmptyString($value): bool
    {
        return (is_string($value) && strlen(trim($value)) > 0);
    }

    public function disableImmutableCheck(): self
    {
        $this->checkLock();
        $this->disableImmutableCheck = true;
        return $this;
    }

    public function lock()
    {
        $this->isLocked = true;
    }

    protected function checkLock()
    {
        if ($this->isLocked && !$this->disableImmutableCheck) {
            throw new PubNubConfigurationException("This configuration is locked and cannot be changed anymore.");
        }
    }
}
