import os
import re
import sys
import asyncio
import logging
import shlex

from kuwa.executor import LLMExecutor, Modelfile

sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from src.subprocess_helper import SubProcessHelper, StreamName

logger = logging.getLogger(__name__)

class PipeExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        """
        Add custom command-line arguments.
        """
        parser.add_argument('--path', default="../../tools", help='The path to find executables.')
        parser.add_argument('--program', default="cat", help='The program to run.')
        parser.add_argument('--argv', default="", help='Arguments.')
        parser.add_argument('--encoding', default="utf-8", help='The encoding of the standard I/O streams. Set to None indicating I/O raw bytes.')
        parser.add_argument('--hide_stderr', action='store_true', help='Hide the stderr content in the executor response.')
        parser.add_argument('--extract_last_codeblock', action='store_true', help='Make sure the program only gets the code from inside the last code block.')

    def setup(self):
        self.sub_process = None

    def extract_code_from_markdown(self, markdown_text):
        """
        Extracts code from markdown code blocks using the provided regular expression.

        Args:
            markdown_text: The markdown text to extract code from.

        Returns:
            A list of dictionaries, where each dictionary represents a code block
            and contains the following keys:
            - 'language': The language of the code block (if specified), otherwise None.
            - 'code': The code within the code block.
        """

        regex = r"```(?P<language>[^`\r\n]*)[\r\n]+(?P<code>.+?)```"
        matches = re.findall(regex, markdown_text, re.DOTALL)

        code_blocks = []
        for match in matches:
            code_block = {
                'language': match[0].strip() if match[1].strip() else None,
                'code': match[1].strip()
            }
            code_blocks.append(code_block)

        return code_blocks

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        # Read configurations from modelfile.
        pipe_config = modelfile.parameters["pipe_"]
        program = pipe_config.get("program", self.args.program)
        argv = pipe_config.get("argv", self.args.argv)
        encoding = pipe_config.get("encoding", self.args.encoding)
        encoding = None if encoding is None or encoding.lower() == 'none' else encoding
        hide_stderr = pipe_config.get("hide_stderr", self.args.hide_stderr)
        extract_last_codeblock = pipe_config.get("extract_last_codeblock", self.args.extract_last_codeblock)

        # Initialize the context and helper of the subprocess
        last_user_prompt = history[-1]['content']
        sub_proc_input = last_user_prompt
        if extract_last_codeblock:
            codeblocks = self.extract_code_from_markdown(last_user_prompt)
            logger.debug(codeblocks)
            sub_proc_input = codeblocks[-1]['code']+'\n' if len(codeblocks) > 0 else last_user_prompt
        argv = shlex.split(argv)
        output_queue = asyncio.Queue()
        helper = SubProcessHelper(encoding=encoding)
        
        # Check whether the program is under the specified path 
        path = os.path.abspath(self.args.path)
        program = os.path.abspath(f"{self.args.path}/{program}")
        logger.info(f"Program: {program}")
        if not program.startswith(path):
            yield "Access outside the root directory is forbidden."
            return

        # Run a subprocess with stdin from the request.
        cmd = [os.path.realpath(program)]+argv
        logger.info(f"Cmd: {cmd}")
        self.sub_process = await helper.create_subprocess(cmd, sub_proc_input)
        logger.debug(f"Created sub-process with PID: {self.sub_process.pid}")
        logger.debug(f"Wrote {sub_proc_input} to stdin.")

        # Read the stdout and stderr stream from the queue.
        producer = asyncio.create_task(helper.stream_subprocess(self.sub_process, output_queue), name='producer')
        consumer = None
        while not producer.done() or not output_queue.empty():
            consumer = asyncio.create_task(output_queue.get(), name='consumer') if consumer is None else consumer
            # Run the producer and consumer concurrently. Block until any coroutine is done.
            await asyncio.wait([producer, consumer], return_when=asyncio.FIRST_COMPLETED)
            if not consumer.done(): continue

            stream_name, chunk = consumer.result()
            consumer = None
            logger.debug(f"Received chunk: ({stream_name}, {chunk})")
            if chunk is None: continue
            match stream_name:
                case StreamName.STDOUT: yield chunk
                case StreamName.STDERR:
                    logger.info(f"STDERR ({self.sub_process.pid}): {chunk}")
                    if not hide_stderr: yield chunk
                case _: pass
        
        await SubProcessHelper.terminate_subprocess(self.sub_process)
        logger.debug(f"Sub-process {self.sub_process.pid} exited with return code {self.sub_process.returncode}")
        self.sub_process = None

    async def abort(self):
        if self.sub_process is None: return "No job to abort."
        await SubProcessHelper.terminate_subprocess(self.sub_process)
        self.sub_process = None
        logger.debug("aborted")
        return "Aborted"

if __name__ == "__main__":
    executor = PipeExecutor()
    executor.run()