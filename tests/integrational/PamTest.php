<?php


use PubNub\Models\Consumer\AccessManager\PNAccessManagerGrantResult;

class PamTest extends PubNubTestCase
{
    /**
     * @group pam
     * @group pam-integrational
     */
    public function testGlobalLevel()
    {
        /** @var  PNAccessManagerGrantResult $response */
        $response = $this->pubnub_pam->grant()->read(true)->write(true)->sync();

        $this->assertInstanceOf(PNAccessManagerGrantResult::class, $response);
        $this->assertCount(0, $response->getChannels());
        $this->assertCount(0, $response->getChannelGroups());
        $this->assertTrue($response->isReadEnabled());
        $this->assertTrue($response->isWriteEnabled());
        $this->assertFalse($response->isManageEnabled());
    }
}
