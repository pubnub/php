<?php

namespace PubNub\Models\Consumer\Objects\Member;

class PNChannelMember
{
    protected string $userId;
    protected mixed $custom;
    protected ?string $type;
    protected ?string $status;

    /**
     * @param string $userId
     * @param mixed $custom
     * @param ?string $type
     * @param ?string $status
     * @return void
     */
    public function __construct(string $userId, mixed $custom = null, ?string $type = null, ?string $status = null)
    {
        $this->userId = $userId;
        $this->custom = $custom;
        $this->type = $type;
        $this->status = $status;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function setCustom(mixed $custom): self
    {
        $this->custom = $custom;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string[] | \StdClass
     */
    public function getCustom()
    {
        return $this->custom;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string[]
     */
    public function toArray()
    {
        $result = [
            'uuid' => [
                'id' => $this->userId
            ]
        ];

        if ($this->custom) {
            $result['custom'] = $this->custom;
        }

        if ($this->type) {
            $result['type'] = $this->type;
        }

        if ($this->status) {
            $result['status'] = $this->status;
        }

        return $result;
    }
}
