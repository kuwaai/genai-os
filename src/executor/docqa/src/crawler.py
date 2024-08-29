import logging
import os
import i18n

from .recursive_url_multimedia_loader import RecursiveUrlMultimediaLoader

logger = logging.getLogger(__name__)

class Crawler:

    def __init__(
        self,
        max_depth:int = 1,
        user_agent:str = None,
        cache_proxy_url = os.environ.get('HTTP_CACHE_PROXY', None),
    ):
        self.max_depth = max_depth
        self.user_agent = user_agent
        self.cache_proxy_url = cache_proxy_url

    async def fetch_documents(self, url:str):
        # Fetching documents
        logger.info(f'Fetching URL "{url}"')
        docs = []
        loader = RecursiveUrlMultimediaLoader(
            url=url,
            max_depth=self.max_depth,
            prevent_outside=False,
            use_async = True,
            cache_proxy_url = self.cache_proxy_url,
            forge_user_agent=self.user_agent
        ) 
        try:
            docs = await loader.async_load()
            logger.info(f'Fetched {len(docs)} documents.')
        except Exception as e:
            logger.warning(str(e))
            docs = []
        finally:
            return docs