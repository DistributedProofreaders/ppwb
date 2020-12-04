<?php
require_once("base.inc");

$work = "t"; // a working folder for project data
$upid = uniqid('r'); // unique project id
$extensions = array( // allowed file extensions
    "txt", "TXT"
);

// create a unique workbench project folder in t
$workdir = "$work/$upid";
mkdir($workdir, 0755);

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
if ($target_name != "") {
    $cmd = "file '${target_name}'";
    exec($cmd, $ppf_output, $ppf_exitcode);
    $pos = strpos($ppf_output[0], "ISO-8859");
    if ($pos !== false) {
        // Latin-1. convert to UTF-8
        $tmpfname = "/tmp/trash01.txt";
        $cmd = "iconv -f ISO_8859-1 -t UTF-8 -o '${tmpfname}' '${target_name}'";
        exec($cmd, $ppf_output, $ppf_exitcode);
        rename($tmpfname, $target_name);
        file_put_contents("${work}/${upid}/converted-main.txt", "text file converted from ISO-8859");
    }
}

// good words file
if ($gtarget_name != "") {
    $cmd = "file '${gtarget_name}'";
    exec($cmd, $ppf_outputg, $ppf_exitcodeg);
    $pos = strpos($ppf_outputg[0], "ISO-8859");
    if ($pos !== false) {
        // Latin-1. convert to UTF-8
        $tmpfname = "/tmp/trash01.txt";
        $cmd = "iconv -f ISO_8859-1 -t UTF-8 -o '${tmpfname}' '${gtarget_name}'";
        exec($cmd, $ppf_outputg, $ppf_exitcodeg);
        rename($tmpfname, $gtarget_name);
        file_put_contents("${work}/${upid}/converted-gwf.txt", "good words file converted from ISO-8859");
    }
}

// ----- process user options ------------------------------------------

// get the user's chosen language(s) 
$wlang = "";
$nlang = 0;  // number of languages
$isEnglish = false;
if(isset($_POST['wlangs'])){
    foreach($_POST['wlangs'] as $alang){
        $wlang = $wlang . "," . $alang;
        $nlang = $nlang + 1;
        if ($alang == "en" || $alang == "en_GB" || $alang == "en_US" || $alang == "en_CA" ) {
            $isEnglish = true;
        }        
    }
}

// if no "English" language is selected, disable jeebies
$jflag = "";
if (!$isEnglish) {
    $jflag = " -j ";
}

if ($nlang == 0) {
    echo "Please select at least one language. Exiting.";
    exit(1);
}
$wlang = substr($wlang, 1);  // remove leading comma
$wlang = " -a " . $wlang . " ";

// aggregate user-selected tests
$utests = " -t ";
if(isset($_POST['rat']) && $_POST['rat'] == 'Yes') {
  $utests = $utests . "a";
}
if(isset($_POST['rspl']) && $_POST['rspl'] == 'Yes') {
  $utests = $utests . "s";
}
if(isset($_POST['redi']) && $_POST['redi'] == 'Yes') {
  $utests = $utests . "e";
}
if(isset($_POST['rtxt']) && $_POST['rtxt'] == 'Yes') {
  $utests = $utests . "t";
}
if(isset($_POST['rthc']) && $_POST['rthc'] == 'Yes') {
  $utests = $utests . "1";
}
if(isset($_POST['rhsc']) && $_POST['rhsc'] == 'Yes') {
  $utests = $utests . "2";
}
if(isset($_POST['rjee']) && $_POST['rjee'] == 'Yes') {
  $utests = $utests . "j";
}
if(isset($_POST['rsqc']) && $_POST['rsqc'] == 'Yes') {
  $utests = $utests . "q";
}

// see if user has ticked the "verbose" box
$verbose = "";
if(isset($_POST['ver']) && $_POST['ver'] == 'Yes') {
    $verbose = " -v ";
}

$useropts = $wlang . $utests . $verbose;

// include good words file if present
$gw = "";
if ($gtarget_name != "") {
    $gw = " -g \"" . $gtarget_name . "\" ";
}

// ----- run the pptext command ----------------------------------------

// build the command
$scommand = './bin/pptext ' . $useropts . $gw . ' -i "' . $target_name . '" -o ' . $work . "/" . $upid;
$command = escapeshellcmd($scommand) . " 2>&1";

// echo $command;
file_put_contents("${work}/${upid}/command.txt", $command);

// and finally, run pptext
$output = shell_exec($command);

// ----- display results -------------------------------------------

output_header("pptext Results");

$reportok = false;

echo "<p>";
if (file_exists("${work}/${upid}/report.html")) {
   echo "results available: <a href='${work}/${upid}/report.html'>here</a>.<br/>";
   $reportok = true;
}
if (file_exists("${work}/${upid}/scanreport.txt")) {
   echo "punctuation scan report: <a href='${work}/${upid}/scanreport.txt'>here</a>.<br/>";
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

