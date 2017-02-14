<?php



class AddChannelToChannelGroupTest extends PubNubTestCase
{
    /**
     * @group cg
     * @group cg-integrational
     */
    public function xtestSingleChannel()
    {
        $ch = "channel-groups-native-ch";
        $gr = "channel-groups-native-cg";

        // cleanup
        $response = $this->pubnub->addChannelToChannelGroup()->channels($ch)->group($gr)->sync();

        $this->assertGreaterThan(14838270462685247, $response->getTimetoken());
    }
}
