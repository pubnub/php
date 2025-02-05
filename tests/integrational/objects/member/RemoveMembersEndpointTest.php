<?php

namespace Tests\Integrational\Objects\Members;

use PubNubTestCase;

class RemoveMembersEndpointTest extends PubNubTestCase
{
    public function testRemoveMembersForChannel()
    {
        $this->pubnub_pam->setMembers()
            ->channel("ch")
            ->uuids(["uuid", "uuid1", "uuid2"])
            ->custom([
                "a" => "aa",
                "b" => "bb",
            ])
            ->sync();

        $response = $this->pubnub_pam->removeMembers()
            ->channel("ch")
            ->uuids(["uuid", "uuid1", "uuid2"])
            ->sync();

        $this->assertNotEmpty($response);
        $data = $response->getData();
        $this->assertEmpty($data);
    }
}
