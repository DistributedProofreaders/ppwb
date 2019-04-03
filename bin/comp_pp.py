#!/usr/bin/env python3

# -*- coding: utf-8 -*-

# comp_pp - compare 2 files

# Copyright (C) 2012-2013 bibimbop at pgdp

# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA

import sys
import os
import re
import argparse
import tempfile
import subprocess
from lxml import etree
import tinycss
import cssselect
#from functools import partial

class SourceFile(object):
    """Represent a file in memory.
    """

    def load_file(self, fname, encoding=None):
        """Load a file (text ot html) and finds its encoding.
        """

        # Keep the full name, the file name and its path
        self.fullname = fname
        self.basename = os.path.basename(fname)
        self.dirname = os.path.dirname(fname)

        try:
            with open(fname, "rb") as f:
                raw = f.read()
        except Exception:
            raise IOError("Cannot load file: " + os.path.basename(fname))

        if len(raw) < 10:
            raise SyntaxError("File is too short: " + os.path.basename(fname))

        # Remove BOM if present
        if raw[0] == 0xef and raw[1] == 0xbb and raw[2] == 0xbf:
            raw = raw[3:]

        # Try various encodings. Much faster than using chardet
        if encoding is None:
            encodings = ['utf-8', 'iso-8859-1']
        else:
            encodings = [encoding]

        for enc in encodings:
            try:
                # Encode the raw data string into an internal unicode
                # string, according to the discovered encoding.
                text = raw.decode(enc)
            except Exception:
                continue
            else:
                return raw, text, enc

        raise SyntaxError("Encoding cannot be found for: " +
                          os.path.basename(fname))


    def count_ending_empty_lines(self, text):
        """Count the number of ending empty lines."""
        self.ending_empty_lines = 0
        for mychar in text[::-1]:
            if mychar == "\n":
                self.ending_empty_lines += 1
            elif mychar == "\r":
                continue
            else:
                break

    def strip_pg_boilerplate(self):
        """Remove the PG header and footer from a text version if present.
        """
        new_text = []
        self.start = 0
        for lineno, line in enumerate(self.text, start=1):
            # Find the markers. Unfortunately PG lacks consistency
            if line.startswith(("*** START OF THIS PROJECT GUTENBERG EBOOK",
                                "*** START OF THE PROJECT GUTENBERG EBOOK",
                                "***START OF THE PROJECT GUTENBERG EBOOK")):
                new_text = []
                self.start = lineno
            elif line.startswith(("*** END OF THIS PROJECT GUTENBERG EBOOK",
                                  "***END OF THIS PROJECT GUTENBERG EBOOK",
                                  "*** END OF THE PROJECT GUTENBERG EBOOK",
                                  "End of the Project Gutenberg EBook of",
                                  "End of Project Gutenberg's",
                                  "***END OF THE PROJECT GUTENBERG EBOOK")):
                break
            else:
                new_text.append(line)

        self.text = new_text


    def parse_html_xhtml(self, name, raw, text, relax=False):
        """Parse a byte array. Find the correct parser. Returns both the
        parser, which contains the error log, and the resulting tree,
        if the parsing was successful.

        If relax is True, then the lax html parser is used, even for
        XHTML, so the parsing will almost always succeed.
        """

        parser = None
        tree = None

        # Get the first 5 lines and find the DTD
        header = text.splitlines()[:5]

        if any(["DTD XHTML" in x for x in header]):
            parser = etree.XMLParser(dtd_validation=True)
        if any(["DTD HTML" in x for x in header]):
            parser = etree.HTMLParser()

        if parser is None:
            raise SyntaxError("No parser found for that type of document: " +
                              os.path.basename(name))

        # Try the decoded file first.
        try:
            tree = etree.fromstring(text, parser)
        except etree.XMLSyntaxError:
            if relax == False:
                return parser, tree
        except Exception:
            pass
        else:
            return parser, tree

        # Try raw string. This will decode files with <?xml ...
        try:
            tree = etree.fromstring(raw, parser)
        except etree.XMLSyntaxError:
            if relax == False:
                return parser, tree
        except Exception:
            pass
        else:
            return parser, tree

        # The XHTML file may have some errors. If the caller really
        # wants a result then use the HTML parser.
        if relax and any(["DTD XHTML" in x for x in header]):
            parser = etree.HTMLParser()
            try:
                tree = etree.fromstring(text, parser)
            except etree.XMLSyntaxError:
                return parser, tree
            except Exception:
                pass
            else:
                return parser, tree

        raise SyntaxError("File cannot be parsed: " +
                          os.path.basename(name))


    def load_xhtml(self, name, encoding=None, relax=False):
        """Load an html/xhtml file. If it is an XHTML file, get rid of the
        namespace since that makes things much easier later.

        If parsing fails, then self.parser_errlog is not empty.

        If parsing succeeded, then self.tree is set, and
        self.parser_errlog is [].
        """
        self.parser_errlog = None
        self.tree = None
        self.text = None

        raw, text, encoding = self.load_file(name, encoding)
        if raw is None:
            raise IOError("File loading failed for: " + os.path.basename(name))

        parser, tree = self.parse_html_xhtml(name, raw, text, relax)

        self.parser_errlog = parser.error_log
        self.encoding = encoding
        self.count_ending_empty_lines(text)

        if len(self.parser_errlog):
            # Cleanup some errors
            #print(parser.error_log[0].domain_name)
            #print(parser.error_log[0].type_name)
            #print(parser.error_log[0].level_name)

            if type(parser) == etree.HTMLParser:
                # HTML parser rejects tags with both id and name
                #   (513 == DTD_ID_REDEFINED)
                self.parser_errlog = [x for x in parser.error_log
                                      if parser.error_log[0].type != 513]

        if len(self.parser_errlog):
            raise SyntaxError("Parsing errors in document: " +
                              os.path.basename(name))

        self.tree = tree.getroottree()
        self.text = text.splitlines()

        # Find the namespace - HOW ?
        # self.tree.getroot().nsmap -> {None: 'http://www.w3.org/1999/xhtml'}
        xmlns = self.tree.getroot().nsmap.get(None, None)
        if xmlns:
            self.xmlns = '{' + xmlns + '}'
        else:
            self.xmlns = ""

        # Remove the namespace from the tags
        # (eg. {http://www.w3.org/1999/xhtml})
        for element in self.tree.iter(tag=etree.Element):
            element.tag = element.tag.replace(self.xmlns, "")

        # Find type of xhtml (10 or 11 for 1.0 and 1.1). 0=html or
        # unknown. So far, no need to differentiate 1.0 strict and
        # transitional.
        if "DTD/xhtml1-strict.dtd" in self.tree.docinfo.doctype or "DTD/xhtml1-transitional.dtd" in self.tree.docinfo.doctype:
            self.xhtml = 10
        elif "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd" in self.tree.docinfo.doctype:
            self.xhtml = 11
        else:
            self.xhtml = 0

        # Remove PG boilerplate. These are kept in a <pre> tag.
        find = etree.XPath("//pre")
        for element in find(self.tree):
            if element.text is None:
                continue

            text = element.text.strip()

            if re.match(r".*?\*\*\* START OF THIS PROJECT GUTENBERG EBOOK.*?\*\*\*(.*)",
                        text, flags=re.MULTILINE | re.DOTALL):
                clear_element(element)

            elif text.startswith("End of the Project Gutenberg") or text.startswith("End of Project Gutenberg"):
                clear_element(element)


    def load_text(self, fname, encoding=None):
        """Load the file as text."""
        raw, text, encoding = self.load_file(fname, encoding)

        if raw is None:
            return

        self.count_ending_empty_lines(text)

        self.text = text.splitlines()
        self.encoding = encoding

        self.strip_pg_boilerplate()





