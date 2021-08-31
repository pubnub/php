<?php

namespace PubNub\Enums;


class PNPushType
{
    const APNS = "apns";
    const APNS2 = "apns2";
    const MPNS = "mpns";
    const GCM = "gcm";
    // FCM is the new name of GCM. Pubnub still requires 'gcm'
    const FCM = "gcm";
}