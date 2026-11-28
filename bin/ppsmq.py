#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
ppsmq.py.

MIT license (c) 2018 Asylum Computer Services LLC.
"""

import argparse
import re
import sys


class Ppsmq:
    """Post-processing smart-quote processor."""

    def __init__(self, infile: str, outfile: str, debug: bool, wrap: bool) -> None:
        """Initialize class."""
        self.infile = infile
        self.outfile = outfile
        self.debug = debug
        self.wrap = wrap
        self.wb = []
        self.encoding = ""
        self.FLAG = "@"
        self.flag_count = 0

    def fatal(self, message: str) -> None:
        """Display (fatal) error and exit."""
        sys.stderr.write(f"fatal: {message}\n")
        sys.exit(1)

    def load(self, infile: str) -> None:
        """Load file from specified source file."""
        try:
            with open(infile, encoding="UTF-8") as f:
                self.encoding = "UTF-8"
                # print("loaded UTF-8 file")
                self.wb = f.read().split("\n")
        except UnicodeDecodeError:
            with open(infile, encoding="Latin-1") as f:
                self.encoding = "Latin-1"
                # print("loaded Latin-1 file")
                self.wb = f.read().split("\n")
        except OSError as e:
            print(e)
            self.fatal(f"load: cannot open source file {infile}")
        self.wb = [s.rstrip() for s in self.wb]

        if self.wrap:
            # if any tags span two or more lines, unwrap them and leave blank lines
            # so the line counter stays correct
            # for ppsmq, need to be able to restore the lines and indentation
            for i in range(len(self.wb)):
                try:
                    lbc = self.wb[i].count("<")
                    rbc = self.wb[i].count(">")
                    consume = 1
                    while lbc != rbc:
                        self.wb[i] = self.wb[i] + "⦻" + self.wb[i + consume]
                        self.wb[i + consume] = ""
                        lbc = self.wb[i].count("<")
                        rbc = self.wb[i].count(">")
                        consume += 1
                except IndexError:
                    pass

    def save(self, outfile: str) -> None:
        """Save working buffer to specified outfile."""
        while self.wb[-1] == "":
            del self.wb[-1]
        with open(outfile, "w", encoding="UTF-8") as f:
            f.write("\ufeff")  # BOM if UTF-8
            if self.wrap:
                for index, t in enumerate(self.wb):
                    if "⦻" in t:
                        t2 = t.split("⦻")
                        j = index
                        for line in t2:
                            self.wb[j] = line
                            j += 1
            f.write("".join(f"{t:s}\r\n" for t in self.wb))

    def protect_tags(self) -> None:
        """
        Turn quotes in HTML tag attributes into the special ∮ character.

        This avoids them being mangled by quote fixing transforms.
        """
        for i in range(len(self.wb)):
            while re.search("<[^>]*?'.*?>", self.wb[i]):
                self.wb[i] = re.sub("(<[^>]*?)'(.*?>)", r"\1∮\2", self.wb[i])
            while re.search('<[^>]*?".*?>', self.wb[i]):
                self.wb[i] = re.sub('(<[^>]*?)"(.*?>)', r"\1∯\2", self.wb[i])

    def curl_double_quotes(self) -> None:
        """Turn straight double quotes into open/close curly quotes."""
        dqlevel = 0
        for i in range(len(self.wb)):
            # expect dqlevel == 0 on an empty line unless next line starts with open quote
            if self.wb[i] == "":
                if (
                    dqlevel != 0
                    and (i + 1) < len(self.wb)
                    and not self.wb[i + 1].startswith('"')
                ):
                    self.wb[i - 1] += self.FLAG
                    self.flag_count += 1
                dqlevel = 0
                continue

            # replace straight with curly quotes
            while re.search('"', self.wb[i]):
                if dqlevel == 0:
                    self.wb[i] = re.sub('"', "“", self.wb[i], 1)
                    dqlevel += 1
                    continue
                if dqlevel == 1:
                    self.wb[i] = re.sub('"', "”", self.wb[i], 1)
                    dqlevel -= 1
                    continue

    def flag_mismatched_curly_double(self) -> None:
        """Flag seemingly mismatched curly double quotes for review."""
        rules = [
            "^”",  # close quote at start of line
            "“$",  # open quote at end of line
            r"”\w",  # close quote followed by word char
            r"\w“",  # open quote preceeded by word char
            "“ ",  # floating open quote
            " ”",  # floating close quote
        ]
        for i in range(len(self.wb)):
            for rule in rules:
                if re.search(rule, self.wb[i]):
                    self.wb[i] += self.FLAG
                    self.flag_count += 1

    def curl_single_quotes_by_list(self) -> None:
        """Search and replace list of straight quote words with curly quotes."""
        for i in range(len(self.wb)):
            # case specific
            self.wb[i] = re.sub(r"'em\b", r"’em", self.wb[i])
            # case irrelevant
            words = [
                "'Tis",
                "'Tisn't",
                "'Tweren't",
                "'Twere",
                "'Twould",
                "'Twouldn't",
                "'Twas",
                "'Im",
                "'Twixt",
                "'Til",
                "'Scuse",
                "'Gainst ",
                "'Twon't",
            ]
            for word in words:
                search, replace = f"{word}\\b", re.sub("'", "’", word)
                self.wb[i] = re.sub(search, replace, self.wb[i])
                search, replace = search.lower(), replace.lower()
                self.wb[i] = re.sub(search, replace, self.wb[i])

    def curl_single_quotes_by_regex(self) -> None:
        """Replace straight single quotes as apostrophes with curly quote equivalent."""
        rules = [
            (r"(\w)'(\w)", r"\1’\2"),  # letter-'-letter
            (r"([\.,\w])'", r"\1’"),  # period, comma or letter followed by '
            (r"(\w)'\.", r"\1’\."),  # letter-apostrophe-period
            (r"'$", r"’"),  # at end of line
            (r"' ", r"’ "),  # followed by a space
            (r"'”", r"’”"),  # followed by a double quote
        ]
        for i in range(len(self.wb)):
            for search, replace in rules:
                self.wb[i] = re.sub(search, replace, self.wb[i])

    def run(self) -> None:
        """Load, process, and save the text."""
        self.load(self.infile)
        self.protect_tags()
        self.curl_double_quotes()
        self.flag_mismatched_curly_double()
        self.curl_single_quotes_by_list()
        self.curl_single_quotes_by_regex()
        self.save(self.outfile)
        if self.flag_count > 0:
            print(
                f"{self.flag_count} unbalanced double-quotes marked with the '@' character"
            )


def main() -> None:
    """Entry point when run from CLI."""
    parser = argparse.ArgumentParser()
    parser.add_argument("-i", "--infile", help="input file", required=True)
    parser.add_argument("-o", "--outfile", help="output file", required=True)
    parser.add_argument(
        "-d", "--debug", help="debug (developer only)", action="store_true"
    )
    parser.add_argument(
        "-w", "--wrap", help="join tags spanning multiple lines", action="store_true"
    )
    args = parser.parse_args()
    Ppsmq(args.infile, args.outfile, args.debug, args.wrap).run()


if __name__ == "__main__":
    sys.exit(main())
