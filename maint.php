<?php

$action = 1;

/*
set $action = 1 to run this code
clear out the "t" working directory contents
make sure it is the right $dir
when done, make a new 't', chmod 0777 and create a file access.log 0777
*/
if ($action == 1) {
	$dir = '/home/rfrank/public_html/dev/t';
	$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
	$files = new RecursiveIteratorIterator($it,
	             RecursiveIteratorIterator::CHILD_FIRST);
	foreach($files as $file) {
	    if ($file->isDir()){
	        rmdir($file->getRealPath());
	    } else {
	        unlink($file->getRealPath());
	    }
	}
	// rmdir($dir);  leave the parent directory intact
	echo "done";
}

/* if I need to remove specific files owned by www-data: */
// unlink("/home/rfrank/public_html/test/t/command.txt");
// unlink("/home/rfrank/public_html/test/t/output.txt");
