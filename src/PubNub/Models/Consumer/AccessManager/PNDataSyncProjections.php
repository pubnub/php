<?php

namespace PubNub\Models\Consumer\AccessManager;

/**
 * The DataSync field projections a grant token carries, read back out of the token's
 * meta["pn-projections"] section.
 *
 * A projection names the subset of a record's fields the token holder is allowed to see;
 * "__default__" is the base projection.
 */
class PNDataSyncProjections
{
    private PNDataSyncProjectionScope $resources;

    private PNDataSyncProjectionScope $patterns;

    public function __construct(
        ?PNDataSyncProjectionScope $resources = null,
        ?PNDataSyncProjectionScope $patterns = null
    ) {
        $this->resources = $resources ?? new PNDataSyncProjectionScope();
        $this->patterns = $patterns ?? new PNDataSyncProjectionScope();
    }

    /**
     * Projections keyed by exact resource identifier.
     */
    public function getResources(): PNDataSyncProjectionScope
    {
        return $this->resources;
    }

    /**
     * Projections keyed by resource identifier pattern.
     */
    public function getPatterns(): PNDataSyncProjectionScope
    {
        return $this->patterns;
    }

    public function isEmpty(): bool
    {
        return $this->resources->isEmpty() && $this->patterns->isEmpty();
    }

    /**
     * @return array<string, array<string, array<string, string>>>
     */
    public function toArray(): array
    {
        return [
            'resources' => $this->resources->toArray(),
            'patterns' => $this->patterns->toArray(),
        ];
    }

    /**
     * @param mixed $projections
     */
    public static function fromArray($projections): self
    {
        if (!is_array($projections)) {
            return new self();
        }

        return new self(
            PNDataSyncProjectionScope::fromArray($projections['res'] ?? null),
            PNDataSyncProjectionScope::fromArray($projections['pat'] ?? null)
        );
    }
}