def get_block(pp_text):
    """Generator to get a block of text, followed by the number of empty
    lines.

    """

    empty_lines = 0
    block = []

    for line in pp_text:

        if len(line):
            # One or more empty lines will stop a block
            if empty_lines:
                yield block, empty_lines
                block = []
                empty_lines = 0

            block += [line]

        else:
            empty_lines += 1

    yield block, empty_lines

def extract_footnotes_pp(pp_text, fn_regexes):
    """Extract footnotes from a text file. text is iterable. Returns
    the text as an iterable, without the footnotes, and footnotes as a
    list of (footnote string id, line number of the start of the
    footnote, list of strings comprising the footnote).
    fn_regexes is a list of (regex, fn_type) that identify the beginning
    and end of a footnote. The fn_type is 1 when a ] terminates it, or
    2 when a new block terminates it.
    """

    # If the caller didn't give a list of regex to identify the
    # footnotes, build one, taking only the most common.
    if fn_regexes is None:
        all_regexes = [(r"(\s*)\[([\w-]+)\](.*)", 1),
                       (r"(\s*)\[Note (\d+):( .*|$)", 2),
                       (r"(      )Note (\d+):( .*|$)", 1)]
        regex_count = [0] * len(all_regexes) # i.e. [0, 0, 0]

        for block, empty_lines in get_block(pp_text):
            if not len(block):
                continue

            for i, (regex, fn_type) in enumerate(all_regexes):
                matches = re.match(regex, block[0])
                if matches:
                    regex_count[i] += 1
                    break

        # Pick the regex with the most matches
        fn_regexes = [all_regexes[regex_count.index(max(regex_count))]]

    # Different types of footnote. 0 means not in footnote.
    cur_fn_type, cur_fn_indent = 0, 0
    footnotes = []
    text = []
    prev_block = None

    for block, empty_lines in get_block(pp_text):

        # Is the block a new footnote?
        next_fn_type = 0
        if len(block):
            for (regex, fn_type) in fn_regexes:
                matches = re.match(regex, block[0])
                if matches:
                    if matches.group(2).startswith(("Illustration",
                                                    "Décoration",
                                                    "Décoration", "Bandeau",
                                                    "Logo", "Ornement")):
                        # An illustration, possibly inside a footnote. Treat
                        # as part of text or footnote.
                        continue

                    next_fn_type = fn_type
                    next_fn_indent = matches.group(1)

                    # Update first line of block, because we want the
                    # number outside.
                    block[0] = matches.group(3)
                    break

        # Try to close previous footnote
        if cur_fn_type:
            if next_fn_type:
                # New block is footnote, so it ends the previous footnote
                footnotes += prev_block + [""]
                text += [""] * (len(prev_block) + 1)
                prev_block = None
                cur_fn_type, cur_fn_indent = next_fn_type, next_fn_indent
            elif block[0].startswith(cur_fn_indent):
                # Same indent or more. This is a continuation. Merge with
                # one empty line.
                block = prev_block + [""] + block
            else:
                # End of footnote - current block is not a footnote
                footnotes += prev_block + [""]
                text += [""] * (len(prev_block) + 1)
                prev_block = None
                cur_fn_type = 0

        if not cur_fn_type and next_fn_type:
            # Account for new footnote
            cur_fn_type, cur_fn_indent = next_fn_type, next_fn_indent

        if cur_fn_type and (empty_lines >= 2 or
                            (cur_fn_type == 2 and block[-1].endswith("]"))):
            # End of footnote
            if cur_fn_type == 2 and block[-1].endswith("]"):
                # Remove terminal bracket
                block[-1] = block[-1][:-1]

            footnotes += block
            text += [""] * (len(block))
            cur_fn_type = 0
            block = None

        if not cur_fn_type:
            # Add to text, with white lines
            text += (block or []) + [""] * empty_lines
            footnotes += [""] * (len(block or []) + empty_lines)

        prev_block = block

    return text, footnotes

DEFAULT_TRANSFORM_CSS = '''
                i:before, cite:before, em:before, abbr:before, dfn:before,
                i:after, cite:after, em:after, abbr:after, dfn:after      { content: "_"; }

                /* line breaks with <br /> will be ignored by
                 *  normalize-space(). Add a space in all of them to work
                 *  around. */
                br:before { content: " "; }

                /* Add spaces around td tags. */
                td:after, td:after { content: " "; }

                /* Remove page numbers. It seems every PP has a different way. */
                span[class^="pagenum"] { display: none }
                p[class^="pagenum"] { display: none }
                p[class^="page"] { display: none }
                span[class^="pgnum"] { display: none }
                div[id^="Page_"] { display: none }
                div[class^="pagenum"] { display: none }
            '''

