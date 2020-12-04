<?php
// Common functions used by many/all pages

// Output valid HTML page header
function output_header($header="", $addl_links=[], $js="")
{
    if($header)
    {
        $title = "PP Workbench: $header";
    }
    else
    {
        $title = "Post-Processing Workbench";
        $header = "pp workbench";
    }

    echo <<<HEAD
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name=viewport content="width=device-width, initial-scale=1">
    <title>$title</title>
    <link rel="stylesheet" type="text/css" href="rfrank.css">
    <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script>$js</script>
  </head>
  <body>
  <div id="header" class='hsty'>$header</div>
	<hr style='border:none; border-bottom:1px solid silver;'>  
HEAD;

    // Register a shutdown callback to always close out the page
    register_shutdown_function('output_footer', $addl_links);
}

// You should not call this function directly as it is registered as a
// shutdown callback with output_header() to ensure that the HTML is
// always closed properly.
function output_footer($addl_links=[])
{
    // prepend a return to the main page
    if(basename($_SERVER["PHP_SELF"]) != "index.php")
    {
        $addl_links = array_merge(["index.php" => "MAIN PAGE"], $addl_links);
    }

    $links = [];
    foreach($addl_links as $url => $name)
    {
        $links[] = "<a style='font-size: 70%' href='$url'>$name</a>";
    }
    $links = join("&nbsp;|&nbsp;", $links);

    echo <<<FOOT
  <div id="footer">
    <hr style='border:none; border-bottom:1px solid silver;'>
    <table style='width: 100%'>
      <tr>
        <td style='text-align: left'>$links</td>
        <td style='text-align: right'>
        <a style='font-size: 70%' href='mailto:rfrank@rfrank.net'>CONTACT</a></td>
      </tr>
    </table>
  </div>
  </body>
</html>
FOOT;
}

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

// log tool access
function log_tool_access($tool, $upid, $work="t")
{
    // make a record of this attempted run ---
    // format is:
    //    2019-03-31 12:46:44 pptext r5ca0b6b499bec \
    //    67.161.200.103 [Littleton, United States]

    $ip = getUserIP();
    $access_key = 'f00ad0e10a4ebe4ec5cb3ffd6c1dc4c8';

    // Initialize CURL:
    $ch = curl_init("http://api.ipstack.com/$ip?access_key=$access_key");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Store the data:
    $json = curl_exec($ch);
    curl_close($ch);

    // Decode JSON response:
    $api_result = json_decode($json, true);

    // from: https://ipstack.com/documentation
    $city = $api_result['city'];
    $country = $api_result['country_name'];

    $s = sprintf("%s %6s %s %s [%s, %s]\n",
        date('Y-m-d H:i:s'), $tool, $upid, $ip, $city, $country
    );
    file_put_contents($work . "/access.log", $s, FILE_APPEND);
}
