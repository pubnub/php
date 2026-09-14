<?php

namespace PubNub\Models\Consumer\DataSync;

/**
 * Null-safe accessors shared by the DataSync models.
 *
 * Every field of a DataSync response is optional in practice: projections can strip fields a
 * caller is not allowed to see, so reading them defensively is the norm rather than the exception.
 */
final class PNDataSyncValue
{
    /**
     * @param array<array-key, mixed> $payload
     */
    public static function stringOrNull(array $payload, string $key): ?string
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        return (string) $payload[$key];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    public static function intOrNull(array $payload, string $key): ?int
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        return (int) $payload[$key];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    public static function boolOrNull(array $payload, string $key): ?bool
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        return (bool) $payload[$key];
    }

    /**
     * @param array<array-key, mixed> $payload
     * @return array<array-key, mixed>|null
     */
    public static function arrayOrNull(array $payload, string $key): ?array
    {
        if (!array_key_exists($key, $payload) || !is_array($payload[$key])) {
            return null;
        }

        return $payload[$key];
    }
}
