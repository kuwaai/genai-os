import asyncio
import logging

from src.local_rule_indexer import LocalRuleIndexer
from src.server import serve, _cleanup_coroutines

async def main():
    local_rule_indexer = LocalRuleIndexer()
    task_index_local_rule = asyncio.create_task(local_rule_indexer.update_local_rule())
    task_serve = asyncio.create_task(serve())

    await task_index_local_rule
    await serve

if __name__ == "__main__":
    logging.basicConfig(level=logging.INFO)
    loop = asyncio.get_event_loop()
    try:
        loop.run_until_complete(main())
    finally:
        loop.run_until_complete(*_cleanup_coroutines)
        loop.close()