import unittest

from llm_safety_guard.buffer import PassageBuffer

class TestBuffer(unittest.TestCase):

    def test_stop_charter(self):
        test_passage = "在一疊舊照片中，有一張照片深深地烙印在我的記憶裡。這張照片的印象深刻的原因，是它記錄了我成長的過程、與他人的情景、環境的變遷和美麗"
        expected_chunk = "在一疊舊照片中，有一張照片深深地烙印在我的記憶裡。這張照片的印象深刻的原因，"
        buffer = PassageBuffer(streaming=True)
        buffer.append(test_passage)
        chunk = buffer.get_chunk()
        self.assertEqual(chunk, expected_chunk)
    
    def test_finalized(self):
        test_passage = "在一疊舊照片中，有一張照片深深地烙印在我的記憶裡。這張照片的印象深刻的原因，是它記錄了我成長的過程、與他人的情景、環境的變遷和美麗"
        buffer = PassageBuffer(streaming=True)
        buffer.append(test_passage)
        buffer.finalize()
        chunk = buffer.get_chunk()
        self.assertEqual(chunk, test_passage)
    
    def test_n_max_buffer(self):
        test_passage = "在一疊舊照片中有一張照片深深地烙印在我的記憶裡這張照片的印象深刻的原因是它記錄了我成長的過程與他人的情景環境的變遷和美麗"
        expected_chunk = "在一疊舊照片中有一張照片深深地烙印在我的記憶裡這張照片的印象深刻的原因是它記錄了我成長的過程與他人的情景環境的變遷和美麗"
        buffer = PassageBuffer(n_max_buffer=5, streaming=True)
        buffer.append(test_passage)
        chunk = buffer.get_chunk()
        self.assertEqual(chunk, expected_chunk)

if __name__ == '__main__':
    unittest.main()