<?php
require_once("base.inc");

output_header("pphtml", ["techinfo-pphtml.php" => "TECH INFO"]);
output_content();

function output_content()
{
echo <<<MENU

<p>Pphtml combines several tests that are applicable to HTML files that have
been prepared to submit to Project Gutenberg. This includes link checking, image
validation, and several tests usually accomplished by Post Processing Verifiers
at DP.</p>

<p>To use this test, prepare a zip file containing a book's HTML file and
the images folder. Drag and drop the zip file onto the Browse button and then
click Submit to run pphtml against your uploaded zip. When it's
finished you should see a screen announcing "Pphtml Results" with a link to the results
of the run. Left click to view or right click the link to download the results.</p>

<form target="_blank" action="pphtml-action.php" method="POST" enctype="multipart/form-data">
<table>
<tr>
    <td style='text-align:right'><label for='userfile'>User zip file (HTML+images)</label></td>
    <td><input type="file" name="userfile" autocomplete=off /></td>
</tr>
</table>
<div style='margin-top:1em; margin-bottom:0em;'><input type="submit" value="Submit" name="upload"/></div>
</form>

MENU;
}

