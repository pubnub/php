<?php

namespace PubNub\Endpoints\FileSharing;

use Exception;
use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;

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

    protected array $customParamMapping = [

    ];

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

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        throw new Exception('Not implemented');
        $this->validateSubscribeKey();
        $this->validatePublishKey();
    }


    /**
     * @param array $json Decoded json
     * @return PNPublishResult
     */
    protected function createResponse($json)
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
        return PNHttpMethod::GET;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNFetchMessagesOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "Fetch Messages";
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
        return $this->fileUploadEnvelope->data["url"];
    }

    public function buildFileUploadRequest()
    {
        /*

    def build_file_upload_request(self):
        file = self.encrypt_payload()
        multipart_body = {}
        for form_field in self._file_upload_envelope.result.data["form_fields"]:
            multipart_body[form_field["key"]] = (None, form_field["value"])

        multipart_body["file"] = (self._file_name, file, None)

        return multipart_body
        */
    }
}
