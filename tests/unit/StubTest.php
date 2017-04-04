<?php

use PHPUnit\Framework\TestCase;
use Tests\Helpers\Stub;


class StubTest extends TestCase
{
    /**
     * @group stub
     */
    public function testSimpleStub()
    {
        $stub = (new Stub(""))->withQuery([
            'uuid' => "blah",
            'pnsdk' => '123'
        ]);
        $this->assertTrue($stub->isQueryMatch("uuid=blah&pnsdk=123"));
    }

    /**
     * @group stub
     */
    public function testAny()
    {
        $stub = (new Stub(""))->withQuery([
            'uuid' => Stub::ANY,
            'pnsdk' => '123'
        ]);

        $this->assertTrue($stub->isQueryMatch("uuid=blahblah123&pnsdk=123"));
    }

    /**
     * @group stub
     */
    public function testExtraExpectedArgument()
    {
        $stub = (new Stub(""))->withQuery([
            'id' => '15',
            'uuid' => 'blah',
            'pnsdk' => '123'
        ]);

        $this->assertFalse($stub->isQueryMatch("uuid=blah&pnsdk=123"));
    }

    /**
     * @group stub
     */
    public function testExtraActualArgument()
    {
        $stub = (new Stub(""))->withQuery([
            'uuid' => 'blah',
            'pnsdk' => '123'
        ]);

        $this->assertFalse($stub->isQueryMatch("uuid=blah&pnsdk=123&id=321"));
    }
}
