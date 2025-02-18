<?php

namespace PubNub\Endpoints\Objects;

trait MatchesETagTrait
{
    protected ?string $eTag = null;
    protected array $customHeaders = [];

    /**
     * @param string $eTag
     * @return $this
     */
    public function ifMatchesETag(string $eTag): self
    {
        $this->eTag = $eTag;
        $this->customHeaders['If-Match'] = $eTag;
        return $this;
    }
}
