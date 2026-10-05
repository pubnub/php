<?php

namespace PubNub\Endpoints\DataSync\Traits;

use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;
use PubNub\PubNubUtil;

/**
 * RFC-6902 JSON Patch support for the DataSync update operations.
 *
 * Unlike the other write operations the patch body is a bare JSON array rather than a
 * {"data": ...} envelope, and it travels with the application/json-patch+json content type.
 */
trait HasJsonPatch
{
    /** @var PNDataSyncPatch|array<int, mixed>|null */
    protected $patch = null;

    /**
     * Accepts either the fluent builder or a raw RFC-6902 array for callers who prefer one.
     *
     * @param PNDataSyncPatch|array<int, mixed> $patch
     * @return $this
     */
    public function patch($patch): static
    {
        $this->patch = $patch;
        return $this;
    }

    /**
     * @return array<int, mixed>
     */
    protected function patchOperations(): array
    {
        if ($this->patch instanceof PNDataSyncPatch) {
            return $this->patch->toArray();
        }

        return is_array($this->patch) ? $this->patch : [];
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validatePatch(): void
    {
        $operations = $this->patchOperations();

        if (count($operations) === 0) {
            throw new PubNubValidationException("patch operations missing");
        }

        $requiresValue = [PNDataSyncPatch::OP_ADD, PNDataSyncPatch::OP_REPLACE, PNDataSyncPatch::OP_TEST];
        $requiresFrom = [PNDataSyncPatch::OP_MOVE, PNDataSyncPatch::OP_COPY];

        foreach ($operations as $index => $operation) {
            if (!is_array($operation)) {
                throw new PubNubValidationException("patch operation #$index must be an array");
            }

            if (!array_key_exists('op', $operation) || !array_key_exists('path', $operation)) {
                throw new PubNubValidationException("patch operation #$index requires both op and path");
            }

            $op = $operation['op'];

            if (in_array($op, $requiresValue, true) && !array_key_exists('value', $operation)) {
                throw new PubNubValidationException("patch operation \"$op\" requires a value");
            }

            if (in_array($op, $requiresFrom, true) && !array_key_exists('from', $operation)) {
                throw new PubNubValidationException("patch operation \"$op\" requires a from path");
            }
        }
    }

    protected function buildPatchData(): string
    {
        return PubNubUtil::writeValueAsString($this->patchOperations());
    }
}
