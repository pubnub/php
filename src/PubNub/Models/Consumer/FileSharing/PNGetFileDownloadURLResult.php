<?php

namespace PubNub\Models\Consumer\FileSharing;

use PubNub\Exceptions\PubNubResponseParsingException;

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
        try {
            $this->fileUrl = $response['Location'][0];
            assert(is_string($this->fileUrl));
            assert(!empty($this->fileUrl));
        } catch (\Exception) {
            throw new PubNubResponseParsingException("Failed to parse response: " . json_encode($response));
        }
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