def clear_element(element):
    """In an XHTML tree, remove all sub-elements of a given element.

    We can't properly remove an XML element while traversing the
    tree. But we can clean it. Remove its text and children. However
    the tail must be preserved because it belongs to the next element,
    so re-attach."""
    tail = element.tail
    element.clear()
    element.tail = tail


class pgdp_file(object):
    """Stores and process a DP/text/html file.
    """

    def __init__(self, args):
        self.text = None
        self.words = None
        self.myfile = SourceFile()
        self.args = args

        # œ ligature - has_oe_ligature and has_oe_dp are mutually
        # exclusive
        self.has_oe_ligature = False # the real thing
        self.has_oe_dp = False       # DP type: [oe]

        # Conversion to latin1 ?
        self.convert_to_latin1 = False

        self.transform_func = []

        # Footnotes, if extracted
        self.footnotes = ""

        # First line of the text. This is where <body> is for html.
        self.start_line = 0


    def load(self, filename):
        pass

    def process_args(self, args):
        """Process command line arguments"""
        pass

    def analyze(self):
        pass

    def extract_footnotes(self):
        """Extract the footnotes."""
        pass

    def transform(self):
        """Final transformation pass."""
        pass




class pgdp_file_text(pgdp_file):

    def __init__(self, args):
        super().__init__(args)
        self.from_pgdp_rounds = False
        self.char_text = None

    def load(self, filename):
        """Load the file"""
        self.myfile.load_text(filename)
        self.from_pgdp_rounds = self.myfile.basename.startswith('projectID')

    def analyze(self):
        """Clean then analyse the content of a file. Decides if it is PP version,
        a DP version, ..."""

        # Remember which line the text started
        self.start_line = self.myfile.start

        # Unsplit lines
        self.text = '\n'.join(self.myfile.text)

        # Keep a copy to search for characters
        self.char_text = self.text

        # Check for œ, or [oe]
        if self.text.find('œ') != -1 or self.text.find('Œ') != -1:
            self.has_oe_ligature = True
        elif self.text.find('[oe]') != -1 or self.text.find('[OE]') != -1:
            self.has_oe_dp = True


    def convert(self):
        """Remove markers from the text."""

        if self.args.txt_cleanup_type == "n":
            return

        if self.from_pgdp_rounds:
            # Clean proofers
            self.text = re.sub(r"-----File: \w+.png.*", '', self.text)

        if self.args.txt_cleanup_type == "p":
            # Proofers only. Done.
            return

        # Clean all.
        if self.from_pgdp_rounds:
            self.text = self.text.replace("\n/*\n", '\n\n')
            self.text = self.text.replace("\n*/\n", '\n\n')
            self.text = self.text.replace("\n/#\n", '\n\n')
            self.text = self.text.replace("\n#/\n", '\n\n')
            self.text = self.text.replace("\n/P\n", '\n\n')
            self.text = self.text.replace("\nP/\n", '\n\n')

            if self.args.ignore_format:
                self.text = self.text.replace("<i>", "")
                self.text = self.text.replace("</i>", "")
            else:
                self.text = self.text.replace("<i>", "_")
                self.text = self.text.replace("</i>", "_")

            self.text = re.sub("<.*?>", '', self.text)
            self.text = re.sub("</.*?>", '', self.text)
            self.text = re.sub(r"\[Blank Page\]", '', self.text)

            if self.args.suppress_proofers_notes:
                self.text = re.sub(r"\[\*\*[^]]*?\]", '', self.text)

            if self.args.regroup_split_words:
                self.text = re.sub(r"(\w+)-\*(\n+)\*", r'\2\1', self.text)
                self.text = re.sub(r"(\w+)-\*_(\n\n)_\*", r"\2\1", self.text)
                self.text = re.sub(r"(\w+)-\*(\w+)", r"\1\2", self.text)

        else:
            if self.args.ignore_format:
                self.text = self.text.replace("_", "")

            # Horizontal separation
            self.text = self.text.replace("*       *       *       *       *", "")
            self.text = self.text.replace("*     *     *     *     *", "")


        # Remove [Footnote, [Illustrations and [Sidenote tags
        if self.args.ignore_format or self.args.suppress_footnote_tags:
            self.text = re.sub(r"\[Footnote (\d+): ", r'\1 ', self.text)
            self.text = re.sub(r"\*\[Footnote: ", '', self.text)

        if self.args.ignore_format or self.args.suppress_illustration_tags:
            self.text = re.sub(r"\[Illustrations?:([^]]*?)\]", r'\1', self.text, flags=re.MULTILINE)
            self.text = re.sub(r"\[Illustration\]", '', self.text)

        if self.args.ignore_format or self.args.suppress_sidenote_tags:
            self.text = re.sub(r"\[Sidenote:([^]]*?)\]", r'\1', self.text, flags=re.MULTILINE)

        # Replace -- with real mdash
        self.text = self.text.replace("--", "—")


    def extract_footnotes_pgdp(self):
        # Extract the footnotes from an F round
        # Start with [Footnote ... and finish with ] at the end of a line

        # Note: this is really dirty code. Should rewrite. Don't use
        # cur_fnote[0].

        in_fnote = False        # currently processing a footnote
        cur_fnote = []          # keeping current footnote
        text = []               # new text without footnotes
        footnotes = []

        for line in self.text.splitlines():

            # New footnote
            if "[Footnote" in line:

                if "*[Footnote" in line:
                    # Join to previous - Remove the last from the existing
                    # footnotes.
                    line = line.replace("*[Footnote: ", "")
                    cur_fnote, footnotes = footnotes[-1], footnotes[:-1]
                else:
                    line = re.sub(r"\[Footnote \d+: ", "", line)
                    cur_fnote = [-1, ""]

                in_fnote = True

            if in_fnote:
                cur_fnote[1] = "\n".join([cur_fnote[1], line])

                # Footnote continuation: ] or ]*
                # We don't try to regroup yet
                if line.endswith(']'):
                    cur_fnote[1] = cur_fnote[1][:-1]
                    footnotes.append(cur_fnote)
                    in_fnote = False
                elif line.endswith("]*"):
                    cur_fnote[1] = cur_fnote[1][:-2]
                    footnotes.append(cur_fnote)
                    in_fnote = False

            else:
                text.append(line)

        # Rebuild text, now without footnotes
        self.text = '\n'.join(text)
        self.footnotes = "\n".join([x[1] for x in footnotes])


    def extract_footnotes_pp(self):
        # Extract the footnotes from a PP text version
        # Convert to lines and back
        text, footnotes = extract_footnotes_pp(self.text.splitlines(), None)

        # Rebuild text, now without footnotes
        self.text = '\n'.join(text)
        self.footnotes = '\n'.join(footnotes)


    def extract_footnotes(self):
        if self.from_pgdp_rounds:
            self.extract_footnotes_pgdp()
        else:
            self.extract_footnotes_pp()


    def transform(self):
        """Final cleanup."""
        for func in self.transform_func:
            self.text = func(self.text)

        # Apply transform function to the footnotes
        for func in self.transform_func:
            self.footnotes = func(self.footnotes)


