#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
  ppsmq.py
  MIT license (c) 2018 Asylum Computer Services LLC
"""

import argparse
import re
import sys
import string
import os
import codecs

class Ppsmq(object):

    def __init__(self, args):
        self.srcfile = args['infile']
        self.outfile = args['outfile']
        self.debug = args['debug']
        self.wb = []
        self.encoding = ""
        self.FLAG = "@"
        self.flag_count = 0
        self.VERSION = "2018.03.22"
        self.root = os.path.dirname(os.path.realpath(__file__))

    # display (fatal) error and exit
    def fatal(self, message):
        sys.stderr.write("fatal: " + message + "\n")
        exit(1)

    # load file from specified source file
    def loadFile(self, fn):
        try:
            wbuf = open(fn, "r", encoding='UTF-8').read()
            self.encoding = "UTF-8"
            # print("loaded UTF-8 file")##
            self.wb = wbuf.split("\n")
        except UnicodeDecodeError:
            wbuf = open(fn, "r", encoding='Latin-1').read()
            self.encoding = "Latin-1"
            # print("loaded Latin-1 file")##
            self.wb = wbuf.split("\n")
        except Exception as e:
            print(e)
            self.fatal("loadFile: cannot open source file {}".format(fn))
        self.wb = [s.rstrip() for s in self.wb]

    # save working buffer to specified dstfile
    def saveFile(self, fn):
        empty = re.compile("^$")
        while empty.match(self.wb[-1]):
            del self.wb[-1]
        f1 = open(fn, "w", encoding="UTF-8")
        f1.write('\ufeff')  # BOM if UTF-8
        for index, t in enumerate(self.wb):
            f1.write("{:s}\r\n".format(t))
        f1.close()

    def protectTags(self):
        for i, _ in enumerate(self.wb):
            while re.search("<[^>]*?'.*?>", self.wb[i]):
                self.wb[i] = re.sub("(<[^>]*?)'(.*?>)", r"\1∮\2", self.wb[i])
            while re.search("<[^>]*?\".*?>", self.wb[i]):
                self.wb[i] = re.sub("(<[^>]*?)\"(.*?>)", r"\1∯\2", self.wb[i])

    def doubleQuotes(self):
        empty = re.compile("^$")
        dqlevel = 0
        for i, line in enumerate(self.wb):

            # expect dqlevel == 0 on an empty line unless next line starts with open quote
            if empty.match(line):
                if dqlevel != 0 and ((i + 1) < len(self.wb)) and not self.wb[i+1].startswith('"'):
                    self.wb[i-1] += self.FLAG
                    self.flag_count += 1
                dqlevel = 0
                continue

            # replace straight with curly quotes
            while re.search('"', self.wb[i]):
                if dqlevel == 0:
                    self.wb[i] = re.sub('"', '“', self.wb[i], 1)
                    dqlevel += 1
                    continue
                if dqlevel == 1:
                    self.wb[i] = re.sub('"', '”', self.wb[i], 1)
                    dqlevel -= 1
                    continue

    def checkDouble(self):
        for i, line in enumerate(self.wb):
            if re.match("^”", self.wb[i]):  # wrong quote start of line
                self.wb[i] += self.FLAG
                self.flag_count += 1
            if re.search("“$", self.wb[i]):  # wrong quote end of line
                self.wb[i] += self.FLAG
                self.flag_count += 1
            # close quote followed by word char
            if re.search("”/w", self.wb[i]):
                self.wb[i] += self.FLAG
                self.flag_count += 1
            # open quote preceeded by word char
            if re.search("/w“", self.wb[i]):
                self.wb[i] += self.FLAG
                self.flag_count += 1
            if re.search("“ ", self.wb[i]):  # floating open quote
                self.wb[i] += self.FLAG
                self.flag_count += 1
            if re.search(" ”", self.wb[i]):  # floating close quote
                self.wb[i] += self.FLAG
                self.flag_count += 1

    def singleQuotesByList(self):
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
                t1 = "{}\\b".format(w[0])
                t2 = re.sub("'", "’", w[0])
                self.wb[i] = re.sub(t1, t2, self.wb[i])
                t1 = t1.lower()
                t2 = t2.lower()
                self.wb[i] = re.sub(t1, t2, self.wb[i])

    def singleQuotesByRule(self):
        for i, line in enumerate(self.wb):
            # letter-'-letter
            self.wb[i] = re.sub(r"(\w)'(\w)", r"\1’\2", self.wb[i])
            # period, comma or letter followed by '
            self.wb[i] = re.sub(r"([\.,\w])'", r"\1’", self.wb[i])
            # letter-apostrophe-period
            self.wb[i] = re.sub(r"(\w)'\.", r"\1’\.", self.wb[i])
            # at end of line
            self.wb[i] = re.sub(r"'$", r"’", self.wb[i])
            # followed by a space
            self.wb[i] = re.sub(r"' ", r"’ ", self.wb[i])
            # followed by a double quote
            self.wb[i] = re.sub(r"'”", r"’”", self.wb[i])

    def run(self):
        self.loadFile(self.srcfile)
        self.protectTags()
        self.doubleQuotes()
        self.checkDouble()
        self.singleQuotesByList()
        self.singleQuotesByRule()
        self.saveFile(self.outfile)
        if self.flag_count > 0:
            print(
                "{} unbalanced double-quotes marked with the '@' character".format(self.flag_count))

    def __str__(self):
        return "ppsmq"

def parse_args():
    parser = argparse.ArgumentParser()
    parser.add_argument('-i', '--infile', help='input file', required=True)
    parser.add_argument('-o', '--outfile', help='output file', required=True)
    parser.add_argument(
        '-d', '--debug', help='debug (developer only)', action='store_true')
    args = vars(parser.parse_args())
    return args

def main():
    args = parse_args()
    ppsmq = Ppsmq(args)
    ppsmq.run()

if __name__ == "__main__":
    sys.exit(main())
