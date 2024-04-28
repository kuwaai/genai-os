import logging
from typing import List, Optional

from langchain.docstore.document import Document
from langchain.document_loaders.text import TextLoader
import textract

logger = logging.getLogger(__name__)

class TextractLoader(TextLoader):
    """Extract text from files with textract.

    Args:
        file_path: Path to the file to load.
    """

    def __init__(self, file_path: str,):
        self.file_path = file_path

    def load(self) -> List[Document]:
        """Load from file path."""
        text = ""
        try:
            text = textract.process(self.file_path)
        except Exception as e:
            raise RuntimeError(f"Error loading {self.file_path}") from e

        metadata = {"source": self.file_path}
        return [Document(page_content=text, metadata=metadata)]