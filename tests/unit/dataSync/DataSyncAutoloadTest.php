<?php

namespace PubNubTests\unit\dataSync;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Both supported ways of installing the SDK resolve a class from its namespace rather than from a
 * list: Composer's PSR-4 map, and the src/autoloader.php the README documents for installs without
 * Composer. Neither enumerates classes, so a file whose namespace does not match its path is
 * invisible to both and the omission only shows up at runtime.
 */
class DataSyncAutoloadTest extends TestCase
{
    /**
     * Where the DataSync classes live, relative to src/.
     */
    private const SOURCE_PATHS = [
        'PubNub/DataSync.php',
        'PubNub/Endpoints/DataSync',
        'PubNub/Models/Consumer/DataSync',
        'PubNub/Models/Consumer/AccessManager/PNDataSyncProjections.php',
        'PubNub/Models/Consumer/AccessManager/PNDataSyncProjectionScope.php',
    ];

    public function testEveryDataSyncClassResolvesFromItsNamespace(): void
    {
        $files = $this->dataSyncFiles();

        $this->assertGreaterThan(50, count($files), 'the DataSync sources should all have been found');

        foreach ($files as $relativePath) {
            $className = str_replace('/', '\\', substr($relativePath, 0, -strlen('.php')));

            $this->assertTrue(
                class_exists($className) || trait_exists($className) || interface_exists($className),
                $relativePath . ' does not autoload as ' . $className
            );
        }
    }

    /**
     * The bundled autoloader is PEAR-flavoured and turns an underscore in a class name into a
     * directory separator, so a DataSync class carrying one would be looked for in a directory
     * that does not exist.
     */
    public function testNoDataSyncClassNameCarriesAnUnderscore(): void
    {
        foreach ($this->dataSyncFiles() as $relativePath) {
            $this->assertStringNotContainsString(
                '_',
                basename($relativePath),
                $relativePath . ' would be misresolved by src/autoloader.php'
            );
        }
    }

    /**
     * @return string[] Paths relative to src/, using forward slashes.
     */
    private function dataSyncFiles(): array
    {
        $root = dirname(__DIR__, 3) . '/src/';
        $files = [];

        foreach (self::SOURCE_PATHS as $path) {
            $absolute = $root . $path;

            if (is_file($absolute)) {
                $files[] = $path;
                continue;
            }

            $this->assertDirectoryExists($absolute);

            /** @var SplFileInfo $file */
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolute)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $path . '/' . str_replace(
                        '\\',
                        '/',
                        substr($file->getPathname(), strlen($absolute) + 1)
                    );
                }
            }
        }

        return $files;
    }
}
