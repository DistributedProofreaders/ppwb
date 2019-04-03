<?php

output_header();

echo <<<BODY
<p><b>Revision history for pphtml</b></p>

<table>
<tr>
	<td valign='top' style='color:green'>2019.01.08</td>
	<td style='padding-left:1em'>initial posting of pphtml to rfrank.io</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.09</td>
  <td style='padding-left:1em'>HTML color-coding enhancements</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.18</td>
  <td style='padding-left:1em'>image size limit set to 102400 bytes.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.16</td>
  <td style='padding-left:1em'>image size limit increased to 200k;
    added sorted image size summary; handled comments in CSS and HTML;
    show upload manifest, flag music; no other music processing (yet);
    CSS processing</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.20</td>
  <td style='padding-left:1em'>option clobber; default false</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.22</td>
  <td style='padding-left:1em'>cover image file size message and size to 200K</td>
</tr>

</table>

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
  <div id="header" class='hsty'>pphtml</div>
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
        <a style='font-size:70%' href='pphtml.php'>PPHTML</a></td>
        <td align="right">
        <a style='font-size:70%' href='mailto:rfrank@rfrank.net'>CONTACT</a></td>
      </tr>
    </table>
  </div>
  </body>
</html>
FOOT;
}
