<?php

// Include Composer autoloader (adjust path if needed)
require_once 'vendor/autoload.php';

use PubNub\PNConfiguration;
use PubNub\PubNub;
use PubNub\Exceptions\PubNubServerException;

// Create configuration
$pnConfig = new PNConfiguration();
$pnConfig->setSubscribeKey(getenv("SUBSCRIBE_KEY") ?? "demo");
$pnConfig->setPublishKey(getenv("PUBLISH_KEY") ?? "demo");
$pnConfig->setUserId("php-file-upload-demo");

// Initialize PubNub instance
$pubnub = new PubNub($pnConfig);

try {
    // Define channel and file paths
    $channelName = "file-sharing-channel";
    $fileName = "example.txt";
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . $fileName;

    // Create a sample file if it doesn't exist
    if (!file_exists($filePath)) {
        file_put_contents($filePath, "This is a sample file for PubNub file upload demo.");
    }

    // Open file handle for reading
    $fileHandle = fopen($filePath, "r");

    // Send file to the channel
    $sendFileResult = $pubnub->sendFile()
        ->channel($channelName)
        ->fileName($fileName)
        ->message("Hello from PHP SDK")
        ->fileHandle($fileHandle)
        ->sync();

    // Close file handle
    fclose($fileHandle);

    // Print success message
    echo "File uploaded successfully!" . PHP_EOL;
    echo "File name: " . $sendFileResult->getFileName() . PHP_EOL;
    echo "File ID: " . $sendFileResult->getFileId() . PHP_EOL;

    // Example of how to download this file
    echo PHP_EOL . "To download this file, use:" . PHP_EOL;
    echo '$result = $pubnub->downloadFile()' . PHP_EOL;
    echo '    ->channel("' . $channelName . '")' . PHP_EOL;
    echo '    ->fileId("' . $sendFileResult->getFileId() . '")' . PHP_EOL;
    echo '    ->fileName("' . $sendFileResult->getFileName() . '")' . PHP_EOL;
    echo '    ->sync();' . PHP_EOL;
} catch (PubNubServerException $exception) {
    // Handle PubNub-specific errors
    echo "Error uploading file: " . $exception->getMessage() . PHP_EOL;

    if (method_exists($exception, 'getServerErrorMessage') && $exception->getServerErrorMessage()) {
        echo "Server Error: " . $exception->getServerErrorMessage() . PHP_EOL;
    }

    if (method_exists($exception, 'getStatusCode') && $exception->getStatusCode()) {
        echo "Status Code: " . $exception->getStatusCode() . PHP_EOL;
    }
} catch (Exception $exception) {
    // Handle general exceptions
    echo "Error: " . $exception->getMessage() . PHP_EOL;
}
