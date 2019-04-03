<?php

output_header();

echo <<<BODY
<p>Technical info for pphtml.</p>
<ol>
<li>There is a valid CSS construction of <tt style='border: 1px solid silver; background-color:#FFFFCC'>.class1.class2</tt> that pptext does
not handle correctly. That definition means "an element with both class1 and class2" as in
<tt style='border: 1px solid silver; background-color:#FFFFCC'>&lt;hr class='class1 green class2' /></tt>.
Currently, that construction will show up as "defined but not used".
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
