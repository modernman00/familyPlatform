import { useEffect, useRef, useState } from 'preact/hooks';
import { formatElapsedMs } from './arcadeEngine';
import styles from './ArcadeShell.module.css';

export function VictoryReplayModal({
    result,
    gameLabel,
    playerName,
    replaySteps = [],
    onClose,
    onShareToFeed,
    sharingToFeed,
}) {
    const canvasRef = useRef(null);
    const [recording, setRecording] = useState(false);
    const [copied, setCopied] = useState(false);

    const shareUrl = typeof window !== 'undefined'
        ? `${window.location.origin}${result?.shareUrl || '/arcade'}`
        : (result?.shareUrl || '/arcade');

    // Draw animated victory replay onto Canvas
    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let frame = 0;
        let animId = 0;
        const steps = Array.isArray(replaySteps) && replaySteps.length > 0 ? replaySteps : [{ t: 0 }];

        const renderFrame = () => {
            frame += 1;
            const w = canvas.width;
            const h = canvas.height;

            // Rich dark crimson-gold gradient background
            const grad = ctx.createLinearGradient(0, 0, w, h);
            grad.addColorStop(0, '#0f172a');
            grad.addColorStop(0.55, '#450a0a');
            grad.addColorStop(1, '#1e1b4b');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, w, h);

            ctx.strokeStyle = '#fbbf24';
            ctx.lineWidth = 4;
            ctx.strokeRect(12, 12, w - 24, h - 24);

            // Header text
            ctx.fillStyle = '#fbbf24';
            ctx.font = 'bold 18px system-ui, sans-serif';
            ctx.fillText(`FAMILY ARCADE • ${gameLabel.toUpperCase()} VICTORY REPLAY`, 32, 48);

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 34px system-ui, sans-serif';
            ctx.fillText(`${result?.score || 0} PTS`, 32, 96);

            ctx.fillStyle = '#fde68a';
            ctx.font = '600 16px system-ui, sans-serif';
            ctx.fillText(`${playerName} • ${formatElapsedMs(result?.elapsedMs || 0)}`, 32, 126);

            // Animated replay progress bar & step sparks
            const progressRatio = Math.min(1, ((frame % 180) / 150));
            const visibleSteps = Math.max(1, Math.floor(steps.length * progressRatio));

            ctx.fillStyle = 'rgba(255,255,255,0.12)';
            ctx.fillRect(32, 155, w - 64, 14);
            ctx.fillStyle = '#fbbf24';
            ctx.fillRect(32, 155, (w - 64) * progressRatio, 14);

            // Draw step nodes
            const maxNodes = Math.min(24, steps.length);
            for (let i = 0; i < maxNodes; i++) {
                const active = i < Math.ceil((visibleSteps / steps.length) * maxNodes);
                const cx = 48 + (i % 12) * ((w - 96) / 11);
                const cy = 205 + Math.floor(i / 12) * 34;
                ctx.beginPath();
                ctx.arc(cx, cy, active ? 10 : 6, 0, Math.PI * 2);
                ctx.fillStyle = active ? '#fbbf24' : 'rgba(255,255,255,0.22)';
                ctx.fill();
            }

            ctx.fillStyle = '#e2e8f0';
            ctx.font = '500 13px system-ui, sans-serif';
            ctx.fillText('Can you beat my score? Open FamilyPlatform to race my ghost!', 32, h - 26);

            animId = window.requestAnimationFrame(renderFrame);
        };

        animId = window.requestAnimationFrame(renderFrame);
        return () => window.cancelAnimationFrame(animId);
    }, [result, gameLabel, playerName, replaySteps]);

    const handleDownloadPng = () => {
        const canvas = canvasRef.current;
        if (!canvas) return;
        const a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = `family-arcade-${gameLabel.toLowerCase()}-score.png`;
        a.click();
    };

    const handleDownloadVideo = () => {
        const canvas = canvasRef.current;
        if (!canvas || typeof MediaRecorder === 'undefined' || !canvas.captureStream) {
            handleDownloadPng();
            return;
        }
        setRecording(true);
        const stream = canvas.captureStream(30);
        const chunks = [];
        const recorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
        recorder.ondataavailable = (e) => {
            if (e.data && e.data.size > 0) chunks.push(e.data);
        };
        recorder.onstop = () => {
            setRecording(false);
            const blob = new Blob(chunks, { type: 'video/webm' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `family-arcade-${gameLabel.toLowerCase()}-replay.webm`;
            a.click();
            URL.revokeObjectURL(url);
        };
        recorder.start();
        window.setTimeout(() => {
            if (recorder.state !== 'inactive') recorder.stop();
        }, 2800);
    };

    const handleCopyLink = async () => {
        try {
            await navigator.clipboard.writeText(shareUrl);
            setCopied(true);
            window.setTimeout(() => setCopied(false), 2000);
        } catch {
            // Fallback
        }
    };

    const whatsappHref = `https://api.whatsapp.com/send?text=${encodeURIComponent(
        `🏆 I scored ${result?.score || 0} pts in ${gameLabel} on FamilyPlatform! Think you can beat my time? ${shareUrl}`
    )}`;
    const smsHref = `sms:?&body=${encodeURIComponent(
        `🏆 Beat my ${gameLabel} score (${result?.score || 0} pts) on FamilyPlatform: ${shareUrl}`
    )}`;

    return (
        <div className={styles.modalBackdrop} role="dialog" aria-modal="true" aria-label="Victory Replay and Challenge Card">
            <div className={styles.modalCard}>
                <div className={styles.modalHeader}>
                    <div>
                        <span className={styles.trophyPill}>{result?.isNewRecord ? 'NEW FAMILY RECORD!' : 'PUZZLE COMPLETE'}</span>
                        <h3 className={styles.modalTitle}>
                            {result?.score || 0} pts in {formatElapsedMs(result?.elapsedMs || 0)}
                        </h3>
                    </div>
                    <button type="button" className={styles.closeBtn} onClick={onClose} aria-label="Close victory modal">
                        <i className="bi bi-x-lg" aria-hidden="true" />
                    </button>
                </div>

                {Array.isArray(result?.awardedBadges) && result.awardedBadges.length > 0 && (
                    <div className={styles.badgeEarnedRow}>
                        {result.awardedBadges.map((b) => (
                            <span key={b} className={styles.crownBadge}>
                                <i className="bi bi-award-fill" aria-hidden="true" />
                                <span>{b}</span>
                            </span>
                        ))}
                    </div>
                )}

                <canvas ref={canvasRef} width={600} height={280} className={styles.replayCanvas} />

                <div className={styles.modalActions}>
                    <button
                        type="button"
                        className={`${styles.ctrlBtn} ${styles.ctrlBtnPrimary}`}
                        onClick={onShareToFeed}
                        disabled={sharingToFeed}
                    >
                        <i className="bi bi-megaphone-fill" aria-hidden="true" />
                        <span>{sharingToFeed ? 'Posting...' : 'Post to Family Feed'}</span>
                    </button>
                    <a href={whatsappHref} target="_blank" rel="noopener noreferrer" className={styles.ctrlBtn}>
                        <i className="bi bi-whatsapp" aria-hidden="true" />
                        <span>WhatsApp</span>
                    </a>
                    <a href={smsHref} className={styles.ctrlBtn}>
                        <i className="bi bi-chat-dots-fill" aria-hidden="true" />
                        <span>Messages</span>
                    </a>
                    <button type="button" className={styles.ctrlBtn} onClick={handleCopyLink}>
                        <i className="bi bi-link-45deg" aria-hidden="true" />
                        <span>{copied ? 'Copied!' : 'Copy Link'}</span>
                    </button>
                    <button type="button" className={styles.ctrlBtn} onClick={handleDownloadPng}>
                        <i className="bi bi-image-fill" aria-hidden="true" />
                        <span>Save Card</span>
                    </button>
                    <button type="button" className={styles.ctrlBtn} onClick={handleDownloadVideo} disabled={recording}>
                        <i className="bi bi-film" aria-hidden="true" />
                        <span>{recording ? 'Recording...' : 'Replay Video'}</span>
                    </button>
                </div>
            </div>
        </div>
    );
}
