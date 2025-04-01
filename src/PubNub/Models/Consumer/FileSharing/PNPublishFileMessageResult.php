<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNPublishFileMessageResult
{
    protected $timestamp;
    protected ?string $fileId;
    protected ?string $fileName;

    public function __construct($json)
    {
        $this->timestamp = $json[2];
    }

    public function __toString()
    {
        return "Sending file notification success with timestamp: " . $this->timestamp;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileId(string $fileId): self
    {
        $this->fileId = $fileId;
        return $this;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }
}
