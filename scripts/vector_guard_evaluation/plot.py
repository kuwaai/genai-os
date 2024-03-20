#!/bin/python3

import argparse
import logging
import os
import csv
import json
import pathlib
from dotenv import load_dotenv, find_dotenv
from sklearn.metrics import PrecisionRecallDisplay
import matplotlib.pyplot as plt
import numpy as np

from document_store import DocumentStore

logger = logging.getLogger(__name__)

def load_jsonl(file: str):
    # Load the result
    data = []
    with open(file, 'r') as f:
        data = [json.loads(l) for l in f]
    return data

def plot(result_file: str, figure_path: str):

    results = load_jsonl(result_file)

    _, ax = plt.subplots(figsize=(8, 8))

    for i, result in enumerate(results):
        display = PrecisionRecallDisplay(
            recall=result['recall'],
            precision=result['precision'],
            average_precision=result['ap'],
            prevalence_pos_label=result['chance_level'],
        )

        display.plot(ax=ax, plot_chance_level=(i==len(results)-1), name=result['database_name'])

    f_scores = np.linspace(0.3, 0.9, num=4)
    lines, labels = [], []
    for f_score in f_scores:
        x = np.linspace(0.01, 1)
        y = f_score * x / (2 * x - f_score)
        (l,) = plt.plot(x[y >= 0], y[y >= 0], color="gray", alpha=0.2)
        plt.annotate("F1={0:0.1f}".format(f_score), xy=(0.9, y[45] + 0.02))
    
    # add the legend for the iso-f1 curves
    handles, labels = display.ax_.get_legend_handles_labels()
    handles.extend([l])
    labels.extend(["Iso-F1 curves"])
    # set the legend and the axes
    ax.set_xlim([0.0, 1.0])
    ax.set_ylim([0.0, 1.05])
    ax.legend(handles=handles, labels=labels, loc="best")
    ax.set_title(f"Vector Guard Performance with Different Data Source")
    ax.grid(which='major', axis='both')

    pathlib.Path(os.path.dirname(figure_path)).mkdir(parents=True, exist_ok=True)
    plt.savefig(figure_path)

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Plot the evaluation result.')
    parser.add_argument("result_file", help="the file to writing the evaluation result.", type=str)
    parser.add_argument("figure_path", help="the path to the figure output directory.", type=str)
    parser.add_argument("--log", help="the log level. (INFO, DEBUG, ...)", type=str, default="INFO")
    args = parser.parse_args()
    
    # Setup logger
    numeric_level = getattr(logging, args.log.upper(), None)
    if not isinstance(numeric_level, int):
        raise ValueError(f'Invalid log level: {args.log}')
    logging.basicConfig(level=numeric_level)
    
    # Read local .env file
    load_dotenv(find_dotenv())
    
    plot(args.result_file, args.figure_path)