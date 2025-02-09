<?php

namespace Tests\Integrational\Objects\Channel;

use PubNubTestCase;
use PubNub\Exceptions\PubNubServerException;

class SetChannelMetadataEndpointTest extends PubNubTestCase
{
    public function testAddMetadataToChannel()
    {
        $response = $this->pubnub_pam->setChannelMetadata()
            ->channel("ch")
            ->meta([
                "id" => "ch",
                "name" => "ch_name",
                "description" => "ch_description",
                "custom" => [
                    "a" => "aa",
                    "b" => "bb"
                ]
            ])
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertEquals("ch", $response->getId());
        $this->assertEquals("ch_name", $response->getName());
        $this->assertEquals("ch_description", $response->getDescription());

        $custom = $response->getCustom();

        $this->assertNotEmpty($custom);
        $this->assertEquals("aa", $custom->a);
        $this->assertEquals("bb", $custom->b);
    }

    public function testIfMatchesEtagWriteProtection(): void
    {
        $response = $this->pubnub->setChannelMetadata()
            ->channel("ch")
            ->meta([
                "id" => "ch",
                "name" => "ch_name",
                "description" => "ch_description",
                "custom" => [
                    "a" => "aa",
                    "b" => "bb"
                ]
            ])
            ->sync();

        $this->assertNotEmpty($response);
        $this->assertEquals("ch", $response->getId());
        $this->assertNotEmpty($response->getETag());
        $eTag = $response->getETag();

        $overwrite = $this->pubnub->setChannelMetadata()
            ->channel("ch")
            ->meta(["id" => "ch",
                "name" => "edited_ch_name",
                "description" => "edited_ch_description",
                "custom" => [
                    "c" => "cc",
                    "d" => "cc"
                ]])
            ->sync();

            $this->assertNotEmpty($overwrite);
        $this->assertNotEmpty($overwrite->getETag());
        $this->assertNotEquals($eTag, $overwrite->getETag());

        try {
            $response = $this->pubnub->setChannelMetadata()
                ->channel("ch")
                ->meta([
                    "id" => "ch",
                    "name" => "Channel",
                    "description" => "Some testing is happening",
                    "custom" => [
                        "a" => "aa",
                        "b" => "bb"
                    ]
                ])
                ->ifMatchesETag($eTag)
                ->sync();
        } catch (PubNubServerException $exception) {
            $this->assertEquals("412", $exception->getStatusCode());
            $this->assertNotEmpty($exception->getServerErrorMessage());
        }
    }
}
