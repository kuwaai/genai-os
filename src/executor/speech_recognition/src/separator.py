import logging
import torch
import torchaudio
import pyannote.audio
import scipy.io.wavfile
from pyannote.audio.pipelines.utils.hook import ProgressHook

logger = logging.getLogger(__name__)

class SpeakerSeparator:
    def __init__(self):
        pass

    async def separate(self, src_audio_file:str, output_dir:str, num_speakers:int, **kwargs) -> [str]:
        """
        Separate the different speaker.
        Input an audio file and output separated audio files.
        """
        raise NotImplementedError("You should implement this method for different speaker separator.")

class PyannoteSpeakerSeparator(SpeakerSeparator):
    def __init__(self, pipeline_name = "pyannote/speech-separation-ami-1.0"):
        self.pipeline = pyannote.audio.Pipeline.from_pretrained(
            # "pyannote/speaker-diarization-3.1"
            pipeline_name
        )
        if torch.cuda.is_available():
            logger.info("Using CUDA")
            self.pipeline.to(torch.device("cuda"))

    async def separate(self, src_audio_file:str, output_dir:str, num_speakers:int, **kwargs) -> [str]:
        waveform, sample_rate = torchaudio.load(src_audio_file, normalize=True)
        logger.debug(f"Loaded {src_audio_file}")
        with ProgressHook() as hook:
            diarization, sources = self.pipeline(
                {"waveform": waveform, "sample_rate": sample_rate},
                hook=hook,
                num_speakers=num_speakers
            )

        output_files = []
        output_sample_rate = kwargs.get("sample_rate", 16000)
        for s, speaker in enumerate(diarization.labels()):
            output_file = f'{output_dir}/{speaker}.wav'
            scipy.io.wavfile.write(
                filename=output_file,
                data=sources.data[:,s],
                rate=output_sample_rate,
            )
            output_files.append(output_file)
        
        return output_files