class pgdp_file_html(pgdp_file):

    def __init__(self, args):
        super().__init__(args)

        self.mycss = ""
        self.char_text = None


    def load(self, filename):
        """Load the file"""
        self.myfile.load_xhtml(filename, relax=True)


    def process_args(self, args):
        # Load default CSS for transformations
        if args.css_no_default is False:
            self.mycss = DEFAULT_TRANSFORM_CSS

        # Process command line arguments
        if self.args.css_smcap == 'U':
            self.mycss += ".smcap { text-transform:uppercase; }"
        elif self.args.css_smcap == 'L':
            self.mycss += ".smcap { text-transform:lowercase; }"
        elif self.args.css_smcap == 'T':
            self.mycss += ".smcap { text-transform:capitalize; }"

        if self.args.css_bold:
            self.mycss += "b:before, b:after { content: " + self.args.css_bold + "; }"

        if self.args.css_greek_title_plus:
            # greek: if there is a title, use it to replace the greek. */
            self.mycss += '*[lang=grc] { content: "+" attr(title) "+"; }'

        if self.args.css_add_illustration:
            for figclass in ['figcenter', 'figleft', 'figright']:
                self.mycss += '.' + figclass + ':before { content: "[Illustration: "; }'
                self.mycss += '.' + figclass + ':after { content: "]"; }'

        if self.args.css_add_sidenote:
            self.mycss += '.sidenote:before { content: "[Sidenote: "; }'
            self.mycss += '.sidenote:after { content: "]"; }'

        # --css can be present multiple times, so it's a list.
        for css in self.args.css:
            self.mycss += css


    def analyze(self):
        """Clean then analyse the content of a file."""
        # Empty the head - we only want the body
        self.myfile.tree.find('head').clear()

        # Remember which line <body> was.
        self.start_line = self.myfile.tree.find('body').sourceline - 2

        # Remove PG footer, 1st method
        clear_after = False
        for element in self.myfile.tree.find('body').iter():
            if clear_after:
                element.text = ""
                element.tail = ""
            elif element.tag == "p" and element.text and element.text.startswith("***END OF THE PROJECT GUTENBERG EBOOK"):
                element.text = ""
                element.tail = ""
                clear_after = True

        # Remove PG header and footer, 2nd method
        find = etree.XPath("//pre")
        for element in find(self.myfile.tree):
            if element.text is None:
                continue

            text = element.text.strip()

            # Header - Remove everything until start of book.
            m = re.match(r".*?\*\*\* START OF THIS PROJECT GUTENBERG EBOOK.*?\*\*\*(.*)", text, flags=re.MULTILINE | re.DOTALL)
            if m:
                # Found the header. Keep only the text after the
                # start tag (usually the credits)
                element.text = m.group(1)
                continue

            if text.startswith("End of the Project Gutenberg") or text.startswith("End of Project Gutenberg"):
                clear_element(element)

        # Remove PG footer, 3rd method -- header and footer are normal
        # html, not text in <pre> tag.
        try:
            # Look for one element
            (element,) = etree.XPath("//p[@id='pg-end-line']")(self.myfile.tree)
            while element is not None:
                clear_element(element)
                element = element.getnext()
        except ValueError:
            pass

        # Cleaning is done.

        # Transform html into text for character search.
        self.char_text = etree.XPath("normalize-space(/)")(self.myfile.tree)

        # HTML doc should have oelig by default.
        self.has_oe_ligature = True


    def text_apply(self, element, func):
        """Apply a function to every sub element's .text and .tail,
        and element's .text."""
        if element.text:
            element.text = func(element.text)
        for el in element.iter():
            if el == element:
                continue
            if el.text:
                el.text = func(el.text)
            if el.tail:
                el.tail = func(el.tail)


    def convert(self):
        """Remove HTML and PGDP marker from the text."""

        escaped_unicode_re = re.compile(r"\\u[0-9a-fA-F]{4}")
        def escaped_unicode(m):
            try:
                newstr = bytes(m.group(0), 'utf8').decode('unicode-escape')
            except Exception:
                newstr = m.group(0)

            return newstr

        def new_content(element):
            """Process the "content:" property
            """
            retstr = ""
            for token in val.value:
                if token.type == "STRING":
                    # e.g. { content: "xyz" }
                    retstr += escaped_unicode_re.sub(escaped_unicode, token.value)
                elif token.type == "FUNCTION":
                    if token.function_name == 'attr':
                        # e.g. { content: attr(title) }
                        retstr += element.attrib.get(token.content[0].value, "")
                elif token.type == "IDENT":
                    if token.value == "content":
                        # Identity, e.g. { content: content }
                        retstr += element.text

            return retstr


        # Process each rule from our transformation CSS
        stylesheet = tinycss.make_parser().parse_stylesheet(self.mycss)
        property_errors = []
        for rule in stylesheet.rules:

            # Extract values we care about
            f_transform = None
            f_replace_with_attr = None
            #f_replace_regex = None
            f_text_replace = None
            f_element_func = None
            f_move = None

            for val in rule.declarations:

                if val.name == 'content':
                    # result depends on element and pseudo elements.
                    pass

                elif val.name == "text-transform":
                    if len(val.value) != 1:
                        property_errors += [(val.line, val.column, val.name + " takes 1 argument")]
                    else:
                        v = val.value[0].value
                        if v == "uppercase":
                            f_transform = lambda x: x.upper()
                        elif v == "lowercase":
                            f_transform = lambda x: x.lower()
                        elif v == "capitalize":
                            f_transform = lambda x: x.title()
                        else:
                            property_errors += [(val.line, val.column, val.name + " accepts only 'uppercase', 'lowercase' or 'capitalize'")]

                elif val.name == "_replace_with_attr":
                    f_replace_with_attr = lambda el: el.attrib[val.value[0].value]

                elif val.name == "text-replace":
                    # Skip S (spaces) tokens.
                    values = [v for v in val.value if v.type != "S"]
                    if len(values) != 2:
                        property_errors += [(val.line, val.column, val.name + " takes 2 string arguments")]
                    else:
                        v1 = values[0].value
                        v2 = values[1].value
                        f_text_replace = lambda x: x.replace(v1, v2)

                elif val.name == "display":
                    # Support display none only. So ignore "none" argument.
                    f_element_func = clear_element

                elif val.name == "_graft":
                    values = [v for v in val.value if v.type != "S"]
                    if len(values) < 1:
                        property_errors += [(val.line, val.column, val.name + " takes at least one argument")]
                        continue
                    f_move = []
                    for v in values:
                        print("[", v.value, "]")
                        if v.value == 'parent':
                            f_move.append(lambda el: el.getparent())
                        elif v.value == 'prev-sib':
                            f_move.append(lambda el: el.getprevious())
                        elif v.value == 'next-sib':
                            f_move.append(lambda el: el.getnext())
                        else:
                            property_errors += [(val.line, val.column, val.name + " invalid value " + v.value)]
                            f_move = None
                            break

                    if not f_move:
                        continue

