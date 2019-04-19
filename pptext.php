<?php

output_header();
output_content();
output_footer();

function output_content()
{
echo <<<MENU

<p>This page provides a consolidated pptext program used to check books before
posting them to Project Gutenberg. This program checks a UTF-8 or a Latin-1 text file. A
related program, <a href='pphtml.php'>pphtml</a>, checks the HTML version.

<p>This program is designed to work on an input text file(s) with certain characteristics:</p>

<ol>
<li>for punctuation checks, curly quotes are required</li>
<li>punctuation style is American (double-quotes used for quotations)</li>
<li>if there is a good words text file, it can be any legal filename</li>
<li>for very large files, it is suggested to check "skip time-expensive checks"
and perhaps also "skip edit distance checks" to significantly reduce run time. 
</ol>

<p>To use this program, drag and drop a text file onto the top "Browse" button below.
The text file is the book you want to check. 
You may also drag and drop a "good words" file onto the second "Browse" button if you choose.
Choose one or more languages and any options you want. Then click "Submit" and wait
about 30 seconds. Even compiled, a tremendous amount of processing has to get done. 
Very large text files (roughly 1 meg) could take up to 15 minutes.
When it's
finished you should see a screen announcing "Pptext Results" with a link to the results
of the run. If the Smart Quote Check generated a report, a link to that report will also
be provided on the Results page.
Left click to view or right click the link to download the results.</p>

<form target="_blank" action="pptext-action.php" method="POST" enctype="multipart/form-data">

    <table>
      <tr>
          <td style='text-align:right'><label for='userfile'>User text file </label></td>
          <td><input type="file" name="userfile" autocomplete=off /></td>
      </tr>
      </tr>
          <td style='text-align:right'><label for='goodfile'>Good words file (optional)</label></td>
          <td><input type="file" name="goodfile" autocomplete=off /></td>
      </tr>
    </table>

    <div>Select wordlist language(s)<br />
      <table style='margin-left: 50px;'>
      <tr>
      <td align='right' style='padding-left:30px'>English: <input type="checkbox" checked="yes" name="wlangs[]" value="en" autocomplete=off /></td>
      <td align='right' style='padding-left:30px'>English (US): <input type="checkbox" name="wlangs[]" value="en_US" autocomplete=off /></td>
      <td align='right' style='padding-left:30px'>English (GB): <input type="checkbox" name="wlangs[]" value="en_GB" autocomplete=off /></td>
      <td align='right' style='padding-left:30px'>English (CA): <input type="checkbox" name="wlangs[]" value="en_CA" autocomplete=off /></td>
      </tr>
      <tr>
      <td align='right' style='padding-left:30px'>French: <input type="checkbox" name="wlangs[]" value="fr" autocomplete=off /></td>
      <td align='right' style='padding-left:30px'>German: <input type="checkbox" name="wlangs[]" value="de" autocomplete=off /></td>
      <td align='right' style='padding-left:30px'>Alt. German: <input type="checkbox" name="wlangs[]" value="de-alt" autocomplete=off /></td>
      <td align='right' style='padding-left:30px'>Italian: <input type="checkbox" name="wlangs[]" value="it" autocomplete=off /></td>     
      </tr>
      <tr>
	  <td align='right' style='padding-left:30px'>Spanish: <input type="checkbox" name="wlangs[]" value="es" autocomplete=off /></td>
      </tr>
    </table>
    </div>

    <input type="checkbox" name="sqc" value="Yes" id="sqc" autocomplete="off">
    <label for="sqc">skip smart quote check</label><br/>

    <input type="checkbox" name="lev" value="Yes" id="lev" autocomplete="off">
    <label for="sqc">skip edit-distance check</label><br/>

    <input type="checkbox" name="ver" value="Yes" id="ver" autocomplete="off">
    <label for="ver">verbose operation</label><br/>

    <input type="checkbox" name="skipx" value="Yes" id="skipx" autocomplete="off">
    <label for="sqc">skip time-expensive checks</label><br/>

    <div style='margin-top:1em; margin-bottom:0em;'><input type="submit" value="Submit" name="upload"/></div>

</form>
MENU;
$command = escapeshellcmd('bin/pptext -r');
$output = shell_exec($command);
echo "<div style='text-align:right; font-size:70%; color:white;'>pptext version: ".$output."</div>";
}

function output_header()
{
    echo <<<HEAD
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name=viewport content="width=device-width, initial-scale=1">
    <title>PP Workbench: pptext</title>
    <link rel="stylesheet" type="text/css" href="rfrank.css">
  </head>
  <body>
  <div id="header" class='hsty'>pptext</div>
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
        	<a style='font-size:70%' href='techinfo-pptext.php'>TECH INFO</a>
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
