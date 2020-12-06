<?php
require_once("base.inc");

list($workdir, $workurl, $upid) = init_workdir();
$extensions = ["txt", "htm", "html"]; // allowed file extensions

// ----- process the main project file ---------------------------------

$target_name = process_file_upload("userfile", $workdir, $extensions);

// ----- no errors. proceed ----------------------------------------

log_tool_access("ppsmq", $upid);

// ----- run the ppsmq command ----------------------------------------

// build the command
$scommand = join(" ", [
    $python_runner,
    "./bin/ppsmq.py",
    "-i " . escapeshellarg($target_name),
    "-o " . escapeshellarg("$workdir/report.txt")
]);

$command = join(" ", [
    escapeshellcmd($scommand),
    "2>&1"
]);

// echo $command;

// and finally, run ppsmq
$output = shell_exec($command);

// ----- display results -------------------------------------------

output_header("ppsmq Results");

$reportok = false;

echo "<p>";
if (file_exists("$workdir/report.txt")) {
   echo "results available: <a href='$workurl/report.txt'>here</a>.<br/>";
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

