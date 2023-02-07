<?php

namespace PubNubTests\Features\Context\AccessManager\Traits;

use PubNub\PubNub;
use PubNubTests\Features\Context\PNContextHelper;

trait Given
{
    /**
     * @Given the TTL :ttl
     */
    public function theTtl($ttl)
    {
        $this->context['ttl'] = (int)$ttl;
    }

    /**
     * @Given the authorized UUID :uuid
     */
    public function theAuthorizedUuid($uuid)
    {
        $this->context['authorizedUuid'] = $uuid;
    }

    /**
     * @Given the :channel CHANNEL resource access permissions
     */
    public function theChannelResourceAccessPermissions($channel)
    {
        $this->context['resource']['channel'][$channel] = [];
        $this->resource = &$this->context['resource']['channel'][$channel];
    }

    /**
     * @Given the :channelGroup CHANNEL_GROUP resource access permissions
     */
    public function theChannelGroupResourceAccessPermissions($channelGroup)
    {
        $this->context['resource']['channelGroup'][$channelGroup] = [];
        $this->resource = &$this->context['resource']['channelGroup'][$channelGroup];
    }

    /**
     * @Given the :uuid UUID resource access permissions
     */
    public function theUuidResourceAccessPermissions($uuid)
    {
        $this->context['resource']['uuid'][$uuid] = [];
        $this->resource = &$this->context['resource']['uuid'][$uuid];
    }

    /**
     * @Given grant resource permission READ
     */
    public function grantResourcePermissionRead()
    {
        $this->resource['read'] = true;
    }

    /**
     * @Given grant resource permission WRITE
     */
    public function grantResourcePermissionWrite()
    {
        $this->resource['write'] = true;
    }

    /**
     * @Given grant resource permission GET
     */
    public function grantResourcePermissionGet()
    {
        $this->resource['get'] = true;
    }

    /**
     * @Given grant resource permission MANAGE
     */
    public function grantResourcePermissionManage()
    {
        $this->resource['manage'] = true;
    }

    /**
     * @Given grant resource permission UPDATE
     */
    public function grantResourcePermissionUpdate()
    {
        $this->resource['update'] = true;
    }

    /**
     * @Given grant resource permission JOIN
     */
    public function grantResourcePermissionJoin()
    {
        $this->resource['join'] = true;
    }

    /**
     * @Given grant resource permission DELETE
     */
    public function grantResourcePermissionDelete()
    {
        $this->resource['delete'] = true;
    }

    /**
     * @Given I have a keyset with access manager enabled
     */
    public function iHaveAKeysetWithAccessManagerEnabled()
    {
        $this->pubnub = new PubNub($this->pnConfig);
    }

    /**
     * @Given the :channel CHANNEL pattern access permissions
     */
    public function theChannelPatternAccessPermissions($channel)
    {
        $this->context['pattern']['channel'][$channel] = [];
        $this->resource = &$this->context['pattern']['channel'][$channel];
    }

    /**
     * @Given the :channelGroup CHANNEL_GROUP pattern access permissions
     */
    public function theChannelGroupPatternAccessPermissions($channelGroup)
    {
        $this->context['pattern']['channelGroup'][$channelGroup] = [];
        $this->resource = &$this->context['pattern']['channelGroup'][$channelGroup];
    }

    /**
     * @Given the :uuid UUID pattern access permissions
     */
    public function theUuidPatternAccessPermissions($uuid)
    {
        $this->context['pattern']['uuid'][$uuid] = [];
        $this->resource = &$this->context['pattern']['uuid'][$uuid];
    }

    /**
     * @Given grant pattern permission READ
     */
    public function grantPatternPermissionRead()
    {
        $this->resource['read'] = true;
    }

    /**
     * @Given grant pattern permission WRITE
     */
    public function grantPatternPermissionWrite()
    {
        $this->resource['write'] = true;
    }

    /**
     * @Given grant pattern permission GET
     */
    public function grantPatternPermissionGet()
    {
        $this->resource['get'] = true;
    }

