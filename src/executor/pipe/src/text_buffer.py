import logging

logger = logging.getLogger(__name__)

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