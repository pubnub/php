<?php

namespace PubNub\Models\Consumer\History;

use PubNub\PubNubCryptoCore;


class PNHistoryItemResult
{
    /** @var  string */
    private $entry;

    /** @var  PubNubCryptoCore */
    private $crypto;

    /** @var  int */
    private $timetoken;

    /**
     * PNHistoryItemResult constructor.
     * @param string $entry
     * @param PubNubCryptoCore $crypto
     * @param int $timetoken
     */
    public function __construct($entry, $crypto, $timetoken = null)
    {
        $this->entry = $entry;
        $this->crypto = $crypto;
        $this->timetoken = $timetoken;
    }

    public function __toString()
    {
        return sprintf("History item with tt: %s and content: %s", $this->getTimetoken(), $this->getEntry());
    }

    public function decrypt()
    {
        $this->entry = $this->crypto->decrypt($this->entry);
    }

    /**
     * @return string
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @return int
     */
    public function getTimetoken()
    {
        return $this->timetoken;
    }
}