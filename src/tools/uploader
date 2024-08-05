#!/bin/python

import os
import sys
import requests
import argparse


def download_file(url, save_path):
    """
    Downloads a file from a given URL to a specified path.

    Args:
        url (str): The URL of the file to download.
        save_path (str): The path to save the downloaded file.
    """

    try:
        # Send a GET request to the URL
        response = requests.get(url, stream=True)

        # Raise an exception if the request was unsuccessful
        response.raise_for_status()

        # Get the file name from the URL or use a default name
        file_name = url.split("/")[-1] or "uploaded_file"

        # Create the complete save path including the file name
        complete_path = os.path.join(save_path, file_name)

        # Open the file in write binary mode and write the content in chunks
        with open(complete_path, "wb") as file:
            for chunk in response.iter_content(chunk_size=8192):
                file.write(chunk)

        print(f"File uploaded successfully to: {complete_path}")

    except requests.exceptions.RequestException as e:
        print(f"An error occurred while uploading the file: {e}")

def main(root_path):
    inputs = sys.stdin.readlines()
    assert len(inputs) == 2
    url = inputs[0].strip()
    path = os.path.abspath(os.path.join(root_path, "./"+inputs[1].strip()))
    download_file(url, path)

if __name__ == "__main__": 
    parser = argparse.ArgumentParser(description='Fetch file from URL to specified path.')
    parser.add_argument("--root", default=os.path.expanduser("~"), help="the root path to store the file.", type=str)
    args = parser.parse_args()
    main(root_path = args.root)