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
    public function ifMatchesETag(string $eTag): static
    {
        $this->eTag = $eTag;
        $this->customHeaders['If-Match'] = $eTag;
        return $this;
    }
}
