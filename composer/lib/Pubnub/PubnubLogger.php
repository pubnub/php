<?php

namespace Pubnub;

class PubnubLogger
{
    private $logDebugMessages = false;
    private $className = "";

    public function __construct($className) {
        $this->className = $className;
    }

    public function debug($message)
    {
        if ($this->logDebugMessages) {
            echo $this->className . "::DEBUG:: ";
            print_r($message);
            echo "\n";
        }
    }

    public function enableDebug() {
        $this->logDebugMessages = true;
    }
}