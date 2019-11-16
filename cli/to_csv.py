#!/usr/bin/env python
# -*-coding: utf8-*-
# don't rename this file to "csv.py"
# because this file importing standard csv lib
# and errors will occur if filename will same

'''
===============
html
===============
******************************
parser:bsoup3_parser.py	file:../page_google.html
123123
1.15545797348 s
real:1.18	user:1.17	sys:0.01	max RSS:56532

Becomes CSV like:
doctype,parser,file,max mem dif,parser s,real s,user s,sys s,maximum RSS
pypy,bsoup3_parser.py,page_google.html,122121,1.15545797348,1.18,1.17,0.01,56532
'''

import sys, argparse
import csv, re

argparser = argparse.ArgumentParser(
	description='Convert benchmark output to CSV')
argparser.add_argument(
	'--skip-header', dest='skip_header', action='store_true',
	help="Don't output column names (useful for concatenation)")
argparser.add_argument(
	'--skip-errors', dest='skip_errors', action='store_true',
	help="Silently drop benchmarks, that we can't parse (eg, with tracebacks)")

regexp1 = re.compile(r"^parser:([0-9a-zA-Z_.-]+)\tfile:(.*)")
def parse_line1(line):
	match = regexp1.search(line)
	parser = match.group(1).split("/")[-1]
	filepath = match.group(2).split("/")[-1]
	return parser, filepath

regexp21 = re.compile(r"^([0-9]+)")
def parse_line21(line):
	match = regexp21.search(line)
	return match.group(1)

regexp22 = re.compile(r"^([0-9.]+)( s)?")
def parse_line22(line):
	match = regexp22.search(line)
	return match.group(1)

regexp3 = re.compile(r"^real:([0-9.]+)\tuser:([0-9.]+)\tsys:([0-9.]+)\tmax RSS:([0-9]+)")
def parse_line3(line):
	match = regexp3.search(line)
	return match.group(1), match.group(2), match.group(3), match.group(4)

columns = ["doctype", "parser", "file", "max mem dif", "parser s", "real s", "user s", "sys s", "maximum RSS"]
TEST_DOCTYPE, TEST_END_DOCTYPE, TEST_HDR, TEST_0, TEST_1, TEST_2, TEST_3 = range(7)

class Parser(object):

	def __init__(self, skip_errors=False):
		self.skip_errors = skip_errors
		self.state = None
		self.r = {}

	def reset(self):
		self.r = {"doctype": self.r["doctype"]}

	def parse_line(self, line):
		try:
			return self.do_parse_line(line)
		except:
			if self.skip_errors:
		 		self.reset()
		 		self.state = None
			else:
				raise

	def do_parse_line(self, line):
		c = self
		if c.state == TEST_DOCTYPE:
			c.r["doctype"] = line.strip()
			c.state = TEST_END_DOCTYPE
		elif c.state == TEST_END_DOCTYPE:
			c.state = None
		elif c.state == TEST_HDR:
			c.r["parser"], c.r["file"] = parse_line1(line)
			c.state = TEST_0
		elif c.state == TEST_0:
			c.r["max mem dif"] = parse_line21(line)
			c.state = TEST_1
		elif c.state == TEST_1:
			c.r["parser s"] = parse_line22(line)
			c.state = TEST_2
		elif c.state == TEST_2:
			(c.r["real s"], c.r["user s"], c.r["sys s"], c.r["maximum RSS"]) = parse_line3(line)
			r = self.r
			c.reset()
			c.state = TEST_3
			return r
		elif line.startswith("==="):
			c.state = TEST_DOCTYPE
		elif line.startswith("***"):
			c.state = TEST_HDR
		else:
			raise ValueError('Invalid input: "{0}"'.format(line))

def main(in_file=sys.stdin, out_file=sys.stdout):
	args = argparser.parse_args()
	csvwriter = csv.DictWriter(out_file, delimiter=';', fieldnames=columns)
	if not args.skip_header:
		csvwriter.writeheader()

	c = Parser(skip_errors=args.skip_errors)
	for line in in_file:
		row = c.parse_line(line)
		if row:
			csvwriter.writerow(row)

if __name__ == '__main__':
	main()
