<?php
require_once("base.inc");

list($workdir, $workurl, $upid) = init_workdir();

// ----- process the first file ---------------------------------

$target_name1 = process_file_upload("userfile1", $workdir);

// ----- process the second file ---------------------------------

$target_name2 = process_file_upload("userfile2", $workdir);

// ----- process user options ---------------------------------------

$options = [];

// list of available boolean options; these are checkboxes on the page
// and the names map directly to the ppcomp.py args
$available_boolean_options = [
    'ignore-format',
    'suppress-footnote-tags',
    'suppress-illustration-tags',
    'suppress-sidenote-tags',
    'ignore-case',
    'extract-footnotes',
    'ignore-0-space',
    'suppress-nbsp-num',
    'suppress-word-join',
    'suppress-proofers-notes',
    'regroup-split-words',
    'css-greek-title-plus',
    'css-add-illustration',
    'css-add-sidenote',
    'css-no-default',
    'without-html-header'
];

foreach($available_boolean_options as $option) {
    if(isset($_POST[$option])) {
        $options[] = "--$option";
    }
}

if(isset($_POST['css-smcap'])){
    $options[] = "--css-smcap U";
}

if(isset($_POST['txt-cleanup-type'])){
    $options[] = "--txt-cleanup-type " . escapeshellarg($_POST['txt-cleanup-type']);
}

// ----- no errors. proceed ----------------------------------------

log_tool_access("ppcomp", $upid);

// ----- run the ppcomp command ----------------------------------------

$command = join(" ", [
    $python_runner,
    "$base_codedir/bin/ppcomp/ppcomp/ppcomp.py",
    join(" ", $options),
    escapeshellarg($target_name1),
    escapeshellarg($target_name2)
]);

log_tool_action($workdir, "command", $command);

$exit_code = run_command($command, $output, $error);

log_tool_action($workdir, "error", $error);

// ----- display results -------------------------------------------

output_header("ppcomp Results");

$reportok = false;

echo "<p>";
if($exit_code == 0) {
    file_put_contents("$workdir/result.html", $output);
    echo "results available: <a href='$workurl/result.html'>here</a>.<br>";
    $reportok = true;
}
if ($reportok) {
    echo "Left click to view. Right click to download.</p>";
} else {
    $output = nl2br($output);
    echo "<p>Whoops! Something went wrong and no output was generated.
    The error message was<br><br>
    <tt>$output</tt></p>
    </p>For more assistance, ask in the <a href='$help_url'>discussion topic</a> and include this identifier: $upid</p>";
}
