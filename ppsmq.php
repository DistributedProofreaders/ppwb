<?php
require_once("base.inc");

output_header("ppsmq", ["techinfo-ppsmq.php" => "TECH INFO"]);
output_content();

function output_content()
{
echo <<<MENU

  <p>The ppsmq program attempts to convert a file with straight quotes into one
  with curly quotes. It can work with either a plain text file or an HTML
  file. It only makes changes it's pretty certain are
  correct. It will flag those it is uncertain about with a "@"
  character. It will leave alone any straight quotes it can't reliably
  classify.</p>

  <p>Even if you don't intend to convert your book to curly quotes,
    anything that this program flags is often an error and should
    be investigated.</p>

  <p>If you are converting,
    once you've run the program and downloaded the result file, search
  everywhere for "@" and manually enter the correct punctuation, then
  remove the "@" flag. Then search for straight double quotes (there
  should be none) and straight single quotes (there usually are
  some).</p>

  <p>If you run this on an HTML file, you will find it converts all
  single quotes inside HTML tags to "∮" and all double quotes in tags to "∯"
  to protect them. The process is the same as in the previous paragraph.
  For HTML, there is an added step to replace all "∮" with single quotes and
  all "∯" with double quotes to restore the HTML tags.</p>
  
  <p>Please note that all HTML tags must begin and end on a single line for
  this to work—the beginning &lt; must be in the same row with the &gt;.
  Otherwise the quote marks will be converted to curly quotes which will
  cause problems with the HTML.</p>

<form target="_blank" action="ppsmq-action.php" method="POST" enctype="multipart/form-data">
<table>
  <tr>
          <td style='text-align:right'><label for='userfile'>User text file </label></td>
          <td><input type="file" name="userfile" autocomplete=off /></td>
  </tr>
</table>
<div style='margin-top:1em; margin-bottom:0em;'><input type="submit" value="Submit" name="upload"/></div>
</form>

MENU;
}

