<?php

namespace PubNub\Models\Consumer\Objects\Membership;

class PNChannelMembership
{
    protected string $channel;
    protected mixed $custom;
    protected ?string $type;
    protected ?string $status;

    /**
     *
     * @param string $channel
     * @param mixed $custom
     * @param ?string $type
     * @param ?string $status
     * @return void
     */
    public function __construct(string $channel, mixed $custom = null, ?string $type = null, ?string $status = null)
    {
        $this->channel = $channel;
        $this->custom = $custom;
        $this->type = $type;
        $this->status = $status;
    }

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;
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

    public function getchannel(): string
    {
        return $this->channel;
    }

    /**
     * @return string[] | \StdClass
     */
    public function getCustom(): mixed
    {
        return $this->custom;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        $result = [
            'channel' => [
                'id' => $this->channel
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
