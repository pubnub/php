<?php

namespace PubNubTests\Acceptance\Context\AccessManager;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use PubNubTests\Acceptance\Context\AccessManager\Traits;
use PubNubTests\Acceptance\Context\PubNubContext;

/**
 * Defines application features from the specific context.
 */
class GrantTokenContext extends PubNubContext implements Context
{
    use Traits\Given;
    use Traits\When;
    use Traits\Then;

    protected $resource = null;
    protected $pattern = null;
    protected $token;

    /**
     * @Given I have a keyset with access manager enabled - without secret key
     */
    public function iHaveAKeysetWithAccessManagerEnabledWithoutSecretKey()
    {
        throw new PendingException();
    }

    /**
     * @Given a valid token with permissions to publish with channel :arg1
     */
    public function aValidTokenWithPermissionsToPublishWithChannel($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When I publish a message using that auth token with channel :arg1
     */
    public function iPublishAMessageUsingThatAuthTokenWithChannel($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then the result is successful
     */
    public function theResultIsSuccessful()
    {
        throw new PendingException();
    }

    /**
     * @Given an expired token with permissions to publish with channel :arg1
     */
    public function anExpiredTokenWithPermissionsToPublishWithChannel($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When I attempt to publish a message using that auth token with channel :arg1
     */
    public function iAttemptToPublishAMessageUsingThatAuthTokenWithChannel($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then an auth error is returned
     */
    public function anAuthErrorIsReturned()
    {
        throw new PendingException();
    }

    /**
     * @Then the auth error message is :arg1
     */
    public function theAuthErrorMessageIs($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given The SDK is configured with an AuthKey representing an access Token
     */
    public function theSdkIsConfiguredWithAnAuthkeyRepresentingAnAccessToken()
    {
        throw new PendingException();
    }

    /**
     * @When I publish a messages
     */
    public function iPublishAMessages()
    {
        throw new PendingException();
    }

    /**
     * @Then The request uses the specified access token for authorization
     */
    public function theRequestUsesTheSpecifiedAccessTokenForAuthorization()
    {
        throw new PendingException();
    }

    /**
     * @Given I have associated an access token with the SDK instance
     */
    public function iHaveAssociatedAnAccessTokenWithTheSdkInstance()
    {
        throw new PendingException();
    }

    /**
     * @When I request the current access token via the getToken operation
     */
    public function iRequestTheCurrentAccessTokenViaTheGettokenOperation()
    {
        throw new PendingException();
    }

    /**
     * @Then The token returned matches
     */
    public function theTokenReturnedMatches()
    {
        throw new PendingException();
    }

    /**
     * @Given I have not associated an access token with the SDK instance
     */
    public function iHaveNotAssociatedAnAccessTokenWithTheSdkInstance()
    {
        throw new PendingException();
    }

    /**
     * @Then A non-error response indicating no token is associated will be returned
     */
    public function aNonErrorResponseIndicatingNoTokenIsAssociatedWillBeReturned()
    {
        throw new PendingException();
    }

    /**
     * @Given I have provided an access token to the SDK via the setToken operation
     */
    public function iHaveProvidedAnAccessTokenToTheSdkViaTheSettokenOperation()
    {
        throw new PendingException();
    }

    /**
     * @Given The SDK is configured with an AuthKey representing an acess Token
     */
    public function theSdkIsConfiguredWithAnAuthkeyRepresentingAnAcessToken()
    {
        throw new PendingException();
    }

    /**
     * @Given I provide an access token to the SDK via the setToken operation
     */
    public function iProvideAnAccessTokenToTheSdkViaTheSettokenOperation()
    {
        throw new PendingException();
    }

    /**
     * @Given I indicated to the SDK to not use a token.
     */
    public function iIndicatedToTheSdkToNotUseAToken()
    {
        throw new PendingException();
    }

    /**
     * @Then The request does not include an access token
     */
    public function theRequestDoesNotIncludeAnAccessToken()
    {
        throw new PendingException();
    }
}
