import logging
from typing import List, Optional
from pathlib import Path

from langchain.docstore.document import Document
from langchain.document_loaders.text import TextLoader
import trafilatura

logger = logging.getLogger(__name__)

class TrafilaturaLoader(TextLoader):
    """Extract text from files with textract.

    Args:
        file_path: Path to the file to load.
    """

    def __init__(self, file_path: str, encoding=None, autodetect_encoding: bool = False):
        self.file_path = file_path
        self.encoding = encoding
        self.autodetect_encoding = autodetect_encoding

    def lazy_load(self) -> List[Document]:
        """Load from file path."""
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
    
    async def mime_extractor(content: str, content_type: str) -> str:
        mime_extractor = {
            'text/html': html_extractor,
            'multipart/related':  html_extractor,
            'application/xhtml+xml': html_extractor,
        }
        fallback_extractor = file_extractor

        mime_type = content_type.split(';', 1)[0]
        extractor = fallback_extractor
        if mime_type in mime_extractor:
            extractor = mime_extractor[mime_type]
        
        return await extractor(content, content_type)
