<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use PubNub\PubNub;
use PubNub\PNConfiguration;

// snippet.setup
$channelName = "file-channel";
$fileName = "pn.gif";

$config = new PNConfiguration();
$config->setSubscribeKey(getenv('SUBSCRIBE_KEY', 'demo'));
$config->setPublishKey(getenv('PUBLISH_KEY', 'demo'));
$config->setUserId('example');

$pubnub = new PubNub($config);
// snippet.end

// snippet.send_file
$fileHandle = fopen(__DIR__ . DIRECTORY_SEPARATOR . $fileName, "r");
$sendFileResult = $pubnub->sendFile()
    ->channel($channelName)
    ->fileName($fileName)
    ->message("Hello from PHP SDK")
    ->fileHandle($fileHandle)
    ->sync();
$fileId = $sendFileResult->getFileId();
$fileName = $sendFileResult->getFileName();

print("File uploaded successfully: {$fileName} with ID: {$fileId}\n");

// snippet.end

// snippet.send_file_with_just_content
$fileContent = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $fileName);
$sendFileResult = $pubnub->sendFile()
    ->channel($channelName)
    ->fileName($fileName)
    ->message("Hello from PHP SDK")
    ->fileContent($fileContent)
    ->sync();
$fileId = $sendFileResult->getFileId();
$fileName = $sendFileResult->getFileName();
print("File uploaded successfully: {$fileName} with ID: {$fileId}\n");
// snippet.end

// snippet.publish_file_with_message
$publishFileMessageResult = $pubnub->publishFileMessage()
    ->channel($channelName)
    ->fileId($fileId)
    ->fileName($fileName)
    ->message("Hello from PHP SDK")
    ->ttl(10)
    ->meta(["key" => "value"])
    ->customMessageType("custom")
    ->sync();
$timestamp = $publishFileMessageResult->getTimestamp();
print("File message published successfully: {$timestamp}\n");
// snippet.end

// snippet.list_files
$channelFiles = $pubnub->listFiles()->channel($channelName)->sync();
$fileCount = $channelFiles->getCount();
if ($fileCount > 0) {
    print("There are {$fileCount} files in the channel {$channelName}\n");
    foreach ($channelFiles->getFiles() as $idx => $file) {
        print("File[{$idx}]: {$file->getName()} with ID: {$file->getId()},"
            . "size {$file->getSize()}, created at: {$file->getCreationTime()}\n");
    }
} else {
    print("There are no files in the channel {$channelName}\n");
}
// snippet.end

// snippet.get_download_url
$file = $channelFiles->getFiles()[0];

print("Getting download URL for the file...\n");
$downloadUrl = $pubnub->getFileDownloadUrl()
    ->channel($channelName)
    ->fileId($file->getId())
    ->fileName($file->getName())
    ->sync();

print("To download the file use the following URL: {$downloadUrl->getFileUrl()}\n");

print("Downloading file... ");
$downloadFile = $pubnub->downloadFile()
    ->channel($channelName)
    ->fileId($file->getId())
    ->fileName($file->getName())
    ->sync();
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . $file->getName(), $downloadFile->getFileContent());
print("done. File saved as: {$file->getName()}\n");
// snippet.end

// snippet.delete_file
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
// snippet.end

// snippet.delete_all_files
$fileList = $pubnub->listFiles()->channel($channelName)->sync();
$fileCount = $fileList->getCount();
if ($fileCount > 0) {
    print("There are {$fileCount} files left in the channel {$channelName}\n");
    foreach ($fileList->getFiles() as $idx => $file) {
        $deleteFile = $pubnub->deleteFile()
            ->channel($channelName)
            ->fileId($file->getId())
            ->fileName($file->getName())
            ->sync();

        if ($deleteFile->getStatus() === 200) {
            print("File {$file->getId()} deleted successfully\n");
        } else {
            print("Failed to delete file {$file->getId()}\n");
        }
    }
} else {
    print("There are no files in the channel {$channelName}\n");
}
// snippet.end
