import logging
import string
import torch
import torchaudio
import pyannote.audio
import scipy.io.wavfile
from pyannote.audio.pipelines.utils.hook import ProgressHook

logger = logging.getLogger(__name__)

class Diary:
    punctuation = string.punctuation + "！？｡。＂＃＄％＆＇（）＊＋，－／：；＜＝＞＠［＼］＾＿｀｛｜｝～｟｠｢｣､、〃》「」『』【】〔〕〖〗〘〙〚〛〜〝〞〟〰〾〿–—‘’‛“”„‟…‧﹏."
    segment_template = {
        "start_time": 0,
        "end_time": 0,
        "text": "",
        "speaker": []
    }

    def __init__(self, duration_thld_sec=1.0):
        self.diary = []
        self.duration_thld_sec = duration_thld_sec

    def _overlap(self, s0:float, e0:float, s1:float, e1:float):
        """
        Assumption e > s.
        """
        return max(0, min(e0, e1) - max(s0, s1))

    def insert(self, start:float, end:float, speaker=None):
        if (end-start) <= self.duration_thld_sec: return
        self.diary.append({"start": start, "end": end, "speaker": speaker})

    def query(self, start:float, end:float):
        """
        Query the values in the range.
        This is a simple O(n) method, maybe there's a O(log(n)) method.
        """
        result = {}
        for record in self.diary:
            overlap_len = self._overlap(record["start"], record["end"], start, end)
            if overlap_len == 0: continue

            speaker = record["speaker"]
            result[speaker] = result.get(speaker, 0) + overlap_len
        return sorted(result.keys())

    def merge_segment(self, orig, next):
        result = orig.copy()
        result["text"] = orig["text"] + next["text"]
        result["end_time"] = max(orig["end_time"], next["end_time"])

        return result
    
    def annotate_transcript(self, transcript:[dict]):
        """
        Annotate the transcript with speaker.
        """
        
        last_segment = self.segment_template.copy()
        result = []
        for segment in transcript:
            speaker = self.query(segment["start_time"], segment["end_time"])
            if speaker == last_segment["speaker"] or len(speaker) == 0:
                last_segment = self.merge_segment(last_segment, segment)
                continue
            
            last_segment["text"] = last_segment["text"].strip()
            if last_segment["text"] != "":
                if last_segment["text"] in self.punctuation and len(result) > 0:
                    result[-1] = self.merge_segment(result[-1], last_segment)
                else:
                    result.append(last_segment.copy())

            last_segment.update(segment)
            last_segment["speaker"] = speaker
        result = result + [last_segment]
        return result
    
    def __repr__(self):
        return "\n".join([
            f"{record['start']:010.2f} -> {record['end']:010.2f}: {record['speaker']}"
            for record in self.diary
        ])

class SpeakerDiarizer:
    def __init__(self):
        pass

    def diarization(self, src_audio_file:str, num_speakers:int, **kwargs) -> [dict]:
        """
        Annotate the speaker diarization
        Input an audio file and output the diarization with following format:
          - start: Start time of the segment
          - end: End time of the segment
          - speaker: The ID of the speaker
        """
        raise NotImplementedError("You should implement this method for different speaker diarization.")

class PyannoteSpeakerDiarizer(SpeakerDiarizer):
    def __init__(self, pipeline_name = "pyannote/speaker-diarization-3.1"):
        self.pipeline_name = pipeline_name

    def _load_pipeline(self):
        self.pipeline = pyannote.audio.Pipeline.from_pretrained(
            self.pipeline_name
        )
        if torch.cuda.is_available():
            logger.info("Using CUDA")
            self.pipeline.to(torch.device("cuda"))

    def diarization(self, src_audio_file:str, num_speakers:int, **kwargs) -> [dict]:
        self._load_pipeline()
        waveform, sample_rate = torchaudio.load(src_audio_file, normalize=True)
        logger.debug("Diarizing...")
        logger.debug(f"Loaded {src_audio_file}")
        with ProgressHook() as hook:
            diarization = self.pipeline(
                {"waveform": waveform, "sample_rate": sample_rate},
                hook=hook,
                num_speakers=num_speakers
            )
        
        diary_param = {k:v for k,v in kwargs.items() if k in ["duration_thld_sec"]}
        result = Diary(**diary_param)
        for turn, _, speaker in diarization.itertracks(yield_label=True):
            result.insert(
                start=turn.start,
                end=turn.end,
                speaker=speaker
            )

        logger.debug("Done diarization.")
        logger.debug(f"Diary: {result}")
        return result