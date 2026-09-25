<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * A single RFC-6902 JSON Patch operation.
 */
class PNDataSyncPatchOperation
{
    protected string $op;

    protected string $path;

    /** @var mixed */
    protected $value;

    protected bool $hasValue;

    protected ?string $from;

    /**
     * @param mixed $value
     */
    public function __construct(string $op, string $path, $value = null, bool $hasValue = false, ?string $from = null)
    {
        $this->op = $op;
        $this->path = $path;
        $this->value = $value;
        $this->hasValue = $hasValue;
        $this->from = $from;
    }

    public function getOp(): string
    {
        return $this->op;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $operation = [
            'op' => $this->op,
            'path' => $this->path,
        ];

        // A null value is meaningful in JSON Patch, so presence is tracked separately from the value itself.
        if ($this->hasValue) {
            $operation['value'] = $this->value;
        }

        if ($this->from !== null) {
            $operation['from'] = $this->from;
        }

        return $operation;
    }
}
