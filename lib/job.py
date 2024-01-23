import asyncio
import inspect
from timeit import default_timer

# Decorator for a periodically async job.
# The signature of the original function is preserved.
def periodically_async_job(period_sec:float, min_sleep_sec:float=1.0):
    def decorator(func):
        async def wrap(*args, **kwargs):
            while True:
                start_time = default_timer()
                await func(*args, **kwargs)
                time_left_sec = period_sec - (default_timer() - start_time)
                await asyncio.sleep(max(time_left_sec, min_sleep_sec))
        wrap.__signature__ = inspect.signature(func)
        return wrap
    return decorator