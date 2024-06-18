<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use PubNub\PubNub;
use PubNub\PNConfiguration;

$channelName = "file-channel";
$fileName = "pn.gif";

$config = new PNConfiguration();
$config->setSubscribeKey(getenv('SUBSCRIBE_KEY', 'demo'));
$config->setPublishKey(getenv('PUBLISH_KEY', 'demo'));
$config->setUserId('example');

$pubnub = new PubNub($config);

// Sending file
// $fileHandle = fopen(__DIR__ . DIRECTORY_SEPARATOR . $fileName, "r");
// $sendFileResult = $pubnub->sendFile()
//     ->channel($channelName)
//     ->fileName($fileName)
//     ->message("Hello from PHP SDK")
//     ->fileHandle($fileHandle)
//     ->sync();

// fclose($fileHandle);

// Listing files in the channel
$channelFiles = $pubnub->listFiles()->channel($channelName)->sync();
if ($channelFiles->getCount() > 0) {
    print("There are {$channelFiles->getCount()} files in the channel {$channelName}\n");
    foreach ($channelFiles->getFiles() as $idx => $file) {
        print("File[{$idx}]: {$file->getName()} with ID: {$file->getId()},"
            . "size {$file->getSize()}, created at: {$file->getCreationTime()}\n");
    }
} else {
    print("There are no files in the channel {$channelName}\n");
}

$file = $channelFiles->getFiles()[0];

print('Getting download URL for the file...');
$downloadUrl = $pubnub->getFileDownloadUrl()
    ->channel($channelName)
    ->fileId($file->getId())
    ->fileName($file->getName())
    ->sync();

printf("To download the file use the following URL: %s\n", $downloadUrl->getFileUrl());

print("Downloading file...");
$downloadFile = $pubnub->downloadFile()
    ->channel($channelName)
    ->fileId($file->getId())
    ->fileName($file->getName())
    ->sync();
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . $file->getName(), $downloadFile->getFileContent());
print("done. File saved as: {$file->getName()}\n");

// deleting file
$deleteFile = $pubnub->deleteFile()
    ->channel($channelName)
    ->fileId($file->getId())
    ->fileName($file->getName())
    ->sync();

if ($deleteFile->getStatus() === 200) {
    print("File deleted successfully\n");
} else {
    print("Failed to delete file\n");
}
