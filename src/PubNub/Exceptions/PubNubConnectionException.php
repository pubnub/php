<?php

namespace PubNub\Exceptions;


class PubNubConnectionException extends PubNubException
{
    /** @var  \Exception */
    private $originalException;

    /**
     * @return \Exception
     */
    public function getOriginalException()
    {
        return $this->originalException;
    }

    /**
     * @param \Exception $originalException
     * @return $this
     */
    public function setOriginalException($originalException)
    {
        $this->originalException = $originalException;

        return $this;
    }
}
