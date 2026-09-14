<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Fluent builder for an RFC-6902 JSON Patch document.
 *
 * Paths are JSON Pointers. Top-level record fields are addressed directly ("/status") while the
 * user-defined payload lives one level down ("/payload/color").
 *
 * Example:
 *
 *     $patch = (new PNDataSyncPatch())
 *         ->replace('/status', 'inactive')
 *         ->add('/payload/color', 'blue');
 */
class PNDataSyncPatch
{
    public const OP_ADD = "add";
    public const OP_REMOVE = "remove";
    public const OP_REPLACE = "replace";
    public const OP_MOVE = "move";
    public const OP_COPY = "copy";
    public const OP_TEST = "test";

    /** @var PNDataSyncPatchOperation[] */
    protected array $operations = [];

    /**
     * @param mixed $value
     * @return $this
     */
    public function add(string $path, $value): static
    {
        $this->operations[] = new PNDataSyncPatchOperation(self::OP_ADD, $path, $value, true);
        return $this;
    }

    /**
     * @return $this
     */
    public function remove(string $path): static
    {
        $this->operations[] = new PNDataSyncPatchOperation(self::OP_REMOVE, $path);
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function replace(string $path, $value): static
    {
        $this->operations[] = new PNDataSyncPatchOperation(self::OP_REPLACE, $path, $value, true);
        return $this;
    }

    /**
     * @return $this
     */
    public function move(string $from, string $path): static
    {
        $this->operations[] = new PNDataSyncPatchOperation(self::OP_MOVE, $path, null, false, $from);
        return $this;
    }

    /**
     * @return $this
     */
    public function copy(string $from, string $path): static
    {
        $this->operations[] = new PNDataSyncPatchOperation(self::OP_COPY, $path, null, false, $from);
        return $this;
    }

    /**
     * Asserts the current value at $path before the rest of the patch is applied.
     *
     * @param mixed $value
     * @return $this
     */
    public function test(string $path, $value): static
    {
        $this->operations[] = new PNDataSyncPatchOperation(self::OP_TEST, $path, $value, true);
        return $this;
    }

    /**
     * @return PNDataSyncPatchOperation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    public function count(): int
    {
        return count($this->operations);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->operations as $operation) {
            $result[] = $operation->toArray();
        }

        return $result;
    }
}
