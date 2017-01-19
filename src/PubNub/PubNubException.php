<?php

namespace PubNub;


class PubNubException extends \Exception
{
    /** @var string $errormsg */
    private $errormsg = "";

    /** @var  PubNubError */
    private $pubnubError;

    /** @var  string $response */
    private $response;

    /** @var  int $statusCode */
    private $statusCode;

    /**
     * Rebuild private 'message' field from another fields
     * of the exception and it's error
     */
    private function rebuildMessageString()
    {
        // 1 - errormsg
        // 2 - pubnubError->errorString (by pubnub)
        // 3 - pubnubError->message (by original exception)
        $err = $this->pubnubError;

        if (empty($this->errormsg) &&  $err!== null) {
            if (!empty($err->getErrorString()) && !empty($err->getMessage())) {
                $this->message = sprintf("%s. Original exception: \"%s\"",
                    $err->getErrorString(),
                    $err->getMessage());
            } else if (!empty($err->getErrorString())) {
                $this->message = $this->pubnubError->getErrorString();
            } else if (!empty($err->getMessage())) {
                $this->message = $this->pubnubError->getMessage();
            }
        } else {
            $this->message = $this->errormsg;
        }
    }

    /**
     * @return string
     */
    public function getErrormsg()
    {
        return $this->errormsg;
    }

    /**
     * @param string $errormsg
     * @return $this
     */
    public function setErrormsg($errormsg)
    {
        $this->errormsg = $errormsg;
        $this->rebuildMessageString();

        return $this;
    }

    /**
     * @param PubNubError $pubnubError
     * @return $this
     */
    public function setPubnubError($pubnubError)
    {
        $this->pubnubError = $pubnubError;
        $this->rebuildMessageString();

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
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }
}
