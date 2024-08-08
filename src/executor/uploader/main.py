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
from kuwa.client import KuwaClient

logger = logging.getLogger(__name__)

class UploaderExecutor(LLMExecutor):
    
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        """
        Override this method to add custom command-line arguments.
        """
        kuwa_root_path = os.environ.get("KUWA_ROOT", os.path.join(os.path.expanduser("~"), "kuwa"))
        parser.add_argument("--root", default=kuwa_root_path, help="The root path to store the file.", type=str)
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
        return file_name, complete_path

    def parse_botfile(self, botfile_content):
        """
        Parses a botfile's content (as a multi-line string) and extracts the bot name and base model.

        Args:
            botfile_content: The botfile content as a multi-line string.

        Returns:
            A tuple containing the bot name and base model as strings.
        """
        name = None
        base_model = None
        description = None

        for line in botfile_content.splitlines():
            match_name = re.match(r'^KUWABOT name [\'"]?([^\'"]+)[\'"]?$', line.strip())
            match_base = re.match(r'^KUWABOT base [\'"]?([^\'"]*)[\'"]?$', line.strip())
            match_description = re.match(r'^KUWABOT description [\'"]?([^\'"]*)[\'"]?$', line.strip())
            if match_name:
                name = match_name.group(1)
            if match_base:
                base_model = match_base.group(1)
            if match_description:
                description = match_description.group(1)

        return name, base_model, description

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        dst_dir = modelfile.parameters["uploader_"].get("dst_dir", "/database")
        bot_template = modelfile.parameters["uploader_"].get("bot_template")
        succeed_message = modelfile.parameters["uploader_"].get("succeed_message", "File uploaded successfully.")

        url, history = extract_last_url(history)
        dst_path = os.path.abspath(os.path.join(self.args.root, "./"+dst_dir))
        file_name, file_path = self.download_file(url, dst_path)
        yield f"{succeed_message}\n"

        if bot_template is None:
            logger.debug("No bot template specified. Skipped creating bot.")
            return
        
        botfile = bot_template.replace("{{file_name}}", file_name)\
                              .replace("{{file_path}}", file_path)
        botfile = bytes(botfile, "utf-8").decode("unicode_escape")
        bot_name, bot_base = self.parse_botfile(botfile)
        logger.debug(f"bot_name={bot_name}")
        logger.debug(f"bot_base={bot_base}")
        logger.debug(f"botfile={botfile}")
        if bot_name is None or bot_base is None:
            yield "Missed name or base in botfile. Failed to create bot.\n"
            return

        kuwa_api_token = modelfile.parameters["_"]["user_token"]
        client = KuwaClient(
            base_url=self.args.api_base_url,
            auth_token=kuwa_api_token
        )
        response = await client.create_bot(
            bot_name=bot_name,
            llm_access_code=bot_base,
            modelfile=botfile
        )
        yield f"Bot \"{bot_name}\" created successfully."

    async def abort(self):
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    executor = UploaderExecutor()
    executor.run()