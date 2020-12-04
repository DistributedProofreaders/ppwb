<?php
require_once("base.inc");

$work = "t"; // a working folder for project data
$upid = uniqid('r'); // unique project id
$extensions = array( // allowed file extensions
    "txt","htm","html"
);

// create a unique workbench project folder in t
$workdir = "$work/$upid";
mkdir($workdir, 0755);

// ----- process the main project file ---------------------------------

$target_name = process_file_upload("userfile", $workdir, $extensions);

// ----- no errors. proceed ----------------------------------------

log_tool_access("ppsmq", $upid);

// ----- run the ppsmq command ----------------------------------------

// build the command
$scommand = 'python3 ./bin/ppsmq.py -i ' . $target_name . ' -o ' . $work . '/' . $upid . '/report.txt';
$command = escapeshellcmd($scommand) . " 2>&1";
// echo $command;

// and finally, run ppsmq
$output = shell_exec($command);

// ----- display results -------------------------------------------

output_header("ppsmq Results");

$reportok = false;

echo "<p>";
if (file_exists("${work}/${upid}/report.txt")) {
   echo "results available: <a href='${work}/${upid}/report.txt'>here</a>.<br/>";
   $reportok = true;
}
if ($reportok) {
    echo "Left click to view. Right click to download.</p>";
} else {
    echo "<p>Whoops! Something went wrong and no output was generated.
    The error message was<br/><br/>
    <tt>${output}</tt></p>
    </p>For more assistance, please email rfrank@rfrank.net and include this project name: ${upid}</p>";
}

