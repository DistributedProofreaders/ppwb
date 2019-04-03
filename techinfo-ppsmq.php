<?php

output_header();

echo <<<BODY
<p>Technical info for ppsmq.</p>
<ol>
<li> to be added.
</li>
</ol>
BODY;

output_footer();

function output_header()
{
    echo <<<HEAD
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name=viewport content="width=device-width, initial-scale=1">
    <title>PP Workbench</title>
    <link rel="stylesheet" type="text/css" href="rfrank.css">
  </head>

<body>
  <div id="header" class='hsty'>ppsmq</div>
  <hr style='border:none; border-bottom:1px solid silver;'>
HEAD;
}

function output_footer()
{
    echo <<<FOOT
  <div id="footer">
    <hr style='border:none; border-bottom:1px solid silver;'>
    <table summary="" width="100%">
      <tr>
        <td align="left">
        <a style='font-size:70%' href='ppsmq.php'>PPSMQ</a></td>
        <td align="right">
        <a style='font-size:70%' href='mailto:rfrank@rfrank.net'>CONTACT</a></td>
      </tr>
    </table>
  </div>
  </body>
</html>
FOOT;
}
