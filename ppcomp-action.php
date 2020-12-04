<?php
require_once("base.inc");

$work = "t"; // a working folder for project data
$upid = uniqid('r'); // unique project id
$options = "";  // user-requested options

// create a unique workbench project folder in t
$workdir = "$work/$upid";
mkdir($workdir, 0755);

// ----- process the first file ---------------------------------

$target_name1 = process_file_upload("userfile1", $workdir);

// ----- process the second file ---------------------------------

$target_name2 = process_file_upload("userfile2", $workdir);

// ----- process user options ---------------------------------------

if(isset($_POST['ignore-format'])){
    $options = $options . " --" . "ignore-format";
}
 
if(isset($_POST['suppress-footnote-tags'])){
    $options = $options . " --" . "suppress-footnote-tags";
}

if(isset($_POST['suppress-illustration-tags'])){
    $options = $options . " --" . "suppress-illustration-tags";
}

if(isset($_POST['ignore-case'])){
    $options = $options . " --" . "ignore-case";
}

if(isset($_POST['extract-footnotes'])){
    $options = $options . " --" . "extract-footnotes";
}

if(isset($_POST['ignore-0-space'])){
    $options = $options . " --" . "ignore-0-space";
}

if(isset($_POST['suppress-nbsp-num'])){
    $options = $options . " --" . "suppress-nbsp-num";
}

if(isset($_POST['suppress-proofers-notes'])){
    $options = $options . " --" . "suppress-proofers-notes";
}

if(isset($_POST['regroup-split-words'])){
    $options = $options . " --" . "regroup-split-words";
}

if(isset($_POST['css-greek-title-plus'])){
    $options = $options . " --" . "css-greek-title-plus";
}

if(isset($_POST['css-add-illustration'])){
    $options = $options . " --" . "css-add-illustration";
}

if(isset($_POST['css-no-default'])){
    $options = $options . " --" . "css-no-default";
}

if(isset($_POST['without-html-header'])){
    $options = $options . " --" . "without-html-header";
}

// if(isset($_POST['bold-replace'])){
//    $options = $options . " --css-bold =";
// }

if(isset($_POST['txt-cleanup-type'])){
    $options = $options . " --" . "txt-cleanup-type" . " " . $_POST['txt-cleanup-type'];
}

// ----- no errors. proceed ----------------------------------------

log_tool_access("ppcomp", $upid);

// ----- run the ppcomp command ----------------------------------------

$scommand = 'PYTHONIOENCODING=utf-8:surrogateescape /home/rfrank/env/bin/python3 ./bin/comp_pp.py ' . $options . " " . $target_name1 . " " . $target_name2;
$command = escapeshellcmd($scommand) . " > " . $work . "/" . $upid . "/result.html 2>&1";

// echo $command;
file_put_contents("${work}/${upid}/command.txt", $command);

$output = shell_exec($command);

// ----- display results -------------------------------------------

output_header("ppcomp Results");

$reportok = false;

echo "<p>";
if (file_exists("${work}/${upid}/result.html")) {
   echo "results available: <a href='${work}/${upid}/result.html'>here</a>.<br/>";
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

