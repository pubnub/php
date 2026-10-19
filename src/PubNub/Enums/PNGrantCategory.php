<?php

namespace PubNub\Enums;

/**
 * App Context resource types that a category grant applies to as a whole.
 */
class PNGrantCategory
{
    /** Listing all channel metadata on the subscribe key. */
    public const CHANNELS = "channels";

    /** Listing all uuid metadata on the subscribe key. */
    public const UUIDS = "uuids";

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::CHANNELS,
            self::UUIDS,
        ];
    }
}
