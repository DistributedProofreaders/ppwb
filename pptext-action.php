<?php

// get user's IP address

function getUserIP()
{
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
            $addr = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($addr[0]);
        }
        else {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    else {
        return $_SERVER['REMOTE_ADDR'];
    }
}


$errors = array(); // place to save error messages
$work = "t"; // a working folder for project data
$upid = uniqid('r'); // unique project id
$extensions = array( // allowed file extensions
    "txt"
);
$target_name = "";
$gtarget_name = "";

// create a unique workbench project folder in t
mkdir($work . "/" . $upid, 0755);


// ----- process the main project file ---------------------------------

if (isset($_FILES['userfile']) && $_FILES['userfile']['name'] != "") {

    // get the information about the file
    $file_name = $_FILES['userfile']['name'];
    $file_size = $_FILES['userfile']['size'];
    $file_tmp = $_FILES['userfile']['tmp_name'];
    $file_type = $_FILES['userfile']['type'];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    move_uploaded_file($file_tmp, "/tmp/" . $upid);

    // begin a series of validation tests

    // does it pass the anti-virus tests?
    $av_test_result = array();
    $av_retval = 0;
    $cmd = "/usr/bin/clamdscan " . escapeshellcmd("/tmp/" . $upid);
    exec($cmd, $av_test_result, $av_retval);
    if ($av_retval == 1) {
        $errors[] = "input file rejected by clamdscan";
        // destroy uploaded file
        unlink( escapeshellcmd("/tmp/" . $upid) );
    }

    // was a file uploaded?
    if ($_FILES['userfile']['size'] == 0) {
        $errors[] = 'no file was uploaded';
    }

    // do they claim it's an allowed type?
    if (in_array($file_ext, $extensions) === false) {
        $errors[] = "please upload a .txt file";
    }

    // is it small enough?
    if ($file_size > 31457280) {
        $errors[] = "file size must be less than 30 MB";
    }

    // if any errors, report and terminate
    if (empty($errors) == false) {
        echo "<pre>";
        echo "process terminated during processing uploaded file:<br/>";
        foreach ($errors as $key => $value) {
            echo $value . "<br/>";
        }
        echo "</pre>";
        exit(1);
    }

    // move the uploaded file to the project folder
    $target_name = $work . "/" . $upid . "/" . $file_name;
    rename("/tmp/" . $upid, $target_name);
}

// ----- do that all again for the goodwords file, if present ------

if (isset($_FILES['goodfile']) && $_FILES['goodfile']['name'] != "") {

    // get the information about the file
    $file_name = $_FILES['goodfile']['name'];
    $file_size = $_FILES['goodfile']['size'];
    $file_tmp = $_FILES['goodfile']['tmp_name'];
    $file_type = $_FILES['goodfile']['type'];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    move_uploaded_file($file_tmp, "/tmp/" . $upid);

    // begin a series of validation tests

    // does it pass the anti-virus tests?
    $av_test_result = array();
    $av_retval = 0;
    $cmd = "/usr/bin/clamdscan " . escapeshellcmd("/tmp/" . $upid);
    exec($cmd, $av_test_result, $av_retval);
    if ($av_retval == 1) {
        $errors[] = "input file rejected by clamdscan";
        // destroy uploaded file
        unlink( escapeshellcmd("/tmp/" . $upid) );
    }

    // was a file uploaded?
    if ($_FILES['userfile']['size'] == 0) {
        $errors[] = 'no file was uploaded';
    }

    // do they claim it's an allowed type?
    if (in_array($file_ext, $extensions) === false) {
        $errors[] = "please upload a .txt file";
    }

    // is it small enough?
    if ($file_size > 31457280) {
        $errors[] = "file size must be less than 30 MB";
    }

    // if any errors, report and terminate
    if (empty($errors) == false) {
        echo "<pre>";
        echo "process terminated during processing good words file:<br/>";
        foreach ($errors as $key => $value) {
            echo $value . "<br/>";
        }
        echo "</pre>";
        exit(1);
    }

    // move the uploaded file to the project folder
    $gtarget_name = $work . "/" . $upid . "/" . $file_name;
    rename("/tmp/" . $upid, $gtarget_name);
}
  
