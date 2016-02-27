<?php

namespace Pubnub;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class PubnubLogger implements LoggerInterface
{
    private $logDebugMessages = false;
    private $className = "";

    public function __construct($className) {
        $this->className = $className;
    }

    public function emergency($message, array $context = array()) {

    }

    public function alert($message, array $context = array()) {

    }

    public function critical($message, array $context = array()) {

    }

    public function error($message, array $context = array()) {

    }

    public function warning($message, array $context = array()) {

    }

    public function notice($message, array $context = array()) {

    }

    public function info($message, array $context = array()) {

    }

    public function debug($message, array $context = array()) {
        if ($this->logDebugMessages) {
            echo $this->className . "::DEBUG:: ";
            print_r($message);
            echo "\n";
        }
    }

    public function log($level, $message, array $context = array()) {
        if ($level === LogLevel::DEBUG) {
            $this->debug($message, $context);
        }
    }

    public function enableDebug() {
        $this->logDebugMessages = true;
    }
}
