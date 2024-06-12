<?php

namespace PubNub\Endpoints\FileSharing;

use Exception;
use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use WpOrg\Requests\Requests;

class SendFile extends Endpoint
{
    protected string $channel;
    protected string $fileName;
    protected mixed $message;
    protected mixed $meta;
    protected bool $shouldStore;
    protected int $ttl;
    protected mixed $fileContent;
    protected mixed $fileHandle;
    protected mixed $fileUploadEnvelope;
    protected bool $shouldCompress = false;

    protected array $customParamMapping = [];

    public function channel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    public function fileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function meta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    public function shouldStore($shouldStore)
    {
        $this->shouldStore = $shouldStore;
        return $this;
    }

    public function ttl($ttl)
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function fileHandle($fileHandle)
    {
        $this->fileHandle = $fileHandle;
        return $this;
    }

    public function fileContent($fileContent)
    {
        $this->fileContent = $fileContent;
        return $this;
    }

    public function requestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    protected function connectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }
    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateChannel();
    }

    protected function validateChannel(): void
    {
        if (!$this->channel) {
            throw new PubNubValidationException("Channel missing");
        }
    }

    /**
     * @param array $result Decoded json
     * @return PNPublishResult
     */
    protected function createResponse($result)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    protected function buildData()
    {
        return null;
    }

    /**
     * @return int
     */
    protected function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * @return int
     */
    protected function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    /**
     * @return string
     */
    protected function httpMethod()
    {
        return PNHttpMethod::POST;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNSendFileAction;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "Send File";
    }


    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];
        foreach ($this->customParamMapping as $customParam => $requestParam) {
            if (isset($this->$customParam) && !empty($this->$customParam)) {
                $params[$requestParam] = $this->$customParam;
            }
        }

        return $params;
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        print("\n" . __FILE__ . ":" . __LINE__ . " \n----PATH----\n{$this->fileUploadEnvelope->getUrl()}:\n");
        var_dump(parse_url($this->fileUploadEnvelope->getUrl(), PHP_URL_PATH));
        return parse_url($this->fileUploadEnvelope->getUrl(), PHP_URL_PATH);
    }

    protected function encryptPayload()
    {
        $crypto = $this->pubnub->getCryptoSafe();

        if ($this->fileHandle) {
            $fileContent = fread($this->fileHandle, filesize($this->fileHandle));
        } else {
            $fileContent = $this->fileContent;
        }

        if ($crypto) {
            return $crypto->encrypt($fileContent);
        }

        return $fileContent;
    }

    protected function uploadFile()
    {
        $response = Requests::POST($this->fileUploadEnvelope->getUrl(), [], $this->fileUploadEnvelope->getFormFields());
        var_dump($response);
    }

    public function buildFileUploadRequest()
    {
        $encryptPayload = $this->encryptPayload();
        $multipartBody = [];
        foreach ($this->fileUploadEnvelope->data["form_fields"] as $formField) {
            $multipartBody[$formField["key"]] = [null, $formField["value"]];
        }
        $multipartBody["file"] = [$this->fileName, $encryptPayload, null];
        return $multipartBody;
    }

    public function sync()
    {
        $this->fileUploadEnvelope = (new FetchFileUploadS3Data($this->pubnub))
            ->channel($this->channel)
            ->fileName($this->fileName)
            ->sync();

        $this->customHost = parse_url($this->fileUploadEnvelope->getUrl(), PHP_URL_HOST);

        $envelope = $this->invokeRequestAndCacheIt();

        if ($envelope->isError()) {
            throw $envelope->getStatus()->getException();
        }

        var_dump($envelope->getResult());
        print("\n" . __FILE__ . ":" . __LINE__ . " fileUploadData:\n");
        var_dump($this->fileUploadEnvelope);

        $publishRequest = new PublishFileMessage($this->pubnub);
        $publishRequest->channel($this->channel)
            ->fileId($this->fileUploadEnvelope->getFileId())
            ->fileName($this->fileName);

        if (isset($this->meta)) {
            $publishRequest->meta($this->meta);
        }
        if (isset($this->meta)) {
            $publishRequest->message($this->message);
        }
        if (isset($this->meta)) {
            $publishRequest->shouldStore($this->shouldStore);
        }
        if (isset($this->meta)) {
            $publishRequest->ttl($this->ttl);
        }

        $publishResponse = $publishRequest->sync();

        return $publishResponse;
    }
}
