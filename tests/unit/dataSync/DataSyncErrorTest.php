<?php

namespace PubNubTests\unit\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Exceptions\PubNubServerException;

/**
 * How a DataSync error body reads through the shared server exception.
 *
 * DataSync answers with a list of errors rather than the single error object the older services
 * use, so the convenience accessor has to recognise that shape too.
 */
class DataSyncErrorTest extends TestCase
{
    private function exception(string $body, int $statusCode = 400): PubNubServerException
    {
        return (new PubNubServerException())->setStatusCode($statusCode)->setRawBody($body);
    }

    public function testTheFirstErrorInTheListIsSurfaced(): void
    {
        $exception = $this->exception(
            '{"errors":[{"errorCode":"DS-0008","message":"Field at \'/payload/dateBought\' with value '
                . 'x is not of expected type \'date\'","path":"/payload/dateBought"}]}'
        );

        $this->assertStringContainsString('not of expected type', (string) $exception->getServerErrorMessage());
        $this->assertSame(400, $exception->getStatusCode());
    }

    /**
     * The code and the path are what a caller acts on, and both stay reachable on the body.
     */
    public function testTheErrorCodeAndPathRemainAvailable(): void
    {
        $body = $this->exception('{"errors":[{"errorCode":"DS-0004","message":"bad","path":"/status"}]}')->getBody();

        $this->assertSame('DS-0004', $body->errors[0]->errorCode);
        $this->assertSame('/status', $body->errors[0]->path);
    }

    /**
     * An Access Manager denial keeps the shape the older services use, and must still read the
     * same way it always has.
     */
    public function testAnAccessManagerDenialStillReadsThroughTheOldShape(): void
    {
        $exception = $this->exception(
            '{"error":true,"status":403,"service":"Access Manager","message":"Forbidden"}',
            403
        );

        $this->assertSame('Forbidden', $exception->getServerErrorMessage());
    }

    public function testAnUnrecognisedBodyReportsNoMessage(): void
    {
        $this->assertNull($this->exception('{"unexpected":"shape"}')->getServerErrorMessage());
    }
}
