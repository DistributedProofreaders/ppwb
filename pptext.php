<?php
require_once("base.inc");

output_header("pptext", ["techinfo-pptext.php" => "TECH INFO"], get_js());
output_content();

function output_content()
{
    // available dictionaries installed on the server
    $dictionaries = [
        "en" => [
            "en" => "English",
            "en_US" => "English (US)",
            "en_GB" => "English (GB)",
            "en_CA" => "English (CA)",
        ],
        "af" => "Afrikaans",
        "da" => "Danish",
        "nl" => "Dutch",
        "eo" => "Esperanto",
        "fr" => "French",
        "gl" => "Galician",
        "de" => [
            "de" => "German",
            "de-alt" => "Alt. German",
        ],
        "grc" => "Greek",
        "it" => "Italian",
        "la" => "Latin",
        "pt" => "Portuguese",
        "ro" => "Romanian",
        "es" => "Spanish",
    ];

    // build checkboxes
    $dictionary_html = "";
    foreach ($dictionaries as $code => $language) {
        if (!is_array($language)) {
            $language = [$code => $language];
        }
        $dictionary_html .= "<tr>";
        foreach($language as $code => $locale_name) {
            $checked = ($code == "en") ? "checked" : "";
            $dictionary_html .= "<td align='right' style='padding-left:30px'>$locale_name: <input type='checkbox' name='wlangs[]' value='$code' autocomplete='off' $checked/></td>";
        }
        $dictionary_html .= "</tr>";
    }

echo <<<MENU

<p>This page provides a consolidated pptext program used to check books before
posting them to Project Gutenberg. This program checks a UTF-8 or a Latin-1 text file. A
related program, <a href='pphtml.php'>pphtml</a>, checks the HTML version.

<p>This program is designed to work on an input text file(s) with certain characteristics:</p>

<ol>
<li>for punctuation checks, curly quotes are required</li>
<li>punctuation style is American (double-quotes used for quotations)</li>
<li>if there is a good words text file, it can be any legal filename</li>
<li>for very large files, it is suggested to
"skip edit distance checks" to significantly reduce run time.</li>
<li>by default, all tests are run. You can choose to enable or disable individual tests.
<li>filenames must not contain an apostrophe</li>
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
      $dictionary_html
      </table>
    </div>

    <br/>

<div>Select/unselect Tests<br />
    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name='rat" value="Yes" id='rat' autocomplete="off" class="chk_boxes">
    <label for="rat">run all tests</label><br/>

    <hr style='border:none; border-bottom:1px solid silver; width:10%; float:left; margin-left:24px;' /><br />

    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="rspl" value="Yes" id="rspl" autocomplete="off" class="chk_boxes1">
    <label for="rspl">run spellcheck</label><br/>    

    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="redi" value="Yes" id="redi" autocomplete="off" class="chk_boxes1">
    <label for="redi">run edit distance check</label><br/>

    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="rtxt" value="Yes" id="rtxt" autocomplete="off" class="tchk chk_boxes1 chk_t">
    <label for="rtxt">run text checks</label><br/>

    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="rthc" value="Yes" id="rthc" autocomplete="off" class="tchks chk_boxes1 chk_t1">
    <label for="rthc">&nbsp;&nbsp;&nbsp;&nbsp;run hyphen consistency</label><br/>

    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="rhsc" value="Yes" id="rhsc" autocomplete="off" class="tchks chk_boxes1 chk_t1">
    <label for="rhsc">&nbsp;&nbsp;&nbsp;&nbsp;run hyphen-space consistency</label><br/>

    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="rjee" value="Yes" id="rjee" autocomplete="off" class="chk_boxes1">
    <label for="sqc">run jeebies check</label><br/>

    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="rsqc" value="Yes" id="rsqc" autocomplete="off" class="chk_boxes1">
    <label for="rsqc">run smart quote check</label><br/>

    <br/>
    <input type="checkbox" name="ver" value="Yes" id="ver" autocomplete="off">
    <label for="ver">verbose operation</label><br/>

    <div style='margin-top:1em; margin-bottom:0em;'><input type="submit" value="Submit" name="upload"/></div>

</form>
MENU;
}

function get_js()
{
    return <<<JS
    $(document).ready(function() {
        $('.chk_boxes').click(function(){
            $('.chk_boxes1').prop('checked',this.checked);
        });
        $('.chk_t').click(function(){
            $('.chk_t1').prop('checked',this.checked);
        });
        $('.chk_boxes1').click(function(){
            $('.chk_boxes').prop('checked',false);
        });
        $('.tchks').click(function(){
            $('.tchk').prop('checked',true);
        });
        $('.chk_boxes1').prop('checked',true);
        $('.chk_boxes').prop('checked',true);       
    });
JS;
}

