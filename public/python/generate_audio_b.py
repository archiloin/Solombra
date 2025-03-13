import numpy as np
from gtts import gTTS
from scipy.io.wavfile import write
from io import BytesIO
import tempfile
import os
import wave
import audioop

# Constantes de sécurité
VALID_FREQ_RANGE = (20, 22000)  # Plage de fréquences acceptables

def sanitize_message(message):
    """
    Nettoie le texte pour éviter les abus.
    """
    if not isinstance(message, str) or len(message) > 500:
        raise ValueError("Message invalide ou trop long.")
    return ''.join(char for char in message if char.isalnum() or char.isspace())

def text_to_speech(message, sample_rate=44100):
    """
    Convertit un message texte en signal audio sans utiliser ffmpeg ou pydub.
    """
    sanitized_message = sanitize_message(message)

    # Génération d'un fichier MP3 temporaire avec gTTS
    with tempfile.NamedTemporaryFile(delete=False, suffix=".mp3") as temp_mp3:
        tts = gTTS(text=sanitized_message, lang='fr')
        tts.save(temp_mp3.name)

        # Conversion MP3 → WAV
        with tempfile.NamedTemporaryFile(delete=False, suffix=".wav") as temp_wav:
            os.system(f"lame --decode {temp_mp3.name} {temp_wav.name}")

            # Chargement du fichier WAV avec wave
            with wave.open(temp_wav.name, 'rb') as wav_file:
                n_channels = wav_file.getnchannels()
                sample_width = wav_file.getsampwidth()
                frame_rate = wav_file.getframerate()
                n_frames = wav_file.getnframes()
                audio_data = wav_file.readframes(n_frames)

            # Convertir les données audio en tableau numpy
            audio = np.frombuffer(audio_data, dtype=np.int16)
            os.remove(temp_mp3.name)
            os.remove(temp_wav.name)

            # Normalisation
            return audio.astype(np.float32) / np.max(np.abs(audio))

def butter_highpass(cutoff, sample_rate, order=5):
    """
    Construit un filtre passe-haut.
    """
    from scipy.signal import butter
    nyquist = 0.5 * sample_rate
    normal_cutoff = cutoff / nyquist
    b, a = butter(order, normal_cutoff, btype='high', analog=False)
    return b, a

def apply_highpass_filter(audio, cutoff, sample_rate, order=5):
    """
    Applique un filtre passe-haut au signal audio.
    """
    from scipy.signal import lfilter
    b, a = butter_highpass(cutoff, sample_rate, order=order)
    return lfilter(b, a, audio)

def modulate_audio(audio, carrier_freq, sample_rate):
    """
    Modulation du signal audio avec suppression des fréquences en dessous de la fréquence porteuse.
    """
    t = np.arange(len(audio)) / sample_rate
    carrier_wave = np.sin(2 * np.pi * carrier_freq * t)
    modulated = audio * carrier_wave

    # Appliquer un filtre passe-haut pour supprimer les fréquences inférieures à la fréquence porteuse
    modulated_filtered = apply_highpass_filter(modulated, cutoff=carrier_freq, sample_rate=sample_rate)
    return modulated_filtered / np.max(np.abs(modulated_filtered))

def generate_audio(message, carrier_freq, sample_rate=44100):
    """
    Génère un fichier audio `.wav` avec modulation.
    """
    if not (VALID_FREQ_RANGE[0] <= carrier_freq <= VALID_FREQ_RANGE[1]):
        raise ValueError(f"Fréquence hors limites : {carrier_freq} Hz")

    # Génération du signal vocal
    audio = text_to_speech(message, sample_rate)
    modulated_audio = modulate_audio(audio, carrier_freq, sample_rate)

    # Conversion en format WAV
    buffer = BytesIO()
    write(buffer, sample_rate, (modulated_audio * 32767).astype(np.int16))
    buffer.seek(0)
    return buffer.getvalue()

if __name__ == "__main__":
    import sys
    try:
        message = sys.argv[1]
        carrier_freq = int(sys.argv[2])
        sample_rate = 44100
        sys.stdout.buffer.write(generate_audio(message, carrier_freq, sample_rate))
    except Exception as e:
        print(f"Erreur : {e}", file=sys.stderr)
        sys.exit(1)
