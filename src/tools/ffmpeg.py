#!/usr/local/bin/python

import argparse
import logging
import subprocess
import requests
import sys
import os
import tempfile
import urllib
import fileinput
from pathlib import Path
from kuwa.client import FileOperations

logger = logging.getLogger(__name__)

def download_file(url):
    """Downloads a file from a given URL."""

    # Extract filename from URL
    filename = urllib.parse.urlparse(url).path.split('/')[-1]
    filename = urllib.parse.unquote(filename)
    filename, ext = os.path.splitext(filename)

    response = requests.get(url, stream=True)
    with tempfile.NamedTemporaryFile(delete=False, prefix=filename, suffix=ext) as f:
        if response.status_code != 200:
            raise Exception(f"Error downloading file. Status code: {response.status_code}")

        for chunk in response.iter_content(chunk_size=1024):
            f.write(chunk)
        
    return f.name, f'{filename}{ext}'

def process_media(input_file, ffmpeg_args):
    """Processes the media file using ffmpeg with the provided arguments."""

    # Output file will be the same as the input file but with a new extension
    input_filename, input_ext = os.path.splitext(input_file)
    output_filename = input_filename+'-'+'_'.join(ffmpeg_args).replace('-', '').replace(':', '_')
    output_file = f"{output_filename}{input_ext}"
    
    # Assuming you have ffmpeg installed and in your system PATH
    command = ["ffmpeg", *ffmpeg_args, "-y", "-i", input_file, output_file]
    logger.debug(command)
    subprocess.run(command, check=True)

    return output_file

def upload_to_web(file_path, api_url, api_token, original_filename=None):
    """Uploads the processed video to the specified API endpoint."""

    file_client = FileOperations(base_url=api_url, auth_token=api_token)

    if original_filename is not None:
        original_filepath = file_path
        file_path = (Path(file_path).parent / original_filename).absolute()
        os.rename(original_filepath, file_path)
    
    try:
        response = file_client.upload_file(file_path=file_path)
    except:
        logger.exception("Error occurs while uploading files.")
    
    if original_filename is not None:
        os.unlink(file_path)

    return response['result']

def parse_args():
    parser = argparse.ArgumentParser()
    parser.add_argument('--debug', action='store_true')
    args, unknown_args, parser.parse_known_args()

if __name__ == "__main__":
    sys.tracebacklimit = -1
    try:
        args, ffmpeg_args = parse_args() # Get ffmpeg arguments from command line
        sys.argv = sys.argv[:1]
        for video_url in fileinput.input():
            video_url = video_url.strip()

            # Download the video
            downloaded_file, original_filename = download_file(video_url)

            # Process the video with ffmpeg
            output_file = process_media(downloaded_file, ffmpeg_args)

            # Upload to the API
            result_url = upload_to_web(
                file_path=output_file,
                api_url=os.environ['KUWA_BASE_URL'],
                api_token=os.environ["KUWA_API_KEY"],
            )

            # Print the result URL
            print(result_url)

            # Cleanup
            os.unlink(downloaded_file)
            os.unlink(output_file)

    except Exception as e:
        print(f"{type(e).__name__}: {e.args[0]}")