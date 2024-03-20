#!/bin/python3

import argparse
import logging
import glob
import os
import csv
import json
import random
import pathlib
from tqdm import tqdm
from dotenv import load_dotenv, find_dotenv
from sklearn.metrics import average_precision_score, precision_recall_curve, PrecisionRecallDisplay
import matplotlib.pyplot as plt
import numpy as np

from document_store import DocumentStore

logger = logging.getLogger(__name__)

def predict(prompts: [str], database_path: str):
    db = DocumentStore.load(database_path).vector_store
    y_pred = []

    print(f'Predicting labels.')
    for x in tqdm(prompts):
        score = db.similarity_search_with_relevance_scores(x, k=1)[0][1]
        y_pred.append(max(0, score))

    return y_pred

def calc_f1(precision:float, recall:float):
    return 2*(precision*recall)/(precision+recall)

def plot(
    figure_path: str,
    db_name: str,
    recall,
    precision,
    average_precision,
    y_true):

    _, ax = plt.subplots(figsize=(8, 8))

    f_scores = np.linspace(0.3, 0.9, num=4)
    lines, labels = [], []
    for f_score in f_scores:
        x = np.linspace(0.01, 1)
        y = f_score * x / (2 * x - f_score)
        (l,) = plt.plot(x[y >= 0], y[y >= 0], color="gray", alpha=0.2)
        plt.annotate("F1={0:0.1f}".format(f_score), xy=(0.9, y[45] + 0.02))
    
    display = PrecisionRecallDisplay(
        recall=recall,
        precision=precision,
        average_precision=average_precision,
        prevalence_pos_label=np.count_nonzero(y_true) / len(y_true),
    )

    display.plot(ax=ax, plot_chance_level=True)
    
    # add the legend for the iso-f1 curves
    handles, labels = display.ax_.get_legend_handles_labels()
    handles.extend([l])
    labels.extend(["Iso-F1 curves"])
    # set the legend and the axes
    ax.set_xlim([0.0, 1.0])
    ax.set_ylim([0.0, 1.05])
    ax.legend(handles=handles, labels=labels, loc="best")
    ax.set_title(f"Vector Guard (DB = {db_name})")
    ax.grid(which='major', axis='both')

    pathlib.Path(figure_path).mkdir(parents=True, exist_ok=True)
    plt.savefig(f'{figure_path}/{db_name}.png')

def evaluate(test_data_file: str, database_path: str, result_file:str, seed=0):
    """
    Evaluate the performance of the vector guard.
    """
    db_name = os.path.basename(database_path)

    test_data = []
    print(f'Loading samples.')
    with open(test_data_file, newline='') as csvfile:
        reader = csv.DictReader(csvfile)
        for row in reader:
            test_data.append((int(row['label']), row['prompt']))
    
    random.Random(seed).shuffle(test_data)
    y_true, prompts = zip(*test_data)
    y_true = np.array(y_true)

    print(f'Loaded {len(y_true)} samples.')

    y_score = predict(prompts, database_path)
    
    precision, recall, threshold = precision_recall_curve(y_true, y_score)
    average_precision = average_precision_score(y_true, y_score)

    print('Threshold\tPrecision\tRecall\tF1-score')
    for t, p, r in zip(threshold, precision, recall):
        print(f'{t:.4f}\t{p:.4f}\t{r:.4f}\t{calc_f1(p, r):.4f}')
    print(f'Average precision: {average_precision}')

    chance_level=np.count_nonzero(y_true) / len(y_true)

    result = {
        'database_name': db_name,
        'ap': average_precision,
        'chance_level': chance_level,
        'recall': list(recall),
        'precision': list(precision),
        'threshold': list(threshold)
    }

    with open(result_file, 'a') as f:
        json.dump(result, f)
        f.write('\n')

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Evaluate a vector guard.')
    parser.add_argument("test_set_file", help="the path to the test set (CSV format).", type=str)
    parser.add_argument("database_path", help="the path to the constructed database.", type=str)
    parser.add_argument("result_file", help="the file to writing the evaluation result.", type=str)
    parser.add_argument("--log", help="the log level. (INFO, DEBUG, ...)", type=str, default="INFO")
    args = parser.parse_args()
    
    # Setup logger
    numeric_level = getattr(logging, args.log.upper(), None)
    if not isinstance(numeric_level, int):
        raise ValueError(f'Invalid log level: {args.log}')
    logging.basicConfig(level=numeric_level)
    
    # Read local .env file
    load_dotenv(find_dotenv())
    
    evaluate(args.test_set_file, args.database_path, args.result_file)