// ----- no errors. proceed ----------------------------------------

// make a record of this attempted run ---
// format is:
//    2019-03-31 12:46:44 pptext r5ca0b6b499bec \
//    67.161.200.103 [Littleton, United States]

$date = date('Y-m-d H:i:s');
$ip = getUserIP();

$access_key = 'f00ad0e10a4ebe4ec5cb3ffd6c1dc4c8';

// Initialize CURL:
$ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($ch);
curl_close($ch);

// Decode JSON response:
$api_result = json_decode($json, true);

// from: https://ipstack.com/documentation
$city = $api_result['city'];
$country = $api_result['country_name'];

$messagegeo = " [" . $city . ", " . $country . "]";
$s = $date . " " . "pptext" . " " . $upid . " " . $ip . $messagegeo . "\n";
file_put_contents($work . "/access.log", $s, FILE_APPEND);

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
    exec($cmd, $ppf_output, $ppf_exitcode);
    $pos = strpos($ppf_output[0], "ISO-8859");
    if ($pos !== false) {
        // Latin-1. convert to UTF-8
        $tmpfname = "/tmp/trash01.txt";
        $cmd = "iconv -f ISO_8859-1 -t UTF-8 -o '${tmpfname}' '${gtarget_name}'";
        exec($cmd, $ppf_output, $ppf_exitcode);
        rename($tmpfname, $gtarget_name);
        file_put_contents("${work}/${upid}/converted-gwf.txt", "good words file converted from ISO-8859");
    }
}

// ----- process user options ------------------------------------------

// get the user's chosen language(s) 
$wlang = "";
$nlang = 0;  // number of languages
if(isset($_POST['wlangs'])){
    foreach($_POST['wlangs'] as $alang){
        $wlang = $wlang . "," . $alang;
        $nlang = $nlang + 1;
    }
}

if ($nlang == 0) {
    echo "Please select at least one language. Exiting.";
    exit(1);
}
$wlang = substr($wlang, 1);  // remove leading comma
$wlang = " -a " . $wlang . " ";

// see if user has ticked the "skip edit distance" box
$nrs = "";
if(isset($_POST['edc']) && $_POST['edc'] == 'Yes') {
    $nrs = " -l ";  
}

// see if user has ticked the "skip smart quote checks" box
$sqc = "";
if(isset($_POST['sqc']) && $_POST['sqc'] == 'Yes') {
    $sqc = " -q ";  
}

// see if user has ticked the "skip edit distance checks" box
$lev = "";
if(isset($_POST['lev']) && $_POST['lev'] == 'Yes') {
    $lev = " -d ";  
}    

// see if user has ticked the "verbose" box
$ver = "";
if(isset($_POST['ver']) && $_POST['ver'] == 'Yes') {
    $ver = " -v ";
}

$useropts = $wlang . $nrs . $sqc . $lev . $ver;

// include good words file if present
$gw = "";
if ($gtarget_name != "") {
    $gw = " -g " . $gtarget_name . " ";
}

// ----- run the pptext command ----------------------------------------

// build the command
$scommand = './bin/pptext ' . $useropts . $gw . ' -i ' . $target_name . ' -o ' . $work . "/" . $upid;
$command = escapeshellcmd($scommand) . " 2>&1";
// echo $command;

// and finally, run pptext
$output = shell_exec($command);

// ----- display results -------------------------------------------

echo "<!doctype html>";
echo "<html lang='en'>";
echo "<head>";
echo "  <link rel='stylesheet' type='text/css' href='rfrank.css'>";
echo "</head>";
echo "<body>";
echo "<p><b>Pptext Results</b></p>";

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

echo "</body>";
echo "</html>";

?>
