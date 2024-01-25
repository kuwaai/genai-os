import os
import logging
import requests

logger = logging.getLogger(__name__)

class TargetCache:
    """
    Communicate with the internal API to maintain the target cache.
    The purpose is to speed-up the non-guarded models.
    """

    def __init__(self):
        base_url = os.environ.get('SAFETY_GUARD_BASE_URL', 'http://127.0.0.1:8000')
        self.endpoint = f'{base_url}/v1/internal/targets'
        self.targets = []

    def should_guard(self, model_id:str):
        """
        Check whether the model_id is in the target list.
        """
        return model_id in self.targets

    def update_list(self):
        resp = requests.get(self.endpoint)
        if not resp.ok:
            logger.warning('Unable to update the target list')
            return
        self.targets = resp.json()

    def __repr__(self):
        return repr(self.targets)

# Singleton pattern
def get_target_cache(cache=TargetCache()):
    return cache