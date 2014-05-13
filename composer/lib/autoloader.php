<?php
#path PHP53  composer/lib/
#path PHP52 none
/*
 * Simple autoload for stanalone lib
 */

define('PUBNUB_LIB_BASE_DIR', __DIR__);

//autoloader
function pubnubAutoloader($className)
{
    $classPath = str_replace('\\', '/', $className);
    $filePath = sprintf('%s/%s.php', PUBNUB_LIB_BASE_DIR, $classPath);
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}

spl_autoload_register('pubnubAutoloader', true, true);