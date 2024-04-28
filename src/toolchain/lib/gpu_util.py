from torch import cuda
import os
import logging

logger = logging.getLogger(__name__)

def check_gpu():
    if cuda.is_available():
        gpu_count = cuda.device_count()
        logger.info(f'{gpu_count} GPU is available and being used.')
        for i in range(gpu_count):
            logger.info(cuda.get_device_name(i))
    else:
        logger.warning('GPU is not available, will use CPU instead.')