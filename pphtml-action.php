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
    "zip"
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
        $errors[] = "please upload a .zip file";
    }

    // is it small enough?
    if ($file_size > 40000000) {
        $errors[] = "file size must be less than 40 MB";
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
$s = $date . " " . "pphtml" . " " . $upid . " " . $ip . $messagegeo . "\n";
file_put_contents($work . "/access.log", $s, FILE_APPEND);

// ----- burst the zip file --------------------------------------------

$zipArchive = new ZipArchive();
$result = $zipArchive->open($target_name);
if ($result === TRUE) {
    $zipArchive->extractTo($work . "/" . $upid);
    $zipArchive->close();
}
else {
    print_r("unable to unzip uploaded file");
    exit(1);
}

// find the name of the user's HTML file. only one allowed

$fileList1 = glob($work . "/" . $upid . '/*.htm');
$fileList2 = glob($work . "/" . $upid . '/*.html');
$user_htmlfile = "";
if (count($fileList1) == 1) {
    $user_htmlfile = $fileList1[0];
}
if (count($fileList2) == 1) {
    $user_htmlfile = $fileList2[0];
}
if ($user_htmlfile == "") {
    echo "could not determine HTML source file name";
    exit(1);       
}

// ----- run the pphtml command ----------------------------------------

// build the command
// $scommand = './pphtml -i ' . $target_name . ' -o ' . $work . "/" . $upid; // orthogonal
// $scommand = './bin/pphtml -i ' . $user_htmlfile . ' -o ' . $work . "/" . $upid . "/report.html";
$scommand = 'python3 ./bin/pphtml.py -i "' . $user_htmlfile . '" -o ' . $work . "/" . $upid . "/report.html";

$command = escapeshellcmd($scommand) . " 2>&1";

// echo $command;
file_put_contents("${work}/${upid}/command.txt", $command);

// and finally, run pphtml
$output = shell_exec($command);

// ----- display results -------------------------------------------

echo "<!doctype html>";
echo "<html lang='en'>";
echo "<head>";
echo "  <link rel='stylesheet' type='text/css' href='rfrank.css'>";
echo "</head>";
echo "<body>";
echo "<p><b>Pphtml Results</b></p>";

$reportok = false;

echo "<p>";
if (file_exists("${work}/${upid}/report.html")) {
   echo "results available: <a href='${work}/${upid}/report.html'>here</a>.<br/>";
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
