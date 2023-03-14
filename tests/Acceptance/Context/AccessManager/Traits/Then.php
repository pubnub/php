<?php

namespace PubNubTests\Acceptance\Context\AccessManager\Traits;

use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\PNRequestResult;
use PHPUnit\Framework\Assert;

trait Then
{
/**
     * @Then the token contains the authorized UUID :uuid
     */
    public function theTokenContainsTheAuthorizedUuid($uuid)
    {
        Assert::assertEquals($uuid, $this->token->getUuid());
    }

    /**
     * @Then the token contains the TTL :ttl
     */
    public function theTokenContainsTheTtl($ttl)
    {
        Assert::assertEquals($ttl, $this->token->getTtl());
    }

    /**
     * @Then the token has :channel CHANNEL resource access permissions
     */
    public function theTokenHasChannelResourceAccessPermissions($channel)
    {
        $this->resource = $this->token->getChannelResource($channel);
        Assert::assertNotEquals(false, $this->resource);
    }

    /**
     * @Then token resource permission READ
     */
    public function tokenResourcePermissionRead()
    {
        Assert::assertTrue($this->resource->hasRead());
    }

    /**
     * @Then token resource permission WRITE
     */
    public function tokenResourcePermissionWrite()
    {
        Assert::assertTrue($this->resource->hasWrite());
    }

    /**
     * @Then token resource permission GET
     */
    public function tokenResourcePermissionGet()
    {
        Assert::assertTrue($this->resource->hasGet());
    }

    /**
     * @Then token resource permission MANAGE
     */
    public function tokenResourcePermissionManage()
    {
        Assert::assertTrue($this->resource->hasManage());
    }

    /**
     * @Then token resource permission UPDATE
     */
    public function tokenResourcePermissionUpdate()
    {
        Assert::assertTrue($this->resource->hasUpdate());
    }

    /**
     * @Then token resource permission JOIN
     */
    public function tokenResourcePermissionJoin()
    {
        Assert::assertTrue($this->resource->hasJoin());
    }

    /**
     * @Then token resource permission DELETE
     */
    public function tokenResourcePermissionDelete()
    {
        Assert::assertTrue($this->resource->hasDelete());
    }

    /**
     * @Then the token has :channelGroup CHANNEL_GROUP resource access permissions
     */
    public function theTokenHasChannelGroupResourceAccessPermissions($channelGroup)
    {
        $this->resource = $this->token->getChannelGroupResource($channelGroup);
        Assert::assertNotEquals(false, $this->resource);
    }

    /**
     * @Then the token has :uuid UUID resource access permissions
     */
    public function theTokenHasUuidResourceAccessPermissions($uuid)
    {
        $this->resource = $this->token->getUuidResource($uuid);
        Assert::assertNotEquals(false, $this->resource);
    }

    /**
     * @Then the token has :channel CHANNEL pattern access permissions
     */
    public function theTokenHasChannelPatternAccessPermissions($channel)
    {
        $this->pattern = $this->token->getChannelPattern($channel);
        Assert::assertNotEquals(false, $this->pattern);
    }

    /**
     * @Then token pattern permission READ
     */
    public function tokenPatternPermissionRead()
    {
        Assert::assertTrue($this->pattern->hasRead());
    }

    /**
     * @Then token pattern permission WRITE
     */
    public function tokenPatternPermissionWrite()
    {
        Assert::assertTrue($this->pattern->hasWrite());
    }

    /**
     * @Then token pattern permission GET
     */
    public function tokenPatternPermissionGet()
    {
        Assert::assertTrue($this->pattern->hasGet());
    }

    /**
     * @Then token pattern permission MANAGE
     */
    public function tokenPatternPermissionManage()
    {
        Assert::assertTrue($this->pattern->hasManage());
    }

    /**
     * @Then token pattern permission UPDATE
     */
    public function tokenPatternPermissionUpdate()
    {
        Assert::assertTrue($this->pattern->hasUpdate());
    }

    /**
     * @Then token pattern permission JOIN
     */
    public function tokenPatternPermissionJoin()
    {
        Assert::assertTrue($this->pattern->hasJoin());
    }

    /**
     * @Then token pattern permission DELETE
     */
    public function tokenPatternPermissionDelete()
    {
        Assert::assertTrue($this->pattern->hasDelete());
    }

    /**
     * @Then the token has :channelGroup CHANNEL_GROUP pattern access permissions
     */
    public function theTokenHasChannelGroupPatternAccessPermissions($channelGroup)
    {
        $this->pattern = $this->token->getChannelGroupPattern($channelGroup);
        Assert::assertNotEquals(false, $this->pattern);
    }

    /**
     * @Then the token has :uuid UUID pattern access permissions
     */
    public function theTokenHasUuidPatternAccessPermissions($uuid)
    {
        $this->pattern = $this->token->getUuidPattern($uuid);
        Assert::assertNotEquals(false, $this->pattern);
    }

    /**
     * @Then the token does not contain an authorized uuid
     */
    public function theTokenDoesNotContainAnAuthorizedUuid()
    {
        Assert::assertNull($this->token->getUuid());
    }

    /**
     * @Then the error status code is :statusCode
     */
    public function theErrorStatusCodeIs($statusCode)
    {
        Assert::assertEquals($statusCode, $this->error->getStatusCode());
    }

    /**
     * @Then the error message is :errorMessage
     */
    public function theErrorMessageIs($errorMessage)
    {
        Assert::assertEquals($errorMessage, $this->error->getServerErrorMessage());
    }

    /**
     * @Then the error source is :source
     */
    public function theErrorSourceIs($source)
    {
        Assert::assertEquals($source, $this->error->getServerErrorSource());
    }

    /**
     * @Then the error detail message is :detailMessage
     */
    public function theErrorDetailMessageIs($detailMessage)
    {
        Assert::assertEquals($detailMessage, $this->error->getServerErrorDetails()->message);
    }

    /**
     * @Then the error detail location is :detailLocation
     */
    public function theErrorDetailLocationIs($detailLocation)
    {
        Assert::assertEquals($detailLocation, $this->error->getServerErrorDetails()->location);
    }

    /**
     * @Then the error detail location type is :detailLocationType
     */
    public function theErrorDetailLocationTypeIs($detailLocationType)
    {
        Assert::assertEquals($detailLocationType, $this->error->getServerErrorDetails()->locationType);
    }

    /**
     * @Then the parsed token output contains the authorized UUID :uuid
     */
    public function theParsedTokenOutputContainsTheAuthorizedUuid($uuid)
    {
        Assert::assertEquals($uuid, $this->token->getUuid());
    }

    /**
     * @Then I get confirmation that token has been revoked
     */
    public function iGetConfirmationThatTokenHasBeenRevoked()
    {
        Assert::assertEquals(PNRequestResult::class, get_class($this->result));
        Assert::assertEquals('Success'::class, $this->result->getMessage());
    }

    /**
     * @Then an error is returned
     */
    public function anErrorIsReturned()
    {
        Assert::assertEquals(PubNubServerException::class, get_class($this->error));
    }

    /**
     * @Then the error detail message is not empty
     */
    public function theErrorDetailMessageIsNotEmpty()
    {
        Assert::assertNotEmpty($this->error->getServerErrorDetails()->message);
    }

    /**
     * @Then the error service is :service
     */
    public function theErrorServiceIs($service)
    {
        Assert::assertEquals($service, $this->error->getBody()->service);
    }
}
