<?php

namespace PubNub\Models\Consumer\AccessManager;


use PubNub\PubNubUtil;

class PNAccessManagerKeyData
{
    /** @var  bool */
    protected $readEnabled;

    /** @var  bool */
    protected $writeEnabled;

    /** @var  bool */
    protected $manageEnabled;

    /** @var  int */
    protected $ttl;

    /**
     * PNAccessManagerKeyData constructor.
     * @param bool $readEnabled
     * @param bool $writeEnabled
     * @param bool $manageEnabled
     * @param int $ttl
     */
    public function __construct($readEnabled, $writeEnabled, $manageEnabled, $ttl)
    {
        $this->readEnabled = $readEnabled;
        $this->writeEnabled = $writeEnabled;
        $this->manageEnabled = $manageEnabled;
        $this->ttl = $ttl;
    }

    public static function fromJson($jsonInput)
    {
        list($r, $w, $m, $ttl) = PubNubUtil::fetchPamPermissionsFrom($jsonInput);

        return new static($r, $w, $m, $ttl);
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
