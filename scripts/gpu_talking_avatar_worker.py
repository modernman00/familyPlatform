#!/usr/bin/env python3
"""
ATG Sovereign Neural Talking Avatar GPU Sidecar Worker
Speaks the PythonSidecarBridge newline-delimited JSON protocol over UNIX socket
(default: /tmp/atg-sidecar.sock) or TCP (ATG_PYTHON_SIDECAR_HOST:PORT).

Pipeline per job (`action == "talking_avatar"`):
  1. If `script_text` is provided without `audio_path`, synthesises speech WAV
     using Kokoro-82M (`kokoro`) or Piper TTS (`piper`).
  2. Runs LivePortrait / MuseTalk on (`image_path`, `audio_path`) to generate
     natural blinks, head turns, smiles, and crisp lip-sync (shell=False).
  3. Muxes H.264 MP4 (`yuv420p`, `aac`) into private `storage/talking_avatars/`
     and updates `manifest_path` to `status: "completed"` with owner-gated URL.
"""

import json
import os
import re
import shlex
import shutil
import socket
import subprocess
import threading
import time
from pathlib import Path

ROOT_DIR = Path(__file__).resolve().parent.parent
JOBS_DIR = ROOT_DIR / "storage" / "talking_avatar_jobs"
OUT_DIR = ROOT_DIR / "storage" / "talking_avatars"
JOBS_DIR.mkdir(parents=True, exist_ok=True)
OUT_DIR.mkdir(parents=True, exist_ok=True)

JOB_ID_RE = re.compile(r"^[a-f0-9]{32}$")
USER_ID_RE = re.compile(r"^[A-Za-z0-9_-]{1,64}$")
MAX_SOCKET_BYTES = 262_144  # 256 KB
DEFAULT_TTL_SECONDS = 86_400  # 24 hours


def purge_expired_files(max_age_sec: int = DEFAULT_TTL_SECONDS) -> None:
    """Remove staged job artifacts and rendered MP4s older than max_age_sec."""
    cutoff = time.time() - max_age_sec
    for directory in (JOBS_DIR, OUT_DIR):
        if not directory.exists():
            continue
        for entry in directory.iterdir():
            try:
                if entry.is_file() and entry.stat().st_mtime < cutoff:
                    entry.unlink(missing_ok=True)
            except OSError:
                pass


def update_manifest(manifest_path: str, patch: dict) -> None:
    p = Path(manifest_path)
    data = {}
    if p.exists():
        data = json.loads(p.read_text(encoding="utf-8"))
    data.update(patch)
    p.write_text(json.dumps(data, indent=2), encoding="utf-8")


