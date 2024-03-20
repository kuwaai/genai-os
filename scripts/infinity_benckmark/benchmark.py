from langchain.embeddings.infinity import InfinityEmbeddings
import numpy as np
import timeit
import random, string
import fire
import asyncio
import os

async def benchmark(emb_model, passages, repeat=100):
    
    # Function under test
    async def dut():
        nonlocal emb_model, passages
        await asyncio.gather(
            *[
                m.aembed_documents([p])
                for m, p in zip(emb_model, passages)
            ]
        )

    time_s = []
    for _ in range(repeat):
        t_start = timeit.default_timer()
        await dut()
        elapsed = timeit.default_timer() - t_start
        time_s.append(elapsed)

    time_ms = np.array(time_s) * 1000

    return time_ms

def main(
    num_doc=2048,
    doc_len=200,
    repeat=100,
    api_url="http://localhost:8181",
    model="thenlper/gte-large-zh"
    ):

    emb_model = [
        InfinityEmbeddings(
            model=model,
            infinity_api_url=api_url
        )
        for _ in range(num_doc)
    ]
    
    print(f'PID={os.getpid()}')
    print(f'num_doc={num_doc}, doc_len={doc_len}, repeat={repeat}')
    print('Random generating testing passages.')
    passages = [
        ''.join([random.choice(string.ascii_letters) for _ in range(doc_len)])
        for _ in range(num_doc)
    ]
    assert len(passages) == num_doc
    assert len(passages[0]) == doc_len

    print('Benchmarking.')
    loop = asyncio.get_event_loop()
    time_ms = loop.run_until_complete(
        benchmark(emb_model, passages, repeat)
    )
    tput_rps = num_doc / (time_ms/1000)
    print('='*10 + ' Result ' + '='*10)
    
    print(f'Response time [ms]: {np.mean(time_ms):.3f} ± {2*np.std(time_ms):.3f}')
    print(f'Throughput [rps]:   {np.mean(tput_rps):.3f} ± {2*np.std(tput_rps):.3f}')

if __name__ == "__main__":
    fire.Fire(main)

