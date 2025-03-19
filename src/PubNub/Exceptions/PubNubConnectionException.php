<?php

namespace PubNub\Exceptions;

class PubNubConnectionException extends PubNubException
{
    /** @var  \Throwable */
    protected $originalException;

    /**
     * @return \Throwable
     */
    public function getOriginalException()
    {
        return $this->originalException;
    }

    /**
     * @param \Throwable $originalException
     * @return $this
     */
    public function setOriginalException($originalException)
    {
        $this->originalException = $originalException;
        $this->message = $originalException->getMessage();

        return $this;
    }
}
