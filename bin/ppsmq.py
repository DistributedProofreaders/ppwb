#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
  ppsmq.py
  MIT license (c) 2018 Asylum Computer Services LLC
"""

import argparse
import re
import sys


class Ppsmq:
    def __init__(self, infile: str, outfile: str, debug: bool) -> None:
        self.infile = infile
        self.outfile = outfile
        self.debug = debug
        self.wb = []
        self.encoding = ""
        self.FLAG = "@"
        self.flag_count = 0

    def fatal(self, message: str) -> None:
        """display (fatal) error and exit"""
        sys.stderr.write(f"fatal: {message}\n")
        sys.exit(1)

    def loadFile(self, infile: str) -> None:
        """load file from specified source file"""
        try:
            with open(infile, "r", encoding='UTF-8') as f:
                self.encoding = "UTF-8"
                # print("loaded UTF-8 file")
                self.wb = f.read().split("\n")
        except UnicodeDecodeError:
            with open(infile, "r", encoding='Latin-1') as f:
                self.encoding = "Latin-1"
                # print("loaded Latin-1 file")
                self.wb = f.read().split("\n")
        except OSError as e:
            print(e)
            self.fatal(f"loadFile: cannot open source file {infile}")
        self.wb = [s.rstrip() for s in self.wb]

        WRAP = False
        if WRAP:
            # if any tags span two or more lines, unwrap them and leave blank lines
            # so the line counter stays correct
            # for ppsmq, need to be able to restore the lines and indentation
            i = 0
            while i < len(self.wb):
                try:
                    lbc = self.wb[i].count('<')
                    rbc = self.wb[i].count('>')
                    consume = 1
                    while lbc != rbc:
                        self.wb[i] = self.wb[i]+"⦻"+self.wb[i+consume]
                        self.wb[i+consume] = ""
                        lbc = self.wb[i].count('<')
                        rbc = self.wb[i].count('>')
                        consume += 1
                except IndexError:
                    pass
                i += 1

    def saveFile(self, outfile: str) -> None:
        """save working buffer to specified outfile"""
        while self.wb[-1] == "":
            del self.wb[-1]
        with open(outfile, "w", encoding="UTF-8") as f:
            f.write('\ufeff')  # BOM if UTF-8

            WRAP = False
            if WRAP:
                for index, t in enumerate(self.wb):
                    if '⦻' in t:
                        t2 = t.split('⦻')
                        j = index
                        for line in t2:
                            self.wb[j] = line
                            j += 1
            f.write("".join(f"{t:s}\r\n" for t in self.wb))

    def protectTags(self) -> None:
        for i, _ in enumerate(self.wb):
            while re.search("<[^>]*?'.*?>", self.wb[i]):
                self.wb[i] = re.sub("(<[^>]*?)'(.*?>)", r"\1∮\2", self.wb[i])
            while re.search("<[^>]*?\".*?>", self.wb[i]):
                self.wb[i] = re.sub("(<[^>]*?)\"(.*?>)", r"\1∯\2", self.wb[i])

    def doubleQuotes(self) -> None:
        dqlevel = 0
        for i, line in enumerate(self.wb):

            # expect dqlevel == 0 on an empty line unless next line starts with open quote
            if line == "":
                if dqlevel != 0 and ((i + 1) < len(self.wb)) and not self.wb[i+1].startswith('"'):
                    self.wb[i-1] += self.FLAG
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

    def checkDouble(self) -> None:
        rules = [
            "^”",  # close quote at start of line
            "“$",  # open quote at end of line
            r"”\w",  # close quote followed by word char
            r"\w“",  # open quote preceeded by word char
            "“ ",  # floating open quote
            " ”",  # floating close quote
        ]
        for i, line in enumerate(self.wb):
            for rule in rules:
                if re.search(rule, self.wb[i]):
                    self.wb[i] += self.FLAG
                    self.flag_count += 1

    def singleQuotesByList(self) -> None:
        for i, _ in enumerate(self.wb):
            # case specific
            self.wb[i] = re.sub(r"'em\b", r"’em", self.wb[i])
            # case irrelevant
            words = [("'Tis", "’Tis"), ("'Tisn't", "’Tisn’t"), ("'Tweren't", "’Tweren’t"), ("'Twere", "’Twere"),
                     ("'Twould", "’Twould"), ("'Twouldn't",
                                              "’Twouldn’t"), ("'Twas", "’Twas"), ("'Im", "’Im"),
                     ("'Twixt", "’Twixt"), ("'Til", "’Til"), ("'Scuse",
                                                              "’Scuse"), ("'Gainst ", "’Gainst "),
                     ("'Twon't", "’Twon’t")]
            for x, w in enumerate(words):
                t1 = f"{w[0]}\\b"
                t2 = re.sub("'", "’", w[0])
                self.wb[i] = re.sub(t1, t2, self.wb[i])
                t1 = t1.lower()
                t2 = t2.lower()
                self.wb[i] = re.sub(t1, t2, self.wb[i])

    def singleQuotesByRule(self) -> None:
        rules = [
            (r"(\w)'(\w)", r"\1’\2"),  # letter-'-letter
            (r"([\.,\w])'", r"\1’"),  # period, comma or letter followed by '
            (r"(\w)'\.", r"\1’\."),  # letter-apostrophe-period
            (r"'$", r"’"),  # at end of line
            (r"' ", r"’ "),  # followed by a space
            (r"'”", r"’”"),  # followed by a double quote
        ]
        for i, line in enumerate(self.wb):
            for search, replace in rules:
                self.wb[i] = re.sub(search, replace, self.wb[i])

    def run(self) -> None:
        self.loadFile(self.infile)
        self.protectTags()
        self.doubleQuotes()
        self.checkDouble()
        self.singleQuotesByList()
        self.singleQuotesByRule()
        self.saveFile(self.outfile)
        if self.flag_count > 0:
            print(
                f"{self.flag_count} unbalanced double-quotes marked with the '@' character"
            )


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("-i", "--infile", help="input file", required=True)
    parser.add_argument("-o", "--outfile", help="output file", required=True)
    parser.add_argument(
        "-d", "--debug", help="debug (developer only)", action="store_true"
    )
    args = parser.parse_args()
    Ppsmq(args.infile, args.outfile, args.debug).run()


if __name__ == "__main__":
    sys.exit(main())
