<?php

namespace PubNub\Endpoints\DataSync\Traits;

/**
 * The user-defined payload and the free-form status string that every DataSync record accepts.
 */
trait HasPayloadAndStatus
{
    protected ?string $status = null;

    /** @var array<string, mixed>|null */
    protected ?array $payload = null;

    /**
     * @return $this
     */
    public function status(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param array<string, mixed> $payload
     * @return $this
     */
    public function payload(array $payload): static
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Appends the optional status and payload keys, leaving them out entirely when unset.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function withPayloadAndStatus(array $data): array
    {
        if ($this->status !== null && $this->status !== '') {
            $data['status'] = $this->status;
        }

        if ($this->payload !== null) {
            // An empty PHP array encodes as [] rather than {}, which the server rejects.
            $data['payload'] = (object) $this->payload;
        }

        return $data;
    }
}