#                elif val.name == "_replace_regex":
#                    f_replace_regex = partial(re.sub, r"(\d)\u00A0(\d)", r"\1\2")
#                    f_replace_regex = partial(re.sub, val.value[0].value, val.value[1].value)

                else:
                    property_errors += [(val.line, val.column, "Unsupported property " + val.name)]
                    continue

                # Iterate through each selectors in the rule
                for selector in cssselect.parse(rule.selector.as_css()):

                    pseudo_element = selector.pseudo_element

                    xpath = cssselect.HTMLTranslator().selector_to_xpath(selector)
                    find = etree.XPath(xpath)

                    # Find each matching element in the HTML/XHTML document
                    for element in find(self.myfile.tree):

                        # Replace text with content of an attribute.
                        if f_replace_with_attr:
                            element.text = f_replace_with_attr(element)

                        if val.name == 'content':
                            v_content = new_content(element)
                            if pseudo_element == "before":
                                element.text = v_content + (element.text or '') # opening tag
                            elif pseudo_element == "after":
                                element.tail = v_content + (element.tail or '') # closing tag
                            else:
                                # Replace all content
                                element.text = new_content(element)

                        if f_transform:
                            self.text_apply(element, f_transform)

                        if f_text_replace:
                            self.text_apply(element, f_text_replace)

                        if f_element_func:
                            f_element_func(element)

                        if f_move:
                            parent = element.getparent()
                            new = element
                            for f in f_move:
                                new = f(new)

                            # Move the tail to the sibling or the parent
                            if element.tail:
                                sibling = element.getprevious()
                                if sibling:
                                    sibling.tail = (sibling.tail or "") + element.tail
                                else:
                                    parent.text = (parent.text or "") + element.tail
                                element.tail = None

                            # Prune and graft
                            parent.remove(element)
                            new.append(element)

                       # if f_replace_regex and element.text:
                       #     element.text = f_replace_regex(element.text)


        css_errors = ""
        if stylesheet.errors or property_errors:
            # There is transformation CSS errors. If the default css
            # is included, take the offset into account.
            i = 0
            if self.args.css_no_default is False:
                i = DEFAULT_TRANSFORM_CSS.count('\n')
            css_errors = "<div class='error-border bbox'><p>Error(s) in the transformation CSS:</p><ul>"
            for err in stylesheet.errors:
                css_errors += "<li>{0},{1}: {2}</li>".format(err.line-i, err.column, err.reason)
            for err in property_errors:
                css_errors += "<li>{0},{1}: {2}</li>".format(err[0]-i, err[1], err[2])
            css_errors += "</ul>"

        return css_errors


    def extract_footnotes(self):
        # Find footnotes, then remove them

        def strip_note_tag(string, keep_num=False):
            """Remove not tag and only keep the number.  For instance
            "Note 123: lorem ipsum" becomes "123 lorem ipsum" or just
            "lorem ipsum".
            """
            for regex in [r"\s*\[([\w-]+)\](.*)",
                          r"\s*([\d]+)\s+(.*)",
                          r"\s*([\d]+):(.*)",
                          r"\s*Note ([\d]+):\s+(.*)"]:
                m = re.match(regex, string, re.DOTALL)
                if m:
                    break

            if m:
                if keep_num:
                    return m.group(1) + " " + m.group(2)
                else:
                    return m.group(2)
            else:
                # That may be bad
                return string

        if self.args.extract_footnotes:
            footnotes = []

            # Special case for PPers who do not keep the marking
            # around the whole footnote. They only mark the first
            # paragraph.
            elements = etree.XPath("//div[@class='footnote']")(self.myfile.tree)
            if len(elements) == 1:
                element = elements[0]

                # Clean footnote number
                for el in element:
                    footnotes += [strip_note_tag(el.xpath("string()"))]

                # Remove the footnote from the main document
                element.getparent().remove(element)
            else:
                for find in ["//div[@id[starts-with(.,'FN_')]]",
                             #  "//div[p/a[@id[starts-with(.,'Footnote_')]]]",
                             "//p[a[@id[starts-with(.,'Footnote_')]]]",
                             "//div/p[span/a[@id[starts-with(.,'Footnote_')]]]",
                             "//div/p[span/a[@id[starts-with(.,'Footnote_')]]]",
                             #"//p[a[@id[not(starts-with(.,'footnotetag')) and starts-with(.,'footnote')]]]",
                             #"//p[a[@id[starts-with(.,'footnote')]]]",
                             "//p[@class='footnote']",
                             "//div[@class='footnote']"]:
                    for element in etree.XPath(find)(self.myfile.tree):

                        # Grab the text and remove the footnote number
                        footnotes += [strip_note_tag(element.xpath("string()"))]

                        # Remove the footnote from the main document
                        element.getparent().remove(element)

                    if len(self.footnotes):
                        # Found them. Stop now.
                        break

            self.footnotes = "\n".join(footnotes)


    def transform(self):
        """Transform html into text. Do a final cleanup."""
        self.text = etree.XPath("string(/)")(self.myfile.tree)

