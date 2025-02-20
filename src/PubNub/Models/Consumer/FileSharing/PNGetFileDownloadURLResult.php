<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNGetFileDownloadURLResult
{
    protected string $fileUrl;

    public function __construct($response)
    {
        $this->fileUrl = $response->getHeader('location')[0];
    }

    public function __toString()
    {
        return "Get file URL success with URL: {$this->fileUrl}";
    }

    public function getFileUrl()
    {
        return $this->fileUrl;
    }
}
