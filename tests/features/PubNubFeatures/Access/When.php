<?php

namespace PubNubFeatures\Access;

use PubNub\Exceptions\PubNubServerException;
use PubNub\PubNub;

trait When
{
    /**
     * @When I grant a token specifying those permissions
     */
    public function iGrantATokenSpecifyingThosePermissions()
    {
        /** @var GrantToken */
        $grantToken = $this->pubnub->grantToken();
        $token = $grantToken->ttl($this->context['ttl'])
            ->authorizedUuid($this->context['authorizedUuid'] ?? null)
            ->addChannelResources($this->context['resource']['channel'] ?? null)
            ->addChannelGroupResources($this->context['resource']['channelGroup'] ?? null)
            ->addUuidResources($this->context['resource']['uuid'] ?? null)
            ->addChannelPatterns($this->context['pattern']['channel'] ?? null)
            ->addChannelGroupPatterns($this->context['pattern']['channelGroup'] ?? null)
            ->addUuidPatterns($this->context['pattern']['uuid'] ?? null)
            ->sync();
        $this->token = $this->pubnub->parseToken($token);
    }

    /**
     * @When I attempt to grant a token specifying those permissions
     */
    public function iAttemptToGrantATokenSpecifyingThosePermissions()
    {
        $this->error = false;
        try {
            /** @var GrantToken */
            $grantToken = $this->pubnub->grantToken();
            $grantToken->ttl($this->context['ttl'])
                ->authorizedUuid($this->context['authorizedUuid'] ?? null)
                ->addChannelResources($this->context['resource']['channel'] ?? null)
                ->addChannelGroupResources($this->context['resource']['channelGroup'] ?? null)
                ->addUuidResources($this->context['resource']['uuid'] ?? null)
                ->addChannelPatterns($this->context['pattern']['channel'] ?? null)
                ->addChannelGroupPatterns($this->context['pattern']['channelGroup'] ?? null)
                ->addUuidPatterns($this->context['pattern']['uuid'] ?? null)
                ->sync();
        } catch (PubNubServerException $exception) {
            $this->error = $exception;
            return true;
        }

        return false;
    }

    /**
     * @When I parse the token
     */
    public function iParseTheToken()
    {
        $this->pubnub = new PubNub($this->pnConfig);
        $this->token = $this->pubnub->parseToken($this->token);
    }

    /**
     * @When I revoke a token
     */
    public function iRevokeAToken()
    {
        try {
            $this->result = $this->pubnub->revokeToken()
                ->token($this->token)
                ->sync();
        } catch (PubNubServerException $exception) {
            $this->error = $exception;
        }
    }
}
