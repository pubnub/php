<?php

namespace PubNub\Enums;

class PNPushType
{

    public const APNS2 = "apns2";
    public const FCM = "fcm";

    /**
     * @deprecated Use APNS2 instead. APNS will be removed in a future version.
     */
    public const APNS = "apns";

    /**
     * @deprecated Use FCM instead. GCM will be removed in a future version.
     */
    public const GCM = "gcm";

    public static function all()
    {
        return [
            self::APNS,
            self::APNS2,
            self::GCM,
            self::FCM
        ];
    }
}
