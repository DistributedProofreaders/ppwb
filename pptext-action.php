<?php
require_once("base.inc");

list($workdir, $workurl, $upid) = init_workdir();
$extensions = ["txt", "TXT"]; // allowed file extensions

// ----- process the main project file ---------------------------------

$target_name = process_file_upload("userfile", $workdir, $extensions);

// ----- do that all again for the goodwords file, if present ------

if (@$_FILES['goodfile']["name"]) {
    $gtarget_name = process_file_upload("goodfile", $workdir, $extensions);
} else {
    $gtarget_name = "";
}

// ----- no errors. proceed ----------------------------------------

log_tool_access("pptext", $upid);

// ----- user has option of uploading a Latin-1 file -------------------

// main file
if(ensure_utf8_file($target_name)) {
    file_put_contents("$workdir/converted-main.txt", "text file converted from ISO-8859\n");
}

// good words file
if ($gtarget_name && ensure_utf8_file($gtarget_name)) {
    file_put_contents("$workdir/converted-gwf.txt", "good words file converted from ISO-8859\n");
}

// ----- process user options ------------------------------------------

$options = [];

// get the user's chosen language(s) 
$wlangs = [];
$isEnglish = false;
if(isset($_POST['wlangs'])){
    foreach($_POST['wlangs'] as $alang){
        $wlangs[] = $alang;
        if ($alang == "en" || $alang == "en_GB" || $alang == "en_US" || $alang == "en_CA" ) {
            $isEnglish = true;
        }        
    }
}

if (count($wlangs) == 0) {
    echo "Please select at least one language. Exiting.";
    exit(1);
}

$options[] = "-a " . escapeshellarg(join(",", $wlangs));

// aggregate user-selected tests
$available_tests = [
    "rat" => "a",
    "rspl" => "s",
    "redi" => "e",
    "rtxt" => "t",
    "rthc" => "1",
    "rhsc" => "2",
    "rsqc" => "q",
];

// only allow jeebies if an "English" language is selected
if ($isEnglish) {
    $available_tests["rjee"] = "j";
}

$utests = [];
foreach($available_tests as $key => $val) {
    if(isset($_POST[$key]) && $_POST[$key] == 'Yes') {
        $utests[] = $val;
    }
}
$options[] = "-t " . escapeshellarg(join("", $utests));

// see if user has ticked the "verbose" box
if(isset($_POST['ver']) && $_POST['ver'] == 'Yes') {
    $options[] = " -v ";
}

// include good words file if present
if ($gtarget_name) {
    $options[] = "-g " . escapeshellarg($gtarget_name);
}

// ----- run the pptext command ----------------------------------------

// build the command
$scommand = join(" ", [
    "./bin/pptext/pptext",
    join(" ", $options),
    "-i " . escapeshellarg($target_name),
    "-o " . escapeshellarg($workdir)
]);

$command = join(" ", [
    escapeshellcmd($scommand),
    "2>&1"
]);

// echo $command;
file_put_contents("$workdir/command.txt", $command);

// and finally, run pptext
$output = shell_exec($command);

// ----- display results -------------------------------------------

output_header("pptext Results");

$reportok = false;

echo "<p>";
if (file_exists("$workdir/report.html")) {
   echo "results available: <a href='$workurl/report.html'>here</a>.<br/>";
   $reportok = true;
}
if (file_exists("$workdir/scanreport.txt")) {
   echo "punctuation scan report: <a href='$workurl/scanreport.txt'>here</a>.<br/>";
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