#        ff=open("compfilehtml.txt", "w")
#        ff.write(self.text)
#        ff.close()

        # Apply transform function to the main text
        for func in self.transform_func:
            self.text = func(self.text)

        # Apply transform function to the footnotes
        for func in self.transform_func:
            self.footnotes = func(self.footnotes)

        # zero width space
        if self.args.ignore_0_space:
            self.text = self.text.replace(chr(0x200b), "")


class CompPP(object):
    """Compare two files.
    """

    def __init__(self, args):
        self.args = args

    def oelig_convert(self, convert_oelig, text):
        # Do the required oelig conversion
        if convert_oelig == 1:
            text = text.replace(r"[oe]", "oe")
            text = text.replace(r"[OE]", "OE")

        elif convert_oelig == 2:
            text = text.replace(r"[oe]", "œ")
            text = text.replace(r"[OE]", "Œ")

        elif convert_oelig == 3:
            text = text.replace("œ", "oe")
            text = text.replace("Œ", "OE")

        return text

    def latin1_convert(self, text):
        # Convert some UTF8 characters to latin1
        text = text.replace("’", "'")
        text = text.replace("‘", "'")
        text = text.replace('“', '"')
        text = text.replace('”', '"')

    #    text = text.replace('in-4º', 'in-4o')
    #    text = text.replace('in-8º', 'in-8o')
    #    text = text.replace('in-fº', 'in-fo')
        text = text.replace('º', 'o')

        return text


    def convert_to_words(self, text):
        """Split the text into a list of words from the text."""

        # Split into list of words
        words = []

        for line in re.findall(r'([\w-]+|\W)', text):
            line = line.strip()

            if line != '':
                words.append(line)

        return words


    def compare_texts(self, text1, text2, debug=False):
        # Compare two sources
        # We could have used the difflib module, but it's too slow:
        #    for line in difflib.unified_diff(f1.words, f2.words):
        #        print(line)
        # Use diff instead.

        # Some debug code
        if False and debug:
            f = open("/tmp/text1", "wb")
            f.write(text1.encode('utf-8'))
            f.close()
            f = open("/tmp/text2", "wb")
            f.write(text2.encode('utf-8'))
            f.close()

        with tempfile.NamedTemporaryFile(mode='wb') as t1, tempfile.NamedTemporaryFile(mode='wb') as t2:

            t1.write(text1.encode('utf-8'))
            t2.write(text2.encode('utf-8'))

            t1.flush()
            t2.flush()

            repo_dir = os.environ.get("OPENSHIFT_DATA_DIR", "")
            if repo_dir:
                dwdiff_path = os.path.join(repo_dir, "bin", "dwdiff")
            else:
                dwdiff_path = "dwdiff"

            cmd = [dwdiff_path,
                   "-P",
                   "-R",
                   "-C 2",
                   "-L",
                   "-w ]COMPPP_START_DEL[",
                   "-x ]COMPPP_STOP_DEL[",
                   "-y ]COMPPP_START_INS[",
                   "-z ]COMPPP_STOP_INS["]

            if self.args.ignore_case:
                cmd += ["--ignore-case"]

            cmd += [t1.name, t2.name]

            # That shouldn't be needed if openshift was utf8 by default.
            env = os.environ.copy()
            env["LANG"] = "en_US.UTF-8"

            p = subprocess.Popen(cmd,
                                 stdout=subprocess.PIPE,
                                 env=env)

            # The output is raw, so we have to decode it to UTF-8, which
            # is the default under Ubuntu.
            return p.stdout.read().decode('utf-8')


    def create_html(self, files, text, footnotes):

        def massage_input(text, start0, start1):
            # Massage the input
            text = text.replace("&", "&amp;")
            text = text.replace("<", "&lt;")
            text = text.replace(">", "&gt;")

            text = text.replace("]COMPPP_START_DEL[", "<del>")
            text = text.replace("]COMPPP_STOP_DEL[", "</del>")
            text = text.replace("]COMPPP_START_INS[", "<ins>")
            text = text.replace("]COMPPP_STOP_INS[", "</ins>")

            if text:
                text = "<hr /><pre>\n" + text
            text = text.replace("\n--\n", "\n</pre><hr /><pre>\n")

            text = re.sub(r"^\s*(\d+):(\d+)",
                          lambda m: "<span class='lineno'>{0} : {1}</span>".format(int(m.group(1)) + start0,
                                                                                   int(m.group(2)) + start1),
                          text, flags=re.MULTILINE)
            if text:
                text = text + "</pre>\n"


            return text

        # Find the number of diff sections
        nb_diffs_text = 0
        if text:
            nb_diffs_text = len(re.findall("\n--\n", text)) + 1

        nb_diffs_footnotes = 0
        if footnotes:
            nb_diffs_footnotes = len(re.findall("\n--\n", footnotes or "")) + 1

        # Text, with correct (?) line numbers
        text = massage_input(text, files[0].start_line, files[1].start_line)

        # Footnotes - line numbers are meaningless right now. We could fix
        # that.
        footnotes = massage_input(footnotes, 0, 0)

        html_content = "<div>"

        if nb_diffs_text == 0:
            html_content += "<p>There is no diff section in the main text.</p>"
        elif nb_diffs_text == 1:
            html_content += "<p>There is " + str(nb_diffs_text) + " diff section in the main text.</p>"
        else:
            html_content += "<p>There are " + str(nb_diffs_text) + " diff sections in the main text.</p>"

        if footnotes:
            html_content += "<p>Footnotes are diff'ed separately <a href='#footnotes'>here</a></p>"
            if nb_diffs_footnotes == 0:
                html_content += "<p>There is no diff section in the footnotes.</p>"
            elif nb_diffs_footnotes == 1:
                html_content += "<p>There is " + str(nb_diffs_footnotes) + " diff section in the footnotes.</p>"
            else:
                html_content += "<p>There are " + str(nb_diffs_footnotes) + " diff sections in the footnotes.</p>"
        else:
            if self.args.extract_footnotes:
                html_content += "<p>There is no diff section in the footnotes.</p>"

        if nb_diffs_text:
            html_content += "<h2 class='sep4'>Main text</h2>"
            html_content += text

        if footnotes:
            html_content += "<h2 id='footnotes' class='sep4'>Footnotes</h2>"
            html_content += "<pre class='sep4'>"
            html_content += footnotes
            html_content += "</pre>"

        html_content += "</div>"

        return html_content


    def check_char(self, files, char_best, char_other):
        """Check whether each file has the best character. If not, add a
        conversion request.

        This is used for instance if one version uses ’ while the other
        uses '. In that case, we need to convert one into the other, to
        get a smaller diff.
        """

        in_0 = files[0].char_text.find(char_best)
        in_1 = files[1].char_text.find(char_best)

        if in_0 >= 0 and in_1 >= 0:
            # Both have it
            return

        if in_0 == -1 and in_1 == -1:
            # None have it
            return

        # Downgrade one version
        if in_0 > 0:
            files[0].transform_func.append(lambda text: text.replace(char_best, char_other))
        else:
            files[1].transform_func.append(lambda text: text.replace(char_best, char_other))


    def check_oelig(self, files):
        """Similar to check_char, but for oe ligatures."""
        if files[0].has_oe_ligature and files[1].has_oe_ligature:
            pass
        elif files[0].has_oe_dp and files[1].has_oe_dp:
            pass
        elif files[0].has_oe_ligature and files[1].has_oe_dp:
            files[1].transform_func.append(lambda text: text.replace("[oe]", "œ"))
            files[1].transform_func.append(lambda text: text.replace("[OE]", "Œ"))
        elif files[1].has_oe_ligature and files[0].has_oe_dp:
            files[0].transform_func.append(lambda text: text.replace("[oe]", "œ"))
            files[0].transform_func.append(lambda text: text.replace("[OE]", "Œ"))
        else:
            if files[0].has_oe_ligature:
                files[0].transform_func.append(lambda text: text.replace("œ", "oe"))
                files[0].transform_func.append(lambda text: text.replace("Œ", "OE"))
            elif files[1].has_oe_ligature:
                files[1].transform_func.append(lambda text: text.replace("œ", "oe"))
                files[1].transform_func.append(lambda text: text.replace("Œ", "OE"))

            if files[0].has_oe_dp:
                files[0].transform_func.append(lambda text: text.replace("[oe]", "oe"))
                files[0].transform_func.append(lambda text: text.replace("[OE]", "OE"))
            elif files[1].has_oe_dp:
                files[1].transform_func.append(lambda text: text.replace("[oe]", "oe"))
                files[1].transform_func.append(lambda text: text.replace("[OE]", "OE"))


    def do_process(self):

        files = [None, None]

        # Load files
        for i, fname in enumerate(self.args.filename):

            # Look for file type.
            if fname.lower().endswith(('.html', '.htm')):
                files[i] = pgdp_file_html(self.args)
            else:
                files[i] = pgdp_file_text(self.args)

            f = files[i]

            f.load(fname)
            f.process_args(self.args)
            f.analyze()


        # How to process oe ligature
        self.check_oelig(files)

        # How to process punctuation
        # Add more as needed
        self.check_char(files, "’", "'") # curly quote to straight
        self.check_char(files, "‘", "'") # curly quote to straight
        self.check_char(files, "º", "o") # ordinal o to letter o
        self.check_char(files, "ª", "a") # ordinal a to letter a
        self.check_char(files, "–", "-") # ndash to regular dash
        self.check_char(files, "½", "-1/2")
        self.check_char(files, "¼", "-1/4")
        self.check_char(files, "¾", "-3/4")
        self.check_char(files, '”', '"')
        self.check_char(files, '“', '"')
        self.check_char(files, '⁄', '/') # fraction
        self.check_char(files, "′", "'") # prime
        self.check_char(files, "″", "''") # double prime
        self.check_char(files, "‴", "'''") # triple prime
        self.check_char(files, "₁", "1") # subscript 1
        self.check_char(files, "₂", "2") # subscript 2
        self.check_char(files, "₃", "3") # subscript 3
        self.check_char(files, "₄", "4") # subscript 4
        self.check_char(files, "₅", "5") # subscript 5
        self.check_char(files, "₆", "6") # subscript 6
        self.check_char(files, "₇", "7") # subscript 7
        self.check_char(files, "¹", "1") # superscript 1
        self.check_char(files, "²", "2") # superscript 2
        self.check_char(files, "³", "3") # superscript 3

        # Remove non-breakable spaces between numbers. For instance, a
        # text file could have 250000, and the html could have 250 000.
        if self.args.suppress_nbsp_num:
            func = lambda text: re.sub(r"(\d)\u00A0(\d)", r"\1\2", text)
            files[0].transform_func.append(func)
            files[1].transform_func.append(func)

        # Suppress shy (soft hyphen)
        func = lambda text: re.sub(r"\u00AD", r"", text)
        files[0].transform_func.append(func)
        files[1].transform_func.append(func)

        # If the original encoding of them is latin1, we must convert a
        # few UTF8 characters. We assume the default is utf-8. No
        # provision for any other format.
        if files[0].myfile.encoding == "iso-8859-1" or files[1].myfile.encoding == "iso-8859-1":
            for f in files:
                if f.myfile.encoding != "iso-8859-1":
                    f.convert_to_latin1 = True

        err_message = ""

        # Apply the various convertions
        for f in files:
            err_message += f.convert() or ""

        # Extract footnotes
        if self.args.extract_footnotes:
            for f in files:
                f.extract_footnotes()

        # Transform the final document into a diffable format
        for f in files:
            f.transform()

        # Compare the two versions
        main_diff = self.compare_texts(files[0].text, files[1].text)

        if self.args.extract_footnotes:
            fnotes_diff = self.compare_texts(files[0].footnotes, files[1].footnotes)
        else:
            fnotes_diff = ""

        html_content = self.create_html(files, main_diff, fnotes_diff)

        return err_message, html_content, files[0].myfile.basename, files[1].myfile.basename


    def simple_html(self):
        """For debugging purposes. Transform the html and print the
        text output."""

        fname = self.args.filename[0]

        if fname.lower().endswith(('.html', '.htm')):
            f = pgdp_file_html(self.args)
        else:
            print("Error: not an html file")

        f.load(fname)
        if f.myfile is None:
            print("Couldn't load file:", fname)
            return

        f.process_args(self.args)
        f.analyze()

        # Remove non-breakable spaces between numbers. For instance, a
        # text file could have 250000, and the html could have 250 000.
        if self.args.suppress_nbsp_num:
            func = lambda text: re.sub(r"(\d)\u00A0(\d)", r"\1\2", text)
            f.transform_func.append(func)

        # Suppress shy (soft hyphen)
        func = lambda text: re.sub(r"\u00AD", r"", text)
        f.transform_func.append(func)

        # Apply the various convertions
        f.convert()

        # Extract footnotes
        if self.args.extract_footnotes:
            f.extract_footnotes()

        # Transform the final document into a diffable format
        f.transform()

        print(f.text)


