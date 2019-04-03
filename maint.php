<?php

$action = 0;

/*
clear out the "t" working directory contents
*/
if ($action == 1) {
	$dir = '/home/rfrank/public_html/test/t';
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
