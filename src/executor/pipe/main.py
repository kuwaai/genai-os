import os
import sys
import asyncio
import logging
import json
import subprocess
from enum import Enum
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from kuwa.executor import LLMExecutor, Modelfile

logger = logging.getLogger(__name__)

class StreamName(Enum):
    STDIN = 0
    STDOUT = 1
    STDERR = 2

class TextBuffer:
    """
    Decode text from byte stream.
    """
    def __init__(self, coding:str|None = None):
        self.coding = coding
        self.buffer = b''
    
    def get_chunk(self, raw_chunk:bytes, eof:bool) -> str|None:
        """
        Continuously append incoming raw data chunks to a buffer.
        Decode complete chunks from this buffer whenever possible.
        If the end-of-file flag is set, decode the entire remaining buffer content.
        Arguments:
          - raw_chunk (bytes): The raw data chunk from the stream.
          - eof (bool): The end-of-file flag.
        """
        if self.coding is None or raw_chunk is None:
            return raw_chunk

        logger.debug(f"Got new bytes: {raw_chunk}")
        self.buffer += raw_chunk
        buffer_len = len(self.buffer)
        chunk = None
        for end in range(buffer_len, 0, -1):
            try:
                chunk = self.buffer[:end].decode(
                    encoding=self.coding,
                    errors='strict' if not eof else 'ignore'
                )
                self.buffer = self.buffer[end:]
                break
            except UnicodeError:
                continue
        logger.debug(f"Decoded chunk: {chunk}")
        return chunk

class SubProcessHelper:
    """
    Helper functions to manipulate asyncio.subprocess
    """
    
    def __init__(self, encoding:str|None="utf-8", max_chunk_bytes = 4096):
        self.encoding = encoding
        self.max_chunk_bytes = max_chunk_bytes

    async def _read_stream(self, stream, name:StreamName, queue):
        """
        Read the stream into queue.
        """
        
        buffer = TextBuffer(self.encoding)
        while True:
            raw_chunk = await stream.read(self.max_chunk_bytes)
            eof = stream.at_eof()
            chunk = buffer.get_chunk(raw_chunk, eof)
            await queue.put((name, chunk))
            if eof:
                await queue.put((name, None)) # None indicates end-of-stream
                break

    async def create_subprocess(self, cmd, input_data:str|None = None):
        """
        Create a subprocess with optional input data.
        """

        process = await asyncio.create_subprocess_exec(
            *cmd,
            stdin=asyncio.subprocess.PIPE,  # Providing input from a stream
            stdout=asyncio.subprocess.PIPE, # Capturing stdout
            stderr=asyncio.subprocess.PIPE  # Capturing stderr
        )
        if input_data != None:
            process.stdin.write(input_data.encode(encoding=self.encoding))
            process.stdin.close()
        return process

    async def stream_subprocess(self, process, queue):
        """
        Read both of STDOUT and STDERR stream into queue.
        Item:
          - For STDOUT: (StreamName.STDOUT, decode chunk)
          - For STDERR: (StreamName.STDERR, decode chunk)
        """
        await asyncio.gather(
            self._read_stream(process.stdout, StreamName.STDOUT, queue),
            self._read_stream(process.stderr, StreamName.STDERR, queue)
        )
        return None
    
    @staticmethod
    async def terminate_subprocess(process):
        if process.returncode is None:
            process.terminate()
            await process.wait()


class PipeExecutor(LLMExecutor):
    def __init__(self):
        super().__init__()

    def extend_arguments(self, parser):
        """
        Add custom command-line arguments.
        """
        parser.add_argument('--path', default="../../tools", help='The path to find executables.')
        parser.add_argument('--program', default="cat", help='The program to run.')
        parser.add_argument('--encoding', default="utf-8", help='The encoding of the standard I/O streams. Set to None indicating I/O raw bytes.')
        parser.add_argument('--display_stderr', action='store_true', help='Show the stderr content in the executor response.')

    def setup(self):
        self.sub_process = None

    async def llm_compute(self, history: list[dict], modelfile:Modelfile):
        pipe_config = modelfile.parameters["pipe_"]
        program = pipe_config.get("program", self.args.program)
        encoding = pipe_config.get("encoding", self.args.encoding)
        encoding = None if encoding is None or encoding.lower() == 'none' else encoding
        display_stderr = pipe_config.get("display_stderr", self.args.display_stderr)
        sub_proc_input = history[-1]['content']
        output_queue = asyncio.Queue()
        helper = SubProcessHelper(encoding=encoding)
        path = os.path.realpath(self.args.path)
        program = os.path.realpath(f"{self.args.path}/{program}")

        if not program.startswith(path):
            yield "Access outside the root directory is forbidden."
            return
        logger.debug(f"{path} {program}")

        # Run a subprocess with stdin from the request.
        self.sub_process = await helper.create_subprocess([program], sub_proc_input)
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
                    if display_stderr: yield chunk
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