    /**
     * @Given grant pattern permission MANAGE
     */
    public function grantPatternPermissionManage()
    {
        $this->resource['manage'] = true;
    }

    /**
     * @Given grant pattern permission UPDATE
     */
    public function grantPatternPermissionUpdate()
    {
        $this->resource['update'] = true;
    }

    /**
     * @Given grant pattern permission JOIN
     */
    public function grantPatternPermissionJoin()
    {
        $this->resource['join'] = true;
    }

    /**
     * @Given grant pattern permission DELETE
     */
    public function grantPatternPermissionDelete()
    {
        $this->resource['delete'] = true;
    }

    /**
     * @Given deny resource permission GET
     */
    public function denyResourcePermissionGet()
    {
        $this->resource['read'] = false;
    }

    /**
     * @Given I have a known token containing an authorized UUID
     */
    public function iHaveAKnownTokenContainingAnAuthorizedUuid()
    {
        $this->token = 'qEF2AkF0GmGEQqhDdHRsGDxDcmVzpURjaGFuoWljaGFubmVsLTEY70NncnChb2NoYW5uZWxfZ3JvdXAtMQVDdXNyoENzcGO'
            . 'gRHV1aWShZnV1aWQtMRhoQ3BhdKVEY2hhbqFtXmNoYW5uZWwtXFMqJBjvQ2dycKF0XjpjaGFubmVsX2dyb3VwLVxTKiQFQ3VzcqBDc3B'
            . 'joER1dWlkoWpedXVpZC1cUyokGGhEbWV0YaBEdXVpZHR0ZXN0LWF1dGhvcml6ZWQtdXVpZENzaWdYIDuyE8oo74oI9LVWTwp_OBrvirh'
            . 'srR88KgoMPmQT7Cqo';
    }

    /**
     * @Given I have a known token containing UUID resource permissions
     */
    public function iHaveAKnownTokenContainingUuidResourcePermissions()
    {
        $this->token = 'qEF2AkF0GmGEQqhDdHRsGDxDcmVzpURjaGFuoWljaGFubmVsLTEY70NncnChb2NoYW5uZWxfZ3JvdXAtMQVDdXNyoENzcGO'
            . 'gRHV1aWShZnV1aWQtMRhoQ3BhdKVEY2hhbqFtXmNoYW5uZWwtXFMqJBjvQ2dycKF0XjpjaGFubmVsX2dyb3VwLVxTKiQFQ3VzcqBDc3B'
            . 'joER1dWlkoWpedXVpZC1cUyokGGhEbWV0YaBEdXVpZHR0ZXN0LWF1dGhvcml6ZWQtdXVpZENzaWdYIDuyE8oo74oI9LVWTwp_OBrvirh'
            . 'srR88KgoMPmQT7Cqo';
    }

    /**
     * @Given I have a known token containing UUID pattern Permissions
     */
    public function iHaveAKnownTokenContainingUuidPatternPermissions()
    {
        $this->token = 'qEF2AkF0GmGEQqhDdHRsGDxDcmVzpURjaGFuoWljaGFubmVsLTEY70NncnChb2NoYW5uZWxfZ3JvdXAtMQVDdXNyoENzcGO'
            . 'gRHV1aWShZnV1aWQtMRhoQ3BhdKVEY2hhbqFtXmNoYW5uZWwtXFMqJBjvQ2dycKF0XjpjaGFubmVsX2dyb3VwLVxTKiQFQ3VzcqBDc3B'
            . 'joER1dWlkoWpedXVpZC1cUyokGGhEbWV0YaBEdXVpZHR0ZXN0LWF1dGhvcml6ZWQtdXVpZENzaWdYIDuyE8oo74oI9LVWTwp_OBrvirh'
            . 'srR88KgoMPmQT7Cqo';
    }

    /**
     * @Given a token
     */
    public function aToken()
    {
        $this->token = PNContextHelper::PAM_TOKEN_WITH_ALL_PERMS_GRANTED;
        return true;
    }

    /**
     * @Given the token string :token
     */
    public function theTokenString($token)
    {
        $this->token = $token;
    }
}
