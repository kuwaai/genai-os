#!/usr/local/bin/python

import sys
import fileinput
import argparse
import opencc

def main(converter):
    for line in fileinput.input():
        line = line.strip()
        result = converter.convert(line)
        print(result)

if __name__ == "__main__":
    # sys.tracebacklimit = -1
    parser = argparse.ArgumentParser(description='Charter converter based-on OpenCC.')
    parser.add_argument("--config", help="the configuration of the converter", type=str, default="s2twp", choices=[
        's2t', 't2s', 's2tw', 'tw2s', 's2hk', 'hk2s', 's2twp', 'tw2sp', 't2tw', 'hk2t', 't2hk', 't2jp', 'jp2t', 'tw2t'
    ])
    args = parser.parse_args()
    sys.argv = []

    converter = opencc.OpenCC(f'{args.config}.json')
    main(converter)