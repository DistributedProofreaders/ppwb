<?php
require_once("base.inc");

// Maintenance script to remove files older files. This script should be run
// via a crontab *through the web server* to ensure it has the correct
// permissions, eg:
//     URL=https://www.pgdp.org/ppwb/maint.php;
//     /usr/bin/wget --quiet --tries=1 --timeout=0 -O- $URL
// This script ensures that only runs from localhost will succeed.

// Reject calls not by localhost or other authorized callers
if(!requester_is_authorized()) {
    die("You are not authorized to perform this request.\n");
}

// Walk through our workdir deleting directories that are older than
// $maint_days_ago
$maint_cutoff = time() - ($maint_days_ago * 60*60*24);
foreach(new DirectoryIterator($base_workdir) as $dir) {
    // skip any dot files or access.log
    if($dir->isDot() || $dir->getFilename() == "access.log") {
        continue;
    }

    if($dir->getMTime() < $maint_cutoff) {
        // echo "Deleting " . $dir->getFilename() . "\n";

        $command = join(" ", [
            "rm",
            "-rf",
            escapeshellarg("$base_workdir/" . $dir->getFilename())
        ]);

        // echo "$command\n";
        $output = null;
        $result_code = null;
        exec($command, $output, $result_code);
        if ($result_code != 0) {
            echo "Error deleting " . $dir->getFilename() . ": $output\n";
        }
    }
}

//---------------------------------------------------------------------------

// function to determine if page requester is from localhost or in our OK list
function requester_is_authorized()
{
    global $maint_ips;

    if(php_sapi_name() == "cli")
    {
        return FALSE;
    }

    if($_SERVER['REMOTE_ADDR'] == '127.0.0.1' ||
        $_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR'] ||
        in_array($_SERVER['REMOTE_ADDR'], $maint_ips))
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

