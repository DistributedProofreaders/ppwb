<?php

output_header();
output_content();
output_footer();

function output_content()
{
echo <<<MENU

<p>The ppcomp program is used to compare two files. It is most useful in
post-processing to compare the concatenated text file downloaded from DP at
the start of PPing to the final text file at the end of PPing before it is
uploaded to Project Gutenberg. The user uploads a zip file containing exactly two
files to compare. Each file can be text or HTML.
The user then clicks Submit, waits for the test to run and then downloads
or views the results file. Filenames should have no spaces in them, nor should there
be any other files or directories/folders in the zip file.</p>

<p>To use this program, prepare a zip file containing two files to compare. Drag
and drop the zip file onto the Browse button and then click Submit. When it's
finished you should see a screen announcing "Ppcomp Results" with a link to
the results of the run. Left click to view or right click the link to download
the results.</p>

<form target="_blank" action="ppcomp-action.php" method="POST" enctype="multipart/form-data">
<table>
<tr>
    <td style='text-align:right'><label for='userfile'>User zip file (containing two files to compare)</label></td>
    <td><input type="file" name="userfile" autocomplete=off /></td>
</tr>
</table>
<div style='margin-top:1em; margin-bottom:0em;'><input type="submit" value="Submit" name="upload"/></div>
</form>

MENU;
}

function output_header()
{
    echo <<<HEAD
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name=viewport content="width=device-width, initial-scale=1">
    <title>PP Workbench: ppcomp</title>
    <link rel="stylesheet" type="text/css" href="rfrank.css">
  </head>
  <body>
  <div id="header" class='hsty'>ppcomp</div>
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
        	<a style='font-size:70%' href='index.php'>MAIN PAGE</a>
        	&nbsp;|&nbsp;
        	<a style='font-size:70%' href='techinfo-ppcomp.php'>TECH INFO</a>
          &nbsp;|&nbsp;
          <a style='font-size:70%' href='history-ppcomp.php'>HISTORY</a>       
        </td>
        <td align="right">
        <a style='font-size:70%' href='mailto:rfrank@rfrank.net'>CONTACT</a></td>
      </tr>
    </table>
  </div>
  </body>
</html>
FOOT;
}