######################################

# Sample CSS used to display the diffs.
def diff_css():
    return """
body {
    margin-left: 5%;
    margin-right: 5%;
}

del {
    text-decoration: none;
    border: 1px solid black;
    color: #700000 ;
    background-color: #f4f4f4;
    font-size: larger;
}
ins {
    text-decoration: none;
    border: 1px solid black;
    color: green;
    font-weight: bold;
    background-color: #f4f4f4;
    font-size: larger;
}
.lineno { margin-right: 3em; }
.sep4 { margin-top: 4em; }
.bbox { margin-left: auto;
    margin-right: auto;
    border: 1px dashed;
    padding: 0em 1em 0em 1em;
    background-color: #F0FFFF;
    width: 90%;
    max-width: 50em;
}
.center { text-align:center; }

/* Use a CSS counter to number each diff. */
body {
  counter-reset: diff;  /* set diff counter to 0 */
}
hr:before {
  counter-increment: diff; /* inc the diff counter ... */
  content: "Diff " counter(diff) ": "; /* ... and display it */
}

.error-border { border-style:double; border-color:red; border-width:15px; }
"""

# Describe how to use the diffs
def html_usage(filename1, filename2):
    return """
    <div class="bbox">
      <p class="center">— Note —</p>
      <p>
        The first number is the line number in the first file (""" + filename1 + """)<br />
        The second number is the line number in the second file (""" + filename2 + """)<br />
        Line numbers can sometimes be very approximate.
      </p>
      <p>
        Deleted words that were in the first file but not in the second will appear <del>like this</del>.<br />
        Inserted words that were in the second file but not in the first will appear <ins>like this</ins>.
      </p>
    </div>
    """


