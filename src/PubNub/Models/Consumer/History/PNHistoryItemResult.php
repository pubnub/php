<?php

namespace PubNub\Models\Consumer\History;

use PubNub\PubNubCrypto;
use PubNub\PubNubUtil;

class PNHistoryItemResult
{
    private $entry;
    private $crypto;
    private $timetoken;

    public function __construct($entry, $crypto, $timetoken = null)
    {
        $this->entry = $entry;
        $this->crypto = $crypto;
        $this->timetoken = $timetoken;
    }

    public function __toString()
    {
        return sprintf("History item with tt: %s and content: %s", $this->timetoken, $this->entry);
    }


    public function decrypt($cipherKey)
    {
        $this->entry = new PubNubCrypto($cipherKey, $this->entry);
    }
}