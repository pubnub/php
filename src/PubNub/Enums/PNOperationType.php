<?php

namespace PubNub\Enums;


class PNOperationType
{
    const PNSubscribeOperation = 1;
    const PNUnsubscribeOperation = 2;

    const PNPublishOperation = 3;

    const PNHistoryOperation = 4;
    const PNFetchMessagesOperation = 5;

    const PNWhereNowOperation = 6;

    const PNHeartbeatOperation = 7;
    const PNSetStateOperation = 8;
    const PNAddChannelsToGroupOperation = 9;
    const PNRemoveChannelsFromGroupOperation = 10;
    const PNChannelGroupsOperation = 11;
    const PNRemoveGroupOperation = 12;
    const PNChannelsForGroupOperation = 13;
    const PNPushNotificationEnabledChannelsOperation = 14;
    const PNAddPushNotificationsOnChannelsOperation = 15;
    const PNRemovePushNotificationsFromChannelsOperation = 16;
    const PNRemoveAllPushNotificationsOperation = 17;
    const PNTimeOperation = 18;

    // CREATED
    const PNHereNowOperation = 19;
    const PNGetState = 20;
    const PNAccessManagerAudit = 21;
    const PNAccessManagerGrant = 22;
    const PNAccessManagerRevoke = 23;
    const PNHistoryDeleteOperation = 24;
}