def output_html(args, html_content, filename1, filename2):
    # Outputs a complete HTML file
    print("""
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <title>
      Compare """ + filename1 + " and " + filename2 + """
    </title>
    <style type="text/css">
""")

    print(diff_css())

    print("""
    </style>
  </head>
<body>
""")

    print("<h1>" + filename1 + " and " + filename2 + "</h1>")
    print(html_usage(filename1, filename2))
    # print('<p>Custom CSS added on command line: ' + " ".join(args.css) + '</p>')

    print(html_content)
    print("""
  </body>
</html>
""")

def main():

    parser = argparse.ArgumentParser(description='Diff text document for PGDP PP.')

    parser.add_argument('filename', metavar='FILENAME', type=str,
                        help='input text file', nargs=2)
    parser.add_argument('--ignore-format', action='store_true', default=False,
                        help='Silence formating differences')
    parser.add_argument('--suppress-footnote-tags', action='store_true', default=False,
                        help='Suppress "[Footnote ?:" marks')
    parser.add_argument('--suppress-illustration-tags', action='store_true', default=False,
                        help='Suppress "[Illustration:" marks')
    parser.add_argument('--suppress-sidenote-tags', action='store_true', default=False,
                        help='Suppress "[Sidenote:" marks')
    parser.add_argument('--ignore-case', action='store_true', default=False,
                        help='Ignore case when comparing')
    parser.add_argument('--extract-footnotes', action='store_true', default=False,
                        help='Extract and process footnotes separately')
    parser.add_argument('--ignore-0-space', action='store_true', default=False,
                        help='HTML: suppress zero width space (U+200b)')
    parser.add_argument('--suppress-nbsp-num', action='store_true', default=False,
                        help="Suppress non-breakable spaces between numbers")
    parser.add_argument('--css-smcap', type=str, default=None,
                        help="HTML: Transform small caps into uppercase (U), lowercase (L) or title (T)")
    parser.add_argument('--css-bold', type=str, default=None,
                        help="HTML: Surround bold strings with this string")
    parser.add_argument('--css', type=str, default=[], action='append',
                        help="HTML: Insert transformation CSS")
    parser.add_argument('--suppress-proofers-notes', action='store_true', default=False,
                        help="In Px/Fx versions, remove [**proofreaders notes]")
    parser.add_argument('--regroup-split-words', action='store_true', default=False,
                        help="In Px/Fx versions, regroup split wo-* *rds")
    parser.add_argument('--css-greek-title-plus', action='store_true', default=False,
                        help="HTML: use greek transliteration in title attribute")
    parser.add_argument('--css-add-illustration', action='store_true', default=False,
                        help="HTML: add [Illustration ] tag")
    parser.add_argument('--css-add-sidenote', action='store_true', default=False,
                        help="HTML: add [Sidenote: ...]")
    parser.add_argument('--css-no-default', action='store_true', default=False,
                        help="HTML: do not use default transformation CSS")
    parser.add_argument('--without-html-header', action='store_true', default=False,
                        help="HTML: do not output html header and footer")
    parser.add_argument('--txt-cleanup-type', type=str, default='b',
                        help="TXT: Type of text cleaning -- (b)est effort, (n)one, (p)roofers")
    parser.add_argument('--simple-html', action='store_true', default=False,
                        help="HTML: Process the html file and print the output (debug)")

    args = parser.parse_args()

    x = CompPP(args)
    if args.simple_html:
        x.simple_html()
    else:
        _, html_content, fn1, fn2 = x.do_process()

        output_html(args, html_content, fn1, fn2)

if __name__ == '__main__':
    main()
