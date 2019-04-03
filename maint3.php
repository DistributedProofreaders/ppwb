<?php

/* this script is run by cron daily to remove any log files in
   the 't' subdirectory older than 14 days.

   crontab -e entry:
   31 2 * * * wget -q --spider http://pgdp.org/~rfrank/ppwb/maint3.php
*/

function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

$fileSystemIterator = new FilesystemIterator('t');
$now = time();
foreach ($fileSystemIterator as $file) {
    if ($now - $file->getCTime() >= 60 * 60 * 24 * 14) { 
        echo('t/'.$file->getFilename());
        echo "<br/>";
        deleteDir('t/'.$file->getFilename());
    }
}

?>
