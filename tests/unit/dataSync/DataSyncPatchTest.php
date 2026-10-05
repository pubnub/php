<?php

namespace PubNubTests\unit\dataSync;

use PHPUnit\Framework\TestCase;
use PubNub\Models\Consumer\DataSync\PNDataSyncPatch;

/**
 * The RFC-6902 document produced by the patch builder.
 */
class DataSyncPatchTest extends TestCase
{
    public function testBuildsOperationsInOrder(): void
    {
        $patch = (new PNDataSyncPatch())
            ->replace('/status', 'inactive')
            ->add('/payload/color', 'blue')
            ->remove('/payload/obsolete');

        $this->assertSame([
            ['op' => 'replace', 'path' => '/status', 'value' => 'inactive'],
            ['op' => 'add', 'path' => '/payload/color', 'value' => 'blue'],
            ['op' => 'remove', 'path' => '/payload/obsolete'],
        ], $patch->toArray());
    }

    public function testMoveAndCopyCarryFrom(): void
    {
        $patch = (new PNDataSyncPatch())
            ->move('/payload/old', '/payload/new')
            ->copy('/payload/new', '/payload/backup');

        $this->assertSame([
            ['op' => 'move', 'path' => '/payload/new', 'from' => '/payload/old'],
            ['op' => 'copy', 'path' => '/payload/backup', 'from' => '/payload/new'],
        ], $patch->toArray());
    }

    public function testNullValueIsKeptRatherThanDropped(): void
    {
        $patch = (new PNDataSyncPatch())->replace('/payload/color', null);

        $this->assertSame([
            ['op' => 'replace', 'path' => '/payload/color', 'value' => null],
        ], $patch->toArray());
    }

    public function testEncodesAsBareJsonArray(): void
    {
        $patch = (new PNDataSyncPatch())->test('/status', 'active');

        $this->assertSame(
            '[{"op":"test","path":"/status","value":"active"}]',
            json_encode($patch->toArray(), JSON_UNESCAPED_SLASHES)
        );
    }

    public function testCountsOperations(): void
    {
        $patch = (new PNDataSyncPatch())->add('/payload/a', 1)->add('/payload/b', 2);

        $this->assertSame(2, $patch->count());
    }
}
