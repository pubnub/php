<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNGetFileDownloadURLResult
{
    protected string $fileUrl;

    /**
     *
     * @param string[] $response
     * @return void
     */
    public function __construct(array $response)
    {
        $this->fileUrl = $response['Location'][0];
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
