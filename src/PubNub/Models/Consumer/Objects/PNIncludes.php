<?php

namespace PubNub\Models\Consumer\Objects;

class PNIncludes
{
    public bool $custom = false;
    public bool $status = false;
    public bool $totalCount = false;
    public bool $type = false;

    /** @var string[] */
    protected array $mapping = [
        'custom' => 'custom',
        'status' => 'status',
        'totalCount' => 'totalCount',
        'type' => 'type',
    ];

    public function __toString(): string
    {
        $result = [];
        foreach ($this->mapping as $key => $value) {
            if (isset($this->$key)) {
                array_push($result, $value);
            }
        }
        return implode(',', $result);
    }

    public function custom(bool $custom = true): static
    {
        $this->custom = $custom;
        return $this;
    }

    public function status(bool $status = true): static
    {
        $this->status = $status;
        return $this;
    }

    public function totalCount(bool $totalCount = true): static
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    public function type(bool $type = true): static
    {
        $this->type = $type;
        return $this;
    }
}
