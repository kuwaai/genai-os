import asyncio
import logging

from src.local_rule_indexer import LocalRuleIndexer

async def main():
    local_rule_indexer = LocalRuleIndexer()
    await local_rule_indexer.update_local_rule()
    task_index_local_rule = asyncio.create_task(local_rule_indexer.update_local_rule())

    await task_index_local_rule

if __name__ == "__main__":
    logging.basicConfig(level=logging.DEBUG)
    asyncio.run(main())
