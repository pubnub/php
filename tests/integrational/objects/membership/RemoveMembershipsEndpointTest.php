<?php

namespace Tests\Integrational\Objects\Memberships;

use PubNubTestCase;


class RemoveMembershipsEndpointTest extends PubNubTestCase
{
    public function testGetMemberships()
    {
        $this->pubnub_pam->setMemberships()
            ->uuid("uuid")
            ->channels(["ch", "ch1", "ch2"])
            ->custom([
                "a" => "aa",
                "b" => "bb",
            ])
            ->sync();

        $response = $this->pubnub_pam->removeMemberships()
            ->uuid("uuid")
            ->channels(["ch", "ch1", "ch2"])
            ->sync();

        $this->assertNotEmpty($response);
        $data = $response->getData();
        $this->assertEmpty($data);
    }
}
