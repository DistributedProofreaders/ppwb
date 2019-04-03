<?php

output_header();

echo <<<BODY
<p><b>Technical Notes for pptext</b></p>

<ul class='circle'>
<li><a href='#spk'>spellcheck</a></li>
<li><a href='#sqs'>smart quote scan</a></li>
<li><a href='#prt'>pptext run time</a></li>
<li><a href='#nlc'>Levenshtein checks</a></li>
<li><a href='#spr'>"spacing pattern" report</a></li>
<li><a href='#jee'>jeebies report</a></li>
<li><a href='#ver'>verbose operation</a></li>
</ul>

<hr class='thin' />

<p id='spk' class='emph'>Spellcheck</p>

<p>User's should be aware that to reduce the number of false positives,
the spellcheck algorithm uses reduction techniques before presenting words
to aspell, which is used internally. In particular, if a word occurs in the
text spelled the same way at least five times, it is considered spelled
correctly. This is useful for names of people, places or things that occur
frequently.</p>

<p id='sqs' class='emph'>Smart Quote Scan</p>

<p>The pptext program incorporates algorithms to detect when
smart or “curly” quotes may be incorrect. This is a very difficult
problem and one that cannot be solved completely given the
ambiguity of the English language and the use of the same symbol (’) for
an apostrophe and a close single quote.</p>

<p>The success of the analysis depends very heavily on rules to determine
what is an apostrophe and what is a close single quote. Here is an
abbreviated version of the classification algorithm used:</p>

<ol>
<li>the "’" character in a words in the good word is always considered an apostrophe</li>
<li>the "’" character inside a word is an apostrophe.</li>
<li>common contractions that end with an apostrophe are protected (such as "an’")</li>
<li>common contractions that start with an apostrophe are protected (such as "’twas")</li>
<li>all words that have a leading "’" and occur frequently are considered as
  using apostrophes</li>
<li>short phrases that occur often are considered as using open and
close single quotes (such as "‘good job’")</li>
<li>if a word ends in "in’", change the ending to "ing" and then see if
that is a valid word. If it is, consider the "’" as a apostrophe, else
it's a close single quote</li>
<li>if a word starts with "’", change the start to "h" and then see if
that is a valid word. If it is, consider the "’" as a apostrophe, else
it's considered a close single quote</li>
<li>if a word ends in "s’", change the ending to drop the "s’" and then see if
that is a valid word. If it is, consider the "’" as a apostrophe (plural
possessive), else it's a close single quote</li>
</ol>

<p>After all that, the program does a stateful scan of the text. "Stateful" means
it knows what to expect and is not considering near-context. For example, an
open double quote followed by another open double quote in the same paragraph
without an intervening open single quote will be flagged. Another example: a
paragraph that ends with some punctuatioon unresolved, such as an open quote
that was never closed, will be flagged. The scanner has to look-ahead to see
if it's a continued quote, etc. There's a lot to keep track of.</p>

<p>The format of the smart quote scan report is the original file with
anything suspicious marked with the "@" character and a four letter code of
what pptext thinks might be wrong. Here are the abbreviations used:</p>

<ul>
<li>[@NESK] non-empty stack and end of paragraph, with list of what's still there</li>
<li>[@CODQ] consecutive open double quotes</li>
<li>[@UCDQ] unclosed double quote</li>
<li>[@COSQ] consecutive open single quotes</li>
<li>[@UCSQ] unclosed single quote</li>
</ul>

<p>The smart quote scan regularly finds errors that no other test discovers. However,
a byproduct of its scrutiny is an often sizable list of false positives. These
usually can be eliminated immediately by a quick evaluation of the error in 
context. This is one reason the "@" flags are injected into the source file in
the scanreport.txt file presented to the user.</p>

<p>For ongoing development analysis of the smart quote scan component
of pptext, see <a href='sqscan-notes.html'>this file</a></p>

<p id='prt' class='emph'>Notes on pptext run time</p>
<p>Even compiled, pptext can take thirty seconds or more to run all tests. This is
much faster than uploading individual files to the DP Workbench for several reasons.
First, there is only one upload of the text file (and optional good_words file).
Since it is text, a much faster virus check can be made. Finally, running compiled code
is faster than running interpreted code, such as with Python.</p>
<p>The run time can be reduced significantly if the curly-quote checks are excluded
from the run. Those checks run a state machine over each character in the file,
keeping track of what is legal and what isn't at each character position. Disable this
compute-intensive check for any run where you are only interested in other tests' results.</p>

