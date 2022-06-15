<?php
require_once("base.inc");

list($workdir, $workurl, $upid) = init_workdir();
$extensions = ["zip"]; // allowed file extensions

// ----- process the main project file ---------------------------------

$target_name = process_file_upload("userfile", $workdir, $extensions);

// ----- no errors. proceed ----------------------------------------

log_tool_access("pphtml", $upid);

// ----- burst the zip file --------------------------------------------

$zipArchive = new ZipArchive();
$result = $zipArchive->open($target_name);
if ($result === TRUE) {
    $zipArchive->extractTo($workdir);
    $zipArchive->close();
}
else {
    print_r("unable to unzip uploaded file");
    exit(1);
}

// find the name of the user's HTML file. only one allowed

$fileList1 = glob("$workdir/*.htm");
$fileList2 = glob("$workdir/*.html");
$fileList3 = glob("$workdir/*.xhtml");
$user_htmlfile = "";
if (count($fileList1) == 1) {
    $user_htmlfile = $fileList1[0];
}
if (count($fileList2) == 1) {
    $user_htmlfile = $fileList2[0];
}
if (count($fileList3) == 1) {
    $user_htmlfile = $fileList3[0];
}
if ($user_htmlfile == "") {
    echo "could not determine HTML source file name";
    exit(1);       
}

$options = [];

// see if user has ticked the "verbose" box
if(isset($_POST['ver']) && $_POST['ver'] == 'Yes') {
    $options[] = "-v";
}

// ----- run the pphtml command ----------------------------------------

// build the command
$scommand = join(" ", [
    $python_runner,
    "./bin/pphtml/pphtml.py",
    join(" ", $options),
    "-i " . escapeshellarg($user_htmlfile),
    "-o " . escapeshellarg("$workdir/report.html")
]);

$command = join(" ", [
    escapeshellcmd($scommand),
    "2>&1"
]);

log_tool_action($workdir, "command", $command);

// and finally, run pphtml
$output = shell_exec($command);

log_tool_action($workdir, "output", $output);

// ----- display results -------------------------------------------

output_header("pphtml Results");

$reportok = false;

echo "<p>";
if (file_exists("$workdir/report.html")) {
   echo "results available: <a href='$workurl/report.html'>here</a>.<br/>";
   $reportok = true;
}
if ($reportok) {
    echo "Left click to view. Right click to download.</p>";
} else {
    echo "<p>Whoops! Something went wrong and no output was generated.
    The error message was<br/><br/>
    <tt>${output}</tt></p>
    </p>For more assistance, ask in the <a href='$help_url'>discussion topic</a> and include this identifier: ${upid}</p>";
}
