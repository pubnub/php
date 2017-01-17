<?php

namespace PubNub;


class PubNubError
{
    /** @var  int $errorCode */
    private $errorCode;

    /** @var  int $errorCodeExtended */
    private $errorCodeExtended;

    /** @var  string $message includes a message from the thrown exception (if any.)*/
    private $message;

    /** @var  string $errorString PubNub supplied explanation of the error.*/
    private $errorString;

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     * @return $this
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}