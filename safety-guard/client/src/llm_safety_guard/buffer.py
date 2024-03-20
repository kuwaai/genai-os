import logging
from enum import Enum
from collections import deque

logger = logging.getLogger(__name__)

class PassageBuffer:
    """
    A buffer to store the streaming output before detection. The purpose is to
    reduce the overall detection cost. 
    """
    class State(Enum):
        normal = 'normal'
        finalized = 'finalized'

    stop_charters = ',，。!！?？\n'

    def __init__(self, n_max_buffer=100, streaming=True):
        """
        n_max_buffer: The maximum characters that the buffer can hold.
        streaming: If not streaming, the buffer will output the chunk only on
        finalized.
        """
        self.n_max_buffer = n_max_buffer
        self.streaming = streaming
        self.state = PassageBuffer.State.normal
        self.q = ''
    
    def append(self, text:str, last:bool = False):
        """
        Append "text" to the queue.
        If "last" equals true, indicating there's no more data.
        Then the next get_chunk() should return all data in the buffer.
        """
        self.q += text

        if last:
            self.state = PassageBuffer.State.finalized

    def get_chunk(self) -> str | None:
        if self.state == PassageBuffer.State.finalized:
            result = ''.join(self.q)
            self.q = ''
            return result
        if not self.streaming and self.state != PassageBuffer.State.finalized:
            return None
        
        split_index = -1
        for c in self.stop_charters:
            last_occur_index = self.q.rfind(c)
            split_index = max(split_index, last_occur_index)
        
        if split_index == -1 and len(self.q) > self.n_max_buffer:
            split_index = len(self.q)

        chunk = self.q[0:split_index+1]
        self.q = self.q[split_index+1:]
        if len(chunk) == 0: chunk = None

        logger.info(f'Buffer remain {len(self.q)} charters.')

        return chunk