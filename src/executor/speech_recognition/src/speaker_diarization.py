import logging
import torch
import torchaudio
import pyannote.audio
import scipy.io.wavfile
from pyannote.audio.pipelines.utils.hook import ProgressHook

logger = logging.getLogger(__name__)

class Diary:
    def __init__(self, duration_thld_sec=1.0):
        self.diary = []
        self.duration_thld_sec = duration_thld_sec

    def _is_overlap(self, s1:float, e1:float, s2:float, e2:float):
        """
        Assumption e > s.
        """
        return not (s2 > e1 or s1 > e2)
    
    def insert(self, start:float, end:float, speaker=None):
        if (end-start) <= self.duration_thld_sec: return
        self.diary.append({"start": start, "end": end, "speaker": speaker})

    def query(self, start:float, end:float):
        """
        Query the values in the range.
        This is a simple O(n) method, maybe there's a O(log(n)) method.
        """
        result = []
        for record in self.diary:
            if not self._is_overlap(record["start"], record["end"], start, end):
                continue
            result.append(record["speaker"])
        return list(set(result))
    
    def annotate_transcript(self, transcript:[dict]):
        """
        Annotate the transcript with speaker.
        """
        segment_template = {
            "start_time": 0,
            "end_time": 0,
            "text": "",
            "speaker": None
        }
        last_segment = segment_template.copy()
        result = []
        for segment in transcript:
            speaker = self.query(segment["start_time"], segment["end_time"])
            if speaker == last_segment["speaker"] or len(speaker) == 0:
                last_segment["text"] += segment["text"]
                last_segment["end_time"] = max(last_segment["end_time"], segment["end_time"])
            else:
                if last_segment["text"].strip() != "":
                    last_segment["text"] = last_segment["text"].strip()
                    result.append(last_segment)
                last_segment = segment_template.copy()
                last_segment["start_time"] = segment["start_time"]
                last_segment["end_time"] = segment["end_time"]
                last_segment["text"] = segment["text"]
                last_segment["speaker"] = speaker
        result = result + [last_segment]
        return result
    
    def __repr__(self):
        return "\n".join([
            f"{record['start']:010.2f} -> {record['end']:010.2f}: {record['speaker']}"
            for record in self.diary
        ])

class SpeakerDiarization:
    def __init__(self):
        pass

    async def diarization(self, src_audio_file:str, num_speakers:int, **kwargs) -> [dict]:
        """
        Annotate the speaker diarization
        Input an audio file and output the diarization with following format:
          - start: Start time of the segment
          - end: End time of the segment
          - speaker: The ID of the speaker
        """
        raise NotImplementedError("You should implement this method for different speaker diarization.")

class PyannoteSpeakerDiarization(SpeakerDiarization):
    def __init__(self, pipeline_name = "pyannote/speaker-diarization-3.1"):
        self.pipeline = pyannote.audio.Pipeline.from_pretrained(
            pipeline_name
        )
        self.pipeline.to(torch.device("cuda"))

    async def diarization(self, src_audio_file:str, num_speakers:int, **kwargs) -> [dict]:
        waveform, sample_rate = torchaudio.load(src_audio_file, normalize=True)
        logger.debug(f"Loaded {src_audio_file}")
        with ProgressHook() as hook:
            diarization = self.pipeline(
                {"waveform": waveform, "sample_rate": sample_rate},
                hook=hook,
                num_speakers=num_speakers
            )
        
        result = Diary()
        for turn, _, speaker in diarization.itertracks(yield_label=True):
            result.insert(
                start=turn.start,
                end=turn.end,
                speaker=speaker
            )

        return result