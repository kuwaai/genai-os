import logging
from typing import List, Optional
from pathlib import Path

from langchain.docstore.document import Document
from langchain.document_loaders.text import TextLoader
import trafilatura
import textract
from magika import Magika

logger = logging.getLogger(__name__)
magika = Magika()

class FileTextLoader(TextLoader):
    """Extract text from files with textract or Trafilatura.

    Args:
        file_path: Path to the file to load.
    """

    def __init__(self, file_path: str, encoding="utf-8", autodetect_encoding: bool = False):
        self.file_path = file_path
        self.encoding = encoding
        self.autodetect_encoding = autodetect_encoding

    def lazy_load(self) -> List[Document]:
        """Load text from file path."""
        
        file_magic_result = magika.identify_path(Path(self.file_path))
        file_mime_type = file_magic_result.output.mime_type.split(';', 1)[0]
        logger.debug(f"{self.file_path}: {file_mime_type}")
        
        mime_extractor = {
            'text/html': self.load_html,
            'multipart/related': self.load_html,
            'application/xhtml+xml': self.load_html,
        }
        fallback_extractor = self.load_doc

        extractor = fallback_extractor
        if file_mime_type in mime_extractor:
            extractor = mime_extractor[file_mime_type]
        
        return extractor()
    
    def load_doc(self) -> List[Document]:
        """Load text from document file."""
        text = ""
        try:
            text = textract.process(self.file_path, encoding=self.encoding)
            text = text.decode(self.encoding)
        except Exception as e:
            logger.warning(f"Error loading {self.file_path}. Skipped.")
            return []

        metadata = {"source": self.file_path}
        return [Document(page_content=text, metadata=metadata)]

    def load_html(self) -> List[Document]:
        """Load text from HTML file."""
        text = ""
        try:
            config = trafilatura.settings.use_config()
            config.set("DEFAULT", "EXTRACTION_TIMEOUT", "0")
            
            content = Path(self.file_path).read_text()
            text = trafilatura.extract(
                content,
                favor_precision=True,
                config=config,
            )
        except Exception as e:
            raise RuntimeError(f"Error loading {self.file_path}") from e

        metadata = {"source": self.file_path}
        return [Document(page_content=text, metadata=metadata)]