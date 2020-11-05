# Post Processor Workbench

This is a set of tools created by @asylumcs used in the final stage of ebook
production: post-processing.

The workbench is a web front-end written in PHP that performs a variety
of post-processing activities by calling out to external programs.
It consists of a user-visible page (`index.php`) with links to programs for
text analysis (see `pptext.php`), HTML analysis (see `pphtml.php`),
"smart quote" processing (see `ppsmq.php`) and a file compare tool
(see `ppcomp.php`).

The following two external tools need to be downloaded and installed
in the `bin/` directory:
* [pptext](https://github.com/DistributedProofreaders/pptext)
* [pphtml](https://github.com/DistributedProofreaders/pphtml)
