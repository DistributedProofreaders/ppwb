# dpwb
Post Processor Workbench

This is a set of tools used in the final stage of ebook production: post-processing.
It consists of a user-visible page (index.php) with links to programs for text analysis
(see pptext.php), HTML analysis (see pphtml.php), "smart quote" processing
(see ppsmq.php) and a file compare tool (see ppcomp.php). Two external binaries are
required: pptext and pphtml, both written in the GO language and in their own
git repositories (asylumcs/pptext, asylumcs/pphtml).
