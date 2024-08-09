import asyncio
import logging
import shlex
from enum import Enum
from .text_buffer import TextBuffer

logger = logging.getLogger(__name__)

class StreamName(Enum):
    STDIN = 0
    STDOUT = 1
    STDERR = 2

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

    async def create_subprocess(self, cmd, input_data:str|None = None, **kwargs):
        """
        Create a subprocess with optional input data.
        """

        cmd = shlex.join(cmd)

        process = await asyncio.create_subprocess_shell(
            cmd,
            stdin=asyncio.subprocess.PIPE,  # Providing input from a stream
            stdout=asyncio.subprocess.PIPE, # Capturing stdout
            stderr=asyncio.subprocess.PIPE,  # Capturing stderr
            **kwargs
        )
        if input_data != None and not process.stdin.is_closing():
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