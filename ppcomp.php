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
uploaded to Project Gutenberg. The user provides two
files to compare. Each file can be text or HTML.
The user then clicks Submit, waits for the test to run and then downloads
or views the results file.</p>

<form target="_blank" action="ppcomp2-action.php" method="POST" enctype="multipart/form-data">

<div>Select options:</div><br />

<input type="checkbox" name="ignore-format" value="No" id="ignore-format" autocomplete="off">
<label for="ignore-format">Silence formating differences</label><br/>

<input type="checkbox" name="suppress-footnote-tags" value="No" id="suppress-footnote-tags" autocomplete="off">
<label for="suppress-footnote-tags">Suppress "[Footnote ?:" marks</label><br/>

<input type="checkbox" name="suppress-illustration-tags" value="No" id="suppress-illustration-tags" autocomplete="off">
<label for="suppress-illustration-tags">Suppress "[Illustration:" marks</label><br/>

<input type="checkbox" name="ignore-case" value="No" id="ignore-case" autocomplete="off">
<label for="ignore-case">Ignore case when comparing</label><br/>

<input type="checkbox" name="extract-footnotes" value="No" id="extract-footnotes" autocomplete="off">
<label for="extract-footnotes">Extract and process footnotes separately</label><br/>

<input type="checkbox" name="suppress-nbsp-num" value="No" id="suppress-nbsp-num" autocomplete="off">
<label for="suppress-nbsp-num">Suppress non-breakable spaces between numbers</label><br/>

<input type="checkbox" name="ignore-0-space" value="No" id="ignore-0-space" autocomplete="off">
<label for="ignore-0-space">HTML: suppress zero width space (U+200b)</label><br/>

<input type="checkbox" name="suppress-proofers-notes" value="No" id="suppress-proofers-notes" autocomplete="off">
<label for="suppress-proofers-notes">In Px/Fx versions, remove [**proofreaders notes]</label><br/>

<input type="checkbox" name="regroup-split-words" value="No" id="regroup-split-words" autocomplete="off">
<label for="regroup-split-words">In Px/Fx versions, regroup split wo-* *rds</label><br/>

<input type="checkbox" name="css-greek-title-plus" value="No" id="css-greek-title-plus" autocomplete="off">
<label for="css-greek-title-plus">HTML: use greek transliteration in title attribute</label><br/>

<input type="checkbox" name="css-add-illustration" value="No" id="css-add-illustration" autocomplete="off">
<label for="css-add-illustration">HTML: add [Illustration ] tag</label><br/>

<input type="checkbox" name="css-add-sidenote" value="No" id="css-add-sidenote" autocomplete="off">
<label for="css-add-sidenote">HTML: add [Sidenote: ...]</label><br/>

<input type="checkbox" name="css-no-default" value="No" id="css-no-default" autocomplete="off">
<label for="css-no-default">HTML: do not use default transformation CSS</label><br/>

<input type="checkbox" name="without-html-header" value="No" id="without-html-header" autocomplete="off">
<label for="without-html-header">HTML: do not output html header and footer</label><br/>

<br />
<div>TXT: Type of text cleaning:</div>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="txt-cleanup-type" value="n" checked="Yes" /> none
&nbsp;&nbsp;&nbsp;
<input type="radio" name="txt-cleanup-type" value="b" /> best effort
&nbsp;&nbsp;&nbsp;
<input type="radio" name="txt-cleanup-type" value="p" /> proofers
<br />


<br />
<table>
<tr>
    <td style='text-align:right'><label for='userfile1'>file1:</label></td>
    <td><input type="file" name="userfile1" autocomplete=off /></td>
</tr>
<tr>
    <td style='text-align:right'><label for='userfile2'>file2:</label></td>
    <td><input type="file" name="userfile2" autocomplete=off /></td>
</tr>
</table>
<div style='margin-top:1em; margin-bottom:0em;'><input type="submit" value="Submit" name="upload"/></div>
</form>





<p>Note: The ppcomp program program was originally written as the standalone
program comp_pp.py by bibimbop at PGDP as part of his
<a href='https://pptools.tangledhelix.com'>PPTOOLS</a> program.
It is used as part of the PP Workbench with permission. 
</p>
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
