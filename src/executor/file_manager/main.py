import re
import os
import sys
import asyncio
import logging
import json
import shlex
import requests
from textwrap import dedent
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile
from kuwa.executor.llm_executor import extract_last_url

logger = logging.getLogger(__name__)

class FileManagerExecutor(LLMExecutor):
    
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        """
        Override this method to add custom command-line arguments.
        """
        parser.add_argument("--root", default=os.path.expanduser("~"), help="The root path to store the file.", type=str)
        parser.add_argument('--api_base_url', default="http://127.0.0.1/", help='The API base URL of Kuwa multi-chat WebUI. This value will pass to the subprocess.')

    def setup(self):
        pass

    def download_file(self, url, save_path):
        """
        Downloads a file from a given URL to a specified path.

        Args:
            url (str): The URL of the file to download.
            save_path (str): The path to save the downloaded file.
        """

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


    def get_help(self):
        return dedent("""
        ## Kuwa File Manager: A Streamlined Approach to File Management within Kuwa GenAI OS

        **Kuwa File Manager** provides a initial way to upload, list, and move files within your Kuwa GenAI OS environment.

        ### Usage

        Interact with the File Manager using a straightforward command structure:
        [sub-command] [path]

        **Components:**

        **`sub-command` (Optional):** The action you want to perform. Choose from:
        - `put`: Upload a file (default if no sub-command is provided).
        - `ls`: List files in a directory.
        - `mv`: Move a file.
        - `help`: Display this help message.

        **`path` (Optional):**  The target location within your Kuwa GenAI OS file system. 
        - If uploading a file and no filename is specified in the path, the uploaded file's name will be used.
        - Key directories:
        - `/bin`: Store executable files for the pipe executor.
        - `/database`: Store RAG databases for the DB QA executor.
        - `/custom`:  Upload custom web UI components to override the default interface. 
            
        ### Sub-commands in Detail:

        - **put  &lt;path&gt;:** Upload the currently selected file to the specified path within Kuwa GenAI OS. 
        - **ls  &lt;path&gt;:** Display a list of all files and directories present at the specified path.
        - **mv  &lt;path&gt;  &lt;new_path&gt;:** Move a file or directory from the original  &lt;path&gt; to the  &lt;new_path&gt; location.
        """)

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        cmds = ["help", "put", "ls", "mv"]
        root_path = modelfile.parameters["uploader_"].get("root", self.args.root)
        url, history = extract_last_url(history)
        orig_cmd = history[-1]['content']
        orig_cmd = shlex.split(orig_cmd)
        if len(orig_cmd) == 0:
            orig_cmd = ["help"]
        if orig_cmd[0] not in cmds:
            orig_cmd = ["put"] + orig_cmd
        if orig_cmd[0] == "ls" and len(orig_cmd) == 1:
            orig_cmd = ["ls", "/"]
        cmd = orig_cmd.copy()

        get_real_path = lambda x: os.path.abspath(os.path.join(root_path, "./"+x.strip()))
        cmd = cmd[:1] + [get_real_path(i) for i in cmd[1:]]
        logger.debug(cmd)

        match cmd[0]:
            case "put" | "":
                if url is None:
                    yield f"Please upload a file from multi-chat."
                    return
                if len(cmd) < 2:
                    yield "Expected 1 argument. Got 0."
                    return
                try:
                    self.download_file(url, cmd[1])
                    yield f"File uploaded successfully to: {orig_cmd[1]}"
                except requests.exceptions.RequestException as e:
                    yield f"An error occurred while uploading the file: {e}"

            case "ls":
                if len(cmd) < 2:
                    yield "Expected 1 argument. Got 0."
                    return
                yield "\n".join(os.listdir(cmd[1]))

            case "mv":
                raise NotImplementedError()

            case _:
                yield self.get_help()

    async def abort(self):
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    executor = FileManagerExecutor()
    executor.run()