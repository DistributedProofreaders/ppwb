<?php
require_once("base.inc");

output_header("ppcomp", ["techinfo-ppcomp.php" => "TECH INFO"]);
output_content();

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

<form target="_blank" action="ppcomp-action.php" method="POST" enctype="multipart/form-data">

<div>General options</div>

<input type="checkbox" name="ignore-case" value="No" id="ignore-case">
<label for="ignore-case">Ignore case when comparing</label><br>

<input type="checkbox" name="extract-footnotes" value="No" id="extract-footnotes">
<label for="extract-footnotes">Extract and process footnotes separately</label><br>
<br>

<div>Options for transforming an HTML file:</div>

<input type="checkbox" name="css-add-illustration" value="No" id="css-add-illustration">
<label for="css-add-illustration">Add [Illustration ] tag</label><br>

<input type="checkbox" name="css-add-sidenote" value="No" id="css-add-sidenote">
<label for="css-add-sidenote">Add [Sidenote: ...]</label><br>

<input type="checkbox" name="css-greek-title-plus" value="No" id="css-greek-title-plus">
<label for="css-greek-title-plus">Use greek transliteration in title attribute</label><br>

<input type="checkbox" name="suppress-nbsp-num" value="No" id="suppress-nbsp-num">
<label for="suppress-nbsp-num">Suppress non-breakable spaces between numbers</label><br>

<input type="checkbox" name="suppress-word-join" value="No" id="suppress-word-join">
<label for="suppress-word-join">Suppress word join (NoBreak) (U+2060)</label><br>

<input type="checkbox" name="ignore-0-space" value="No" id="ignore-0-space">
<label for="ignore-0-space">Suppress zero width space (U+200b)</label><br>

<input type="checkbox" name="css-smcap" value="No" id="css-smcap">
<label for="css-smcap">Convert small caps to uppercase</label><br>

<!--
<input type="checkbox" name="bold-replace" value="No" id="bold-replace">
<label for="bold-replace">replace &lt;b>&lt;/b> markup with "="</label><br>
<br>
-->

<div>Options for transforming a text file:</div>

<input type="checkbox" name="suppress-footnote-tags" value="No" id="suppress-footnote-tags">
<label for="suppress-footnote-tags">Suppress "[Footnote ?:" marks</label><br>

<input type="checkbox" name="suppress-illustration-tags" value="No" id="suppress-illustration-tags">
<label for="suppress-illustration-tags">Suppress "[Illustration:" marks</label><br>

<input type="checkbox" name="suppress-sidenote-tags" value="No" id="suppress-sidenote-tags">
<label for="suppress-sidenote-tags">Suppress "[Sidenote:" marks</label><br>

<input type="checkbox" name="use-custom-transform-css" value="No" id="use-custom-transform-css">
<label for="use-custom-transform-css">Use custom transform CSS</label><br>
<textarea id="css" name="css" rows="8" cols="60">
/* Add brackets around footnote anchor */
/*   .fnanchor:before { content: "["; }
     .fnanchor:after { content: "]"; } */

/* .tb {display: none;} */
</textarea><br>
<br>

<div>If comparing with a file from the rounds</div>

<input type="checkbox" name="suppress-proofers-notes" value="No" id="suppress-proofers-notes">
<label for="suppress-proofers-notes">In Px/Fx versions, remove [**proofreaders notes]</label>
<br>

<input type="checkbox" name="regroup-split-words" value="No" id="regroup-split-words">
<label for="regroup-split-words">In Px/Fx versions, regroup split wo-* *rds</label><br>

<input type="checkbox" name="ignore-format" value="No" id="ignore-format">
<label for="ignore-format">Silence formating differences</label><br>

<div style='margin-left:0.2em'>Type of text cleaning:
&nbsp;&nbsp;&nbsp;
<input type="radio" name="txt-cleanup-type" value="b" checked> best effort
&nbsp;&nbsp;
<input type="radio" name="txt-cleanup-type" value="n"> none
&nbsp;&nbsp;
<input type="radio" name="txt-cleanup-type" value="p"> proofers
</div>
<br>

<br>
<table>
<tr>
    <td style='text-align:right'><label for='userfile1'>file1:</label></td>
    <td><input type="file" name="userfile1" id="userfile1"></td>
</tr>
<tr>
    <td style='text-align:right'><label for='userfile2'>file2:</label></td>
    <td><input type="file" name="userfile2" id="userfile2"></td>
</tr>
</table>
<div style='margin-top:1em; margin-bottom:0em;'><input type="submit" value="Submit" name="upload"></div>
</form>





<p>Note: The ppcomp program program was originally written as the standalone
program comp_pp.py by bibimbop at PGDP as part of his
<a href='https://pptools.tangledhelix.com'>PPTOOLS</a> program.
It is used as part of the PP Workbench with permission.
</p>

<script>
function customTransformCssShowHide(e) {
  if ($("#use-custom-transform-css").is(":checked")) {
    $("#css").show();
  } else {
    $("#css").hide();
  }
}
customTransformCssShowHide(false);
$("#use-custom-transform-css").on("click", customTransformCssShowHide);
</script>

MENU;
}
