<?php

namespace PubNub\Models\Consumer\AccessManager;


use PubNub\PubNubUtil;

abstract class PNPAMEntityData
{
    /** @var  string */
    protected $name;

    /** @var  array */
    protected $authKeys;

    /** @var  bool */
    protected $readEnabled;

    /** @var  bool */
    protected $writeEnabled;

    /** @var  bool */
    protected $manageEnabled;

    /** @var  int */
    protected $ttl;

    /**
     * PNPAMEntityData constructor.
     * @param string $name
     * @param array $authKeys
     * @param bool $readEnabled
     * @param bool $writeEnabled
     * @param bool $manageEnabled
     * @param int $ttl
     */
    public function __construct($name, array $authKeys, $readEnabled, $writeEnabled, $manageEnabled, $ttl)
    {
        $this->name = $name;
        $this->authKeys = $authKeys;
        $this->readEnabled = $readEnabled;
        $this->writeEnabled = $writeEnabled;
        $this->manageEnabled = $manageEnabled;
        $this->ttl = $ttl;
    }

    public static function fromJson($name, $jsonInput)
    {
        list($r, $w, $m, $ttl) = PubNubUtil::fetchPamPermissionsFrom($jsonInput);

        $constructedAuthKeys = [];

        if (array_key_exists('auths', $jsonInput)) {
            foreach ($jsonInput['auths'] as $authKey => $value) {
                $constructedAuthKeys[$authKey] = PNAccessManagerKeyData::fromJson($value);
            }
        }

        return new static($name, $constructedAuthKeys, $r, $w, $m, $ttl);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return PNAccessManagerKeyData[]
     */
    public function getAuthKeys()
    {
        return $this->authKeys;
    }

    /**
     * @return bool
     */
    public function isReadEnabled()
    {
        return $this->readEnabled;
    }

    /**
     * @return bool
     */
    public function isWriteEnabled()
    {
        return $this->writeEnabled;
    }

    /**
     * @return bool
     */
    public function isManageEnabled()
    {
        return $this->manageEnabled;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }
}