def synthesise_tts(script_text: str, voice_id: str, wav_out: Path) -> None:
    """Synthesise natural speech using Kokoro-82M (if installed) or Piper TTS."""
    try:
        from kokoro import KPipeline  # type: ignore
        import soundfile as sf  # type: ignore

        lang = "b" if voice_id.startswith("b") else "a"
        pipeline = KPipeline(lang_code=lang)
        audio_chunks = [audio for _, _, audio in pipeline(script_text, voice=voice_id)]
        if audio_chunks:
            import numpy as np  # type: ignore

            sf.write(str(wav_out), np.concatenate(audio_chunks), 24000)
            return
    except ImportError:
        pass

    # Fallback 2: Piper CLI if installed (shell=False)
    if shutil.which("piper"):
        subprocess.run(
            ["piper", "--model", os.environ.get("ATG_PIPER_MODEL", "en_GB-alba-medium.onnx"), "--output_file", str(wav_out)],
            input=script_text.encode("utf-8"),
            check=True,
            shell=False,
        )
        return

    # Fallback 3: macOS native `say` + ffmpeg for local Mac dev
    if shutil.which("say") and shutil.which("ffmpeg"):
        aiff_tmp = wav_out.with_suffix(".aiff")
        voice_map = {"bf_emma": "Samantha", "bm_george": "Daniel", "af_bella": "Samantha", "am_adam": "Alex"}
        subprocess.run(["say", "-v", voice_map.get(voice_id, "Daniel"), "-o", str(aiff_tmp), script_text], check=True, shell=False)
        subprocess.run(["ffmpeg", "-y", "-i", str(aiff_tmp), str(wav_out)], check=True, shell=False, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        aiff_tmp.unlink(missing_ok=True)
        return

    raise RuntimeError("No TTS engine available (install kokoro, piper, or run on macOS with say+ffmpeg).")


def run_neural_lip_sync(image_path: str, audio_path: str, preset: str, mp4_out: Path) -> None:
    """
    Hook your LivePortrait / MuseTalk inference command via env without shell=True:
      ATG_NEURAL_AVATAR_CMD="python /opt/LivePortrait/inference.py -s {image} -a {audio} -o {output} --preset {preset}"
    """
    cmd_tpl = os.environ.get("ATG_NEURAL_AVATAR_CMD", "").strip()
    if cmd_tpl:
        tokens = shlex.split(cmd_tpl)
        replacements = {
            "{image}": image_path,
            "{audio}": audio_path,
            "{output}": str(mp4_out),
            "{preset}": preset,
        }
        argv = [replacements.get(tok, tok) for tok in tokens]
        subprocess.run(argv, check=True, shell=False)
        return

    # Deterministic FFmpeg still+audio fallback when GPU inference binary is not configured
    subprocess.run(
        [
            "ffmpeg", "-y", "-loop", "1", "-i", image_path, "-i", audio_path,
            "-c:v", "libx264", "-tune", "stillimage", "-pix_fmt", "yuv420p",
            "-vf", "scale=512:512:force_original_aspect_ratio=decrease,pad=512:512:(ow-iw)/2:(oh-ih)/2",
            "-c:a", "aac", "-b:a", "128k", "-shortest", str(mp4_out),
        ],
        check=True,
        shell=False,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )


def generate_depth_map(image_path: str, depth_out: Path) -> None:
    """
    Generate a 16-bit/8-bit greyscale depth map using Depth Anything V2
    (gives 2.5D Living Photo & Post Images true foreground/background parallax).
    """
    cmd_tpl = os.environ.get("ATG_DEPTH_CMD", "").strip()
    if cmd_tpl:
        subprocess.run(cmd_tpl.format(image=image_path, output=str(depth_out)), shell=True, check=True)
        return

    from transformers import pipeline  # type: ignore
    from PIL import Image  # type: ignore

    estimator = pipeline(task="depth-estimation", model="depth-anything/Depth-Anything-V2-Small-hf")
    result = estimator(Image.open(image_path).convert("RGB"))
    result["depth"].save(str(depth_out))


def process_job(payload: dict) -> None:
    job_id = str(payload.get("job_id", ""))
    user_id = str(payload.get("user_id", ""))
    manifest_path = str(payload.get("manifest_path", ""))
    if not JOB_ID_RE.match(job_id) or not USER_ID_RE.match(user_id):
        return
    try:
        purge_expired_files()
        audio_path = payload.get("audio_path")
        if not audio_path and payload.get("script_text"):
            wav_path = Path(manifest_path).with_suffix(".wav")
            synthesise_tts(str(payload["script_text"]), str(payload.get("voice_id", "bf_emma")), wav_path)
            audio_path = str(wav_path)

        mp4_out = OUT_DIR / f"{user_id}_{job_id}.mp4"
        run_neural_lip_sync(
            str(payload["image_path"]),
            str(audio_path),
            str(payload.get("expression_preset", "natural")),
            mp4_out,
        )
        update_manifest(
            manifest_path,
            {
                "status": "completed",
                "video_url": f"/api/emoji/talking-avatar/video/{job_id}",
            },
        )
    except Exception as exc:
        if manifest_path:
            update_manifest(manifest_path, {"status": "failed", "error": str(exc)})


def _read_ndjson_line(conn: socket.socket) -> str:
    chunks = []
    total = 0
    while total < MAX_SOCKET_BYTES:
        chunk = conn.recv(8192)
        if not chunk:
            break
        chunks.append(chunk)
        total += len(chunk)
        if b"\n" in chunk:
            break
    return b"".join(chunks).decode("utf-8", errors="replace").split("\n", 1)[0].strip()


def handle_client(conn: socket.socket) -> None:
    try:
        raw = _read_ndjson_line(conn)
        if not raw:
            return
        req = json.loads(raw)
        action = req.get("action")
        payload = req.get("payload") or {}

        if action == "ping":
            conn.sendall((json.dumps({"status": "ok"}) + "\n").encode("utf-8"))
            return

        if action == "talking_avatar":
            threading.Thread(target=process_job, args=(payload,), daemon=True).start()
            conn.sendall((json.dumps({"status": "queued", "job_id": payload.get("job_id")}) + "\n").encode("utf-8"))
            return

        if action == "depth_map":
            depth_out = OUT_DIR / f"{payload['job_id']}_depth.png"
            generate_depth_map(payload["image_path"], depth_out)
            conn.sendall((json.dumps({"status": "ok", "depth_url": f"/public/uploads/talking_avatars/{depth_out.name}"}) + "\n").encode("utf-8"))
            return

        conn.sendall((json.dumps({"status": "error", "message": f"Unknown action: {action}"}) + "\n").encode("utf-8"))
    finally:
        conn.close()


def main() -> None:
    purge_expired_files()
    host = os.environ.get("ATG_PYTHON_SIDECAR_HOST", "").strip()
    port = int(os.environ.get("ATG_PYTHON_SIDECAR_PORT", "8484"))

    if host:
        srv = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        srv.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        srv.bind((host, port))
        srv.listen(16)
        print(f"[ATG Neural Talking Avatar Worker] Listening on tcp://{host}:{port}")
    else:
        sock_path = os.environ.get("ATG_PYTHON_SIDECAR_SOCKET", "/tmp/atg-sidecar.sock")
        if os.path.exists(sock_path):
            os.unlink(sock_path)
        srv = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        srv.bind(sock_path)
        os.chmod(sock_path, 0o660)
        srv.listen(16)
        print(f"[ATG Neural Talking Avatar Worker] Listening on unix://{sock_path}")

    while True:
        conn, _ = srv.accept()
        threading.Thread(target=handle_client, args=(conn,), daemon=True).start()


if __name__ == "__main__":
    main()
