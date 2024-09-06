#!/usr/local/bin/python

import subprocess
import requests
import sys
import os
import tempfile
import urllib
import fileinput
from pathlib import Path

def download_file(url):
    """Downloads a file from a given URL."""

    # Extract filename from URL
    filename = urllib.parse.urlparse(url).path.split('/')[-1]
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
    print(command, flush=True)
    subprocess.run(command, check=True)

    return output_file

def upload_to_web(file_path, api_url, original_filename=None):
    """Uploads the processed video to the specified API endpoint."""

    # with open(file_path, 'rb') as file:
    #     response = requests.post(api_url, files={'content': file, 'name': original_filename})

    # if response.status_code == 200:
    #     data = response.json()
    #     return data.get('url')  # Assuming the API returns the public URL in 'url' field
    # else:
    #     raise Exception(f"Error uploading file to API. Status code: {response.status_code}")
    
    # Mock output
    return Path(file_path).resolve().as_uri()

if __name__ == "__main__":
    sys.tracebacklimit = -1
    try:
        ffmpeg_args = sys.argv[1:]  # Get ffmpeg arguments from command line
        sys.argv = sys.argv[:1]
        for video_url in fileinput.input():
            video_url = video_url.strip()

            # Download the video
            downloaded_file, original_filename = download_file(video_url)

            # Process the video with ffmpeg
            output_file = process_media(downloaded_file, ffmpeg_args)

            # Upload to the API
            api_url = "YOUR_API_URL_HERE"
            result_url = upload_to_web(output_file, api_url, original_filename)

            # Print the result URL
            print(result_url)

            # Cleanup
            os.unlink(downloaded_file)
            os.unlink(output_file)

    except Exception as e:
        print(f"{type(e).__name__}: {e.args[0]}")