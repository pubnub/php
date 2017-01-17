<?php

namespace PubNub;


class PubNubException extends \Exception
{
    /** @var string $errormsg */
    private $errormsg = "";

    private $pubnubError;

    /** @var  string $response */
    private $response;

    /** @var  int $statusCode */
    private $statusCode;

    /**
     * @return string
     */
    public function getErrormsg()
    {
        return $this->errormsg;
    }

    /**
     * @param string $errormsg
     */
    public function setErrormsg($errormsg)
    {
        $this->errormsg = $errormsg;
    }

    /**
     * @param mixed $pubnubError
     * @return $this
     */
    public function setPubnubError($pubnubError)
    {
        $this->pubnubError = $pubnubError;

        return $this;
    }

    /**
     * @return null|PubNubError
     */
    public function getPubnubError()
    {
        return $this->pubnubError;
    }

    /**
     * @param string $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }
}
