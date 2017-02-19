<?php

namespace PubNub\Exceptions;

/**
 * Class PubNubException
 * @package PubNub\Exceptions
 *
 * Should be extended by following exception types:
 *
 * - PubNubValidationException (like 'channel missing', 'subscribe key missing')
 * - PubNubRequestException (like 'network error', 'request timeout')
 * - PubNubServerException (like 400, 403, 500, etc.)
 */
abstract class PubNubException extends \Exception
{
}
