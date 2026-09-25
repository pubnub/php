<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * What every DataSync record carries, whichever family it belongs to.
 *
 * The families differ in how they say what they refer to - an entity by its class, a relationship
 * by the two records it links, a membership by its channel and user - but identity, status,
 * payload and the concurrency fields are common to all of them, and that is the part the result
 * objects work with.
 */
interface PNDataSyncRecord
{
    public function getId(): ?string;

    public function getStatus(): ?string;

    /**
     * @return array<string, mixed>|null
     */
    public function getPayload(): ?array;

    public function getCreatedAt(): ?string;

    public function getUpdatedAt(): ?string;

    /**
     * Fingerprint to hand back through ifMatchesETag() on the next write.
     */
    public function getETag(): ?string;

    public function getExpiresAt(): ?string;

    public function __toString(): string;
}
