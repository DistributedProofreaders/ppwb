<?php
require_once("base.inc");

output_header("pphtml", ["pphtml.php" => "PPHTML"]);
?>

<p>Technical info for pphtml.</p>
<ol>
<li>There is a valid CSS construction of <tt style='border: 1px solid silver; background-color:#FFFFCC'>.class1.class2</tt> that pphtml does
not handle correctly. That definition means "an element with both class1 and class2" as in
<tt style='border: 1px solid silver; background-color:#FFFFCC'>&lt;hr class='class1 green class2' /></tt>.
Currently, that construction will show up as "defined but not used".
</li>
</ol>

