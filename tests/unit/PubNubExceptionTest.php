<?php

use PHPUnit\Framework\TestCase;
use PubNub\PubNubError;
use PubNub\PubNubException;


class PubNubExceptionTest extends TestCase
{
    /**
     * Only errormsg of PubNubException is set
     *
     * @group exception
     * @group exception-unit
     */
    public function testRebuildMessageCase_1()
    {
        $e = new PubNubException();
        $e->setErrormsg("Ooops...");

        $this->assertEquals("Ooops...", $e->getMessage());
    }

    /**
     * Only error string of PubNubError is set
     *
     * @group exception
     * @group exception-unit
     */
    public function testRebuildMessageCase_2()
    {
        $e = new PubNubException();

        $err = (new PubNubError())
            ->setErrorString("PubNub says that something failed");
        $e->setPubnubError($err);

        $this->assertEquals("PubNub says that something failed", $e->getMessage());
    }

    /**
     * Only message of PubNubError is set
     *
     * @group exception
     * @group exception-unit
     */
    public function testRebuildMessageCase_3()
    {
        $e = new PubNubException();

        $err = (new PubNubError())
            ->setMessage("Requests handler stuck with an unexpected error");
        $e->setPubnubError($err);

        $this->assertEquals("Requests handler stuck with an unexpected error", $e->getMessage());
    }

    /**
     * Both error string and message of PubNubError are set
     *
     * @group exception
     * @group exception-unit
     */
    public function testRebuildMessageCase_4()
    {
        $e = new PubNubException();

        $err = (new PubNubError())
            ->setErrorString("PubNub says that something failed")
            ->setMessage("Requests handler stuck with an unexpected error");
        $e->setPubnubError($err);

        $this->assertEquals("PubNub says that something failed. Original exception: " .
            "\"Requests handler stuck with an unexpected error\"", $e->getMessage());
    }
}
