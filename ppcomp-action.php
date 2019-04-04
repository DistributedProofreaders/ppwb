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
$options = "";  // user-requested options

// in directory /tmp
$tfilename1 = "/tmp/" . $upid . "-1";
$tfilename2 = "/tmp/" . $upid . "-2";

// in project folder
$target_name1 = "";
$target_name2 = "";

// create a unique workbench project folder in t
mkdir($work . "/" . $upid, 0755);

// ----- process the first file ---------------------------------

if (isset($_FILES['userfile1']) && $_FILES['userfile1']['name'] != "") {

    // get the information about the file
    $file_name = $_FILES['userfile1']['name'];
    $file_size = $_FILES['userfile1']['size'];
    $file_tmp = $_FILES['userfile1']['tmp_name'];
    $file_type = $_FILES['userfile1']['type'];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    move_uploaded_file($file_tmp, $tfilename1);

    // begin a series of validation tests

    // does it pass the anti-virus tests?
    $av_test_result = array();
    $av_retval = 0;
    $cmd = "/usr/bin/clamdscan " . escapeshellcmd($tfilename1);
    exec($cmd, $av_test_result, $av_retval);
    if ($av_retval == 1) {
        $errors[] = "input file 1 rejected by clamdscan";
        // destroy uploaded file
        unlink( escapeshellcmd($tfilename1) );
    }

    // was a file uploaded?
    if ($_FILES['userfile1']['size'] == 0) {
        $errors[] = 'no file was uploaded';
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
    $target_name1 = $work . "/" . $upid . "/" . $file_name;
    rename($tfilename1, $target_name1);
}

// ----- process the second file ---------------------------------

if (isset($_FILES['userfile2']) && $_FILES['userfile2']['name'] != "") {

    // get the information about the file
    $file_name = $_FILES['userfile2']['name'];
    $file_size = $_FILES['userfile2']['size'];
    $file_tmp = $_FILES['userfile2']['tmp_name'];
    $file_type = $_FILES['userfile2']['type'];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    move_uploaded_file($file_tmp, $tfilename2);

    // begin a series of validation tests

    // does it pass the anti-virus tests?
    $av_test_result = array();
    $av_retval = 0;
    $cmd = "/usr/bin/clamdscan " . escapeshellcmd($tfilename2);
    exec($cmd, $av_test_result, $av_retval);
    if ($av_retval == 1) {
        $errors[] = "input file 2 rejected by clamdscan";
        // destroy uploaded file
        unlink( escapeshellcmd($tfilename2) );
    }

    // was a file uploaded?
    if ($_FILES['userfile2']['size'] == 0) {
        $errors[] = 'no file was uploaded';
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
    $target_name2 = $work . "/" . $upid . "/" . $file_name;
    rename($tfilename2, $target_name2);
}

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

if(isset($_POST['txt-cleanup-type'])){
    $options = $options . " --" . "txt-cleanup-type" . " " . $_POST['txt-cleanup-type'];
}

// ----- no errors. proceed ----------------------------------------

// make a record of this attempted run ---
// format is:
//    2019-04-02 12:46:44 pphtml r5ca0b6b499bec \
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
$s = $date . " " . "ppcomp" . " " . $upid . " " . $ip . $messagegeo . "\n";
file_put_contents($work . "/access.log", $s, FILE_APPEND);

// ----- run the ppcomp command ----------------------------------------

$scommand = 'PYTHONIOENCODING=utf-8:surrogateescape /home/rfrank/env/bin/python3 ./bin/comp_pp.py ' . $options . " " . $target_name1 . " " . $target_name2;
$command = escapeshellcmd($scommand) . " > " . $work . "/" . $upid . "/result.html 2>&1";
print_r($command);
$output = shell_exec($command);

// ----- display results -------------------------------------------

echo "<!doctype html>";
echo "<html lang='en'>";
echo "<head>";
echo "  <link rel='stylesheet' type='text/css' href='rfrank.css'>";
echo "</head>";
echo "<body>";
echo "<p><b>Ppcomp Results</b></p>";

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

echo "</body>";
echo "</html>";

?>