<p id='nlc' class='emph'>Notes on Levenshtein checks</p>
<p>The pptext program does Levenshtein or "edit-distance" checks on the supplied
UTF-8 text file. The Levenshtein distance between two words
is the minimum number of single-character edits (insertions,
deletions or substitutions) required to change one word into the other.</p>

<p>Here is a part of a report of two words with an edit distance of 1.
It includes how many times each word occurred (once and nine times in
the first report), and the line and line number illustrating each word in the text.</p>

<pre>Marañon(1):Marañón(9)
      12077: rivers Uriaparia and Marañon, and this one of La Plata. I answered
      1047: Gran Chaco, of Alvarado and Mercadillo in the valleys of the Marañón

out-going(1):outgoing(4)
      8437: out-going force of sex-energy. The family relations
      9798: This outgoing impulse among members of</pre>

<p>Edit distance checks do not compare every word in the file. For example, it will
not report that "think" and "thing" are one edit apart, differing by only the last
character. If it did that, there would be many hundreds of false positives making
the run report impractical. Edit distance checks require that at least one of the words
is not a dictionary word or that at least one of them is hyphenated.</p>

<p id='spr' class='emph'>Notes on "spacing pattern" report</p>
<p>The spacing pattern is a visual presentation of the book's use of vertical spaces.
Here is a display from a recent book.

<pre>     0 <span style='color:red'>3</span>11
    12 41..1
    28 412
    39 4
    47 <span style='color:red'>3</span>
    52 4221..1
   332 421..1
   731 421..1
  1060 421..1
  1407 421..1
  1841 421..1
  2015 421..1
  2358 41..1
  2701 421..1
  3061 421..1
  3275 41..1</pre>
<p>The format is line number followed by the spacing pattern. For example line 731 has
4 spaces. The next gap is 2 spaces (after the chapter title) and then th rest is a
series of paragraphs.</p>

<p>Notice that any "3" is highlighted in red because three consecutive spaces
is uncommon in DP texts. Notice also the pattern <tt>2358 41..1</tt> that shows an
error: only that chapter heading has one space instead of two after the title.</p>

<p id='jee' class='emph'>Notes on Jeebies report</p>
<p>The pptext program includes he/be substitution checks. OCR scanning often confuses the letter
"h" and the letter "b" when scanning "he" or "be" in the source text. That leads to
sentences such as:</p>
<pre>The question must he asked: is it worth using distinct wordlists?
Why be considered any other approach is a mystery.</pre>
<p>The jeebies processing is very simplistic. The he/be data file contains the
sequence "must|be|asked" but does not contain "must|he|asked". Similarly it
contains "why|he|considered" but not "why|be|considered". Because the
other form of each is present, both will be flagged and
show up in the report like this:</p>
<pre>why be considered
    using distinct wordlists? Why be considered any other
must he asked
    countries. The question must he asked: is it worth</pre>
<p>Occasionally both forms will be in the he/be list. If that happens, if the
less-common form is the one found in the text, it is shown along with the
ratio of the more-common form to the one in the text. This all depends on
if one or the other form was found in a massive scan of many texts to build
the original he/be data file. If there is a he/be error and neither form is
in the data file of over 100,000 he/be sequences, it will be missed.
Consider these two sentences:</p>
<pre>He would not let his son he thrown into battle.
He would not let his daughter he thrown into battle.</pre>
<p>Both are clearly wrong, but only the second one will be caught because
"daughter|be|thrown" is the only one in the he/be list. Jeebies is useful
for many he/be errors but will miss some due
to the limitations of its template-based approach.</p>

<p id='ver' class='emph'>verbose operation</p>
<p>For many error checks, pptext limits the number of reports. You may see five reports
and then see "...3 more". To disable the limit
and always show all reports, the verbose switch is provided.</p>

<!--
<p id='itemid' class='emph'>itemdesc</p>
<p>writeup</p>
-->

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
