<?php
require_once("base.inc");

output_header();
output_content();

function output_content()
{
    $max_upload_size = ini_get("upload_max_filesize");
    global $docs_url, $help_url;
echo <<<MENU
<p>Welcome to the Post-Processing Workbench. Post Processors usually generate
two formats for each book: text and HTML. This page provides links to
programs that may be used to analyze each format:</p>

<ul>
    <li><a href='./pptext.php'>pptext</a> to analyze a text file</li>
    <li><a href='./pphtml.php'>pphtml</a> to analyze an HTML file and supplemental folders</li>
</ul>

<p>Two additional programs are provided here. One converts “straight” quotes in
a text file into smart or “curly” quotes. The other program is used to compare two
files. Follow these links for details and to access the programs:</p>

<ul>
    <li><a href='./ppsmq.php'>ppsmq</a> to convert straight quotes to curly quotes</li>
    <li><a href='./ppcomp.php'>ppcomp</a> to compare two files, text or HTML mixed</li>
</ul>

<p><i>The maximum upload size for this system is $max_upload_size.</i></p>

<p>Please remember to also run these tests:</p>
  <ul style='margin-top:0'>
    <li><a href='https://ebookmaker.pglaf.org/'>Gutenberg online bookmaker</a></li>
    <li><a href="http://validator.w3.org/">W3C HTML markup validator</a></li>
    <li><a href="http://jigsaw.w3.org/css-validator/">W3C CSS validator</a></li>
    <!-- <li><a href="http://validator.w3.org/checklink">W3C Link Checker</a></li> -->
  </ul>

<p><a href='$docs_url'>Documentation</a> and <a href='$help_url'>help</a> is available as well.</p>

MENU;
}

