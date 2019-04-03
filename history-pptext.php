<?php

output_header();

echo <<<BODY
<p><b>Revision history for pptext</b> (last 30 days)</p>

<table>
<!--
<tr>
	<td valign='top' style='color:green'>2019.01.03</td>
	<td style='padding-left:1em'>corrections to "Mr." and "Mr" evaluations.<br/>
default output filename changed from <i>result.txt</i> to <i>report.txt</i></td>
</tr>
<tr>
	<td valign='top' style='color:green'>2019.01.04</td>
	<td style='padding-left:1em'>major internal restructuring. report lists
	now alphabetic for characters and spelling. minor bug fixes.</td>
</tr>
<tr>
	<td valign='top' style='color:green'>2019.01.05</td>
	<td style='padding-left:1em'>added HTML report with color highlighting. Also
	accepts source file with intentional long (unwrapped) lines.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.06</td>
  <td style='padding-left:1em'>added hyphenation consistency report. added line
  after short line to display. corrected repeated word at end of line algorithm.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.07</td>
  <td style='padding-left:1em'>handle abandoned tags without affecting HTML
  report. Handle mixed numerics (31st, 2nd, 4th, etc.)</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.08</td>
  <td style='padding-left:1em'>added menu of reports by user request so specific tests
  can be accessed more easily.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.12</td>
  <td style='padding-left:1em'>bugfix for rare occasions where repeated word pairs occurred in close proximity, affecting formatting presentation only.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.13</td>
  <td style='padding-left:1em'>bugfix for repeated word check not accurately
  centering the highlighted result.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.14</td>
  <td style='padding-left:1em'>repeated suspect words not isolated on display. 
  verbose flag application to selected reports.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.18</td>
  <td style='padding-left:1em'>rewrote spacing pattern report to indicate line number followed by pattern of consecutive blank lines.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.25</td>
  <td style='padding-left:1em'>rewrite code to display internal segments of text based on rune counts.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.28</td>
  <td style='padding-left:1em'>handle spell checks of all-capital words that are correct in
title case, such as BRITISH -> British or DAVID -> David.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.30</td>
  <td style='padding-left:1em'>added check for hyphenated word and spaced words, flagging
"half-frozen" and "half frozen"</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.01.31</td>
  <td style='padding-left:1em'>changed the sort order to case-insensitive in Spellcheck Report. changed
  spacing in selected reports for readability. Added flags for "22" and "44" pattern in line spacing checks.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.01</td>
  <td style='padding-left:1em'>added count to the "hyphenation and spaced pair check".
    fixed mixed-case within word algorithm to handle leading apostrophe.
    consolidated ellipsis check.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.02</td>
  <td style='padding-left:1em'>major rewrite for many tests to be apostrophe-aware</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.03</td>
  <td style='padding-left:1em'>normalized non-verbose output limiting reports. accepts
  common fractions (⅐, ⅑, ⅒, ⅓, ⅔, ⅕, ⅖, ⅗, ⅘, ⅙, ⅚, ⅛, ⅜, ⅝, ⅞, ¼, ½, ¾) as numerics</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.04</td>
  <td style='padding-left:1em'>reported line numbers are now 1-based. Lines are numbered
  starting at "1".</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.05</td>
  <td style='padding-left:1em'>long lines reported in length order, longest first. Five
  lines default; all available if verbose.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.07</td>
  <td style='padding-left:1em'>allow multiple wordlists.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.10</td>
  <td style='padding-left:1em'>major internal rewrite. aspell integration.
  significant speed improvements.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.11</td>
  <td style='padding-left:1em'>consolidation and correction to full stop spacing checks.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.12</td>
  <td style='padding-left:1em'>Spellcheck suspect word-finding algorithm</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.13</td>
  <td style='padding-left:1em'>
adds straight apostrophe handling to spellcheck;
adds ability to use hyphenated words in good word list;
spellcheck report in case-insensitive alpha order;
to-day/today checks including verbose report;
allow exception for "&c" forms in unusual character report;
British and American titles enhanced overlap checks;
ellipsis check for ".." pattern added;
added more exceptions for standalone 1;
consolidated dash checks;
edit-distance checks check all words including other suspects;
numeral exceptions for 1st, 2nd, etc.
  </td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.20</td>
  <td style='padding-left:1em'>single report format (HTML)</td>
</tr> 
<tr>
  <td valign='top' style='color:green'>2019.02.21</td>
  <td style='padding-left:1em'>
	wrap report line adjust for near end by 8 characters,
	long lines always shown non-dimmed,
	table separators of long lines of "-" are ignored,
	strings can end with a italic underscore using all other rules,
	better recognition of missing paragraph breaks,
	better handling of unexpected paragraph ends,
	edit distance checks are not restricted to length > 5 characters,
	isolation of words corrected in scanno report
  </td>
</tr> 
<tr>
  <td valign='top' style='color:green'>2019.02.22</td>
  <td style='padding-left:1em'>consolidation and expansion of
  full stop sequence checks.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.23</td>
  <td style='padding-left:1em'>Levenshtein edit distance adjustments for ligatures œ and æ.</td>
</tr>
-->
<tr>
  <td valign='top' style='color:green'>2019.02.25</td>
  <td style='padding-left:1em'>better isolation of paragraph segments for reports.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.02.26</td>
  <td style='padding-left:1em'>excludes pure numeric or Roman numerals from distance checks.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.03.02</td>
  <td style='padding-left:1em'>corrected highlighting in "special situations check"</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.03.05</td>
  <td style='padding-left:1em'>common hut/but isolation</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.03.14</td>
  <td style='padding-left:1em'>smart quote scan algorithm enhancements.
  also direct output to separate file.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.03.15</td>
  <td style='padding-left:1em'>counts added for "Mr./Mr" checks.<br/>
  allow "[S" sequence for sidenotes.<br/>
  hyphenation/spacing and hyphenation/non-hyphenated bugfix</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.03.23</td>
  <td style='padding-left:1em'>dash check rewiteen for better UTF-8 coverage<br/>
  better case-insensitivity in spellcheck.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.03.24</td>
  <td style='padding-left:1em'>rewrote hyphen-space checks for speed improvement.</td>
</tr>
<tr>
  <td valign='top' style='color:green'>2019.03.28</td>
  <td style='padding-left:1em'>rewrote hyphen-nospace checks for speed improvement.<br/>
  added tick box to disable Levenshtein (edit-distance) checks</td>
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
        <a style='font-size:70%' href='pptext.php'>PPTEXT</a></td>
        <td align="right">
        <a style='font-size:70%' href='mailto:rfrank@rfrank.net'>CONTACT</a></td>
      </tr>
    </table>
  </div>
  </body>
</html>
FOOT;
}
