<?php

namespace PubNub\Endpoints\FileSharing;

use PubNub\Endpoints\Endpoint;
use PubNub\Exceptions\PubNubValidationException;

abstract class FileSharingEndpoint extends Endpoint
{
    protected ?string $channel;
    protected ?string $fileId;
    protected ?string $fileName;

    public function channel(string $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function fileId(string $fileId): static
    {
        $this->fileId = $fileId;
        return $this;
    }

    public function fileName(string $fileName): static
    {
        $this->fileName = $fileName;
        return $this;
    }

    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateChannel();
        $this->validateFileId();
        $this->validateFileName();
    }

    protected function getRequestTimeout(): int
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    protected function getConnectTimeout(): int
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    protected function validateChannel(): void
    {
        if (!$this->channel) {
            throw new PubNubValidationException("Channel missing");
        }
    }

    protected function validateFileId(): void
    {
        if ($this->fileId === null) {
            throw new PubNubValidationException("File ID missing");
        }
    }

    protected function validateFileName(): void
    {
        if ($this->fileName === null) {
            throw new PubNubValidationException("File name missing");
        }
    }
}
