import { useState, useEffect, useRef } from 'preact/hooks';
import { HEIRLOOM_REACTIONS, getHeirloomGlassSvg } from '../../components/profilePage/heirloomGlassReactions';
import { AtgLivingPhotoAvatarStage } from '../../components/livingPhotoAvatar';
import { apiFetch } from '../shared/apiClient';
import { getCsrfToken, notifyToast } from '../shared/bootstrap';
import { Portal } from '../shared/Portal';
import styles from './FeedShell.module.css';

const QUICK_MEDIA_EMOJIS = ['❤️', '😂', '👏', '🙏', '🎉', '😍'];

export function LightboxModal({
    isOpen,
    images = [],
    initialIndex = 0,
    initialAnimate = false,
    post = null,
    bootstrap = {},
    onClose,
    onReactPost,
    onMutateComments,
}) {
    const [index, setIndex] = useState(initialIndex);
    const [commentDraft, setCommentDraft] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [animateHere, setAnimateHere] = useState(false);
    const [animStatus, setAnimStatus] = useState('idle');
    const [exportBusy, setExportBusy] = useState(false);
    const [postFeedBusy, setPostFeedBusy] = useState(false);
    const touchStartX = useRef(null);
    const liveViewportRef = useRef(null);
    const liveCanvasRef = useRef(null);
    const liveStageRef = useRef(null);

    useEffect(() => {
        setIndex(initialIndex);
        setAnimateHere(Boolean(initialAnimate));
    }, [initialIndex, initialAnimate, isOpen]);

    useEffect(() => {
        if (!isOpen || !animateHere || !liveCanvasRef.current || !liveViewportRef.current) {
            if (liveStageRef.current) {
                liveStageRef.current.destroy();
                liveStageRef.current = null;
            }
            setAnimStatus('idle');
            return undefined;
        }
        const photoUrl = images[index] || images[0];
        if (!photoUrl) {
            setAnimStatus('error');
            return undefined;
        }
        setAnimStatus('loading');
        const stage = new AtgLivingPhotoAvatarStage({
            canvas: liveCanvasRef.current,
            viewport: liveViewportRef.current,
            photoUrl,
        });
        liveStageRef.current = stage;
        let cancelled = false;
        stage.init()
            .then((ok) => {
                if (cancelled) return;
                if (ok) {
                    setAnimStatus('ready');
                    stage.start();
                    stage.triggerReaction('hello', { playSound: false });
                } else {
                    setAnimStatus('error');
                }
            })
            .catch(() => {
                if (!cancelled) setAnimStatus('error');
            });
        return () => {
            cancelled = true;
            stage.destroy();
            if (liveStageRef.current === stage) liveStageRef.current = null;
        };
    }, [isOpen, animateHere, index, images]);

    useEffect(() => {
        if (!isOpen) return;
        const onKey = (e) => {
            if (e.key === 'Escape') onClose?.();
            if (e.key === 'ArrowRight' && images.length > 1) {
                setIndex((i) => (i + 1) % images.length);
            }
            if (e.key === 'ArrowLeft' && images.length > 1) {
                setIndex((i) => (i - 1 + images.length) % images.length);
            }
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [isOpen, images.length, onClose]);

    if (!isOpen || images.length === 0) return null;

    const currentSrc = images[index] || images[0];
    const reactionList = Object.values(HEIRLOOM_REACTIONS).filter(
        (r, idx, arr) => arr.findIndex((x) => x.key === r.key) === idx
    );

    const handleSubmitMediaComment = async (e) => {
        e.preventDefault();
        const text = commentDraft.trim();
        if (!text || !post?.post_no || submitting) return;
        setSubmitting(true);
        const prefix = images.length > 1 ? `[Photo ${index + 1}] ` : '';
        const fullText = `${prefix}${text}`;
        const prevComments = Array.isArray(post.comments) ? [...post.comments] : [];
        const tempId = `temp_${Date.now()}`;

        const optimisticComment = {
            comment_no: tempId,
            post_no: String(post.post_no),
            id: String(bootstrap?.userId || ''),
            firstName: bootstrap?.firstName || 'You',
            lastName: bootstrap?.lastName || '',
            comment: fullText,
            reactions: [],
            date_created: new Date().toISOString(),
            _optimistic: true,
        };

        onMutateComments?.(post.post_no, [...prevComments, optimisticComment]);
        setCommentDraft('');

        try {
            const fd = new FormData();
            fd.append('post_no', String(post.post_no));
            fd.append('comment', fullText);
            const res = await apiFetch('/postCommentProfile', { method: 'POST', body: fd }, bootstrap);
            const serverComment = res?.message && typeof res.message === 'object' ? res.message : null;
            if (serverComment) {
                onMutateComments?.(
                    post.post_no,
                    [
                        ...prevComments,
                        {
                            ...optimisticComment,
                            ...serverComment,
                            comment_no: String(serverComment.comment_no || tempId),
                            _optimistic: false,
                        },
                    ]
                );
            }
        } catch (err) {
            onMutateComments?.(post.post_no, prevComments);
            setCommentDraft(text);
            notifyToast(err?.message || 'Could not post photo comment.', 'error');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <Portal>
        <div
            className={`${styles.modalBackdrop} lightbox-overlay`}
            style={{ position: 'fixed', zIndex: 99999 }}
            role="dialog"
            aria-modal="true"
            aria-label="Photo lightbox"
            onClick={(e) => {
                if (e.target === e.currentTarget) onClose?.();
            }}
        >
            <div className={styles.modalDialog}>
                <div className={styles.modalHeader}>
                    <span className="fw-bold">
                        Photo {index + 1} of {images.length}
                    </span>
                    <button
                        type="button"
                        className={styles.btnBase}
                        onClick={onClose}
                        aria-label="Close lightbox"
                        data-testid="close-photo-view"
                        title="Close photo view"
                    >
                        Close
                    </button>
                </div>

                <div
                    className={styles.modalBody}
                    onTouchStart={(e) => {
                        touchStartX.current = e.touches?.[0]?.clientX ?? null;
                    }}
                    onTouchEnd={(e) => {
                        if (touchStartX.current === null) return;
                        const endX = e.changedTouches?.[0]?.clientX ?? touchStartX.current;
                        const delta = endX - touchStartX.current;
                        if (delta < -40 && images.length > 1) {
                            setIndex((i) => (i + 1) % images.length);
                        } else if (delta > 40 && images.length > 1) {
                            setIndex((i) => (i - 1 + images.length) % images.length);
                        }
                        touchStartX.current = null;
                    }}
                >
                    {animateHere ? (
                        <div className="d-flex flex-column align-items-center gap-2 w-100">
                            {animStatus === 'error' ? (
                                <div className="d-flex flex-column align-items-center gap-2 text-center">
                                    <img
                                        src={currentSrc}
                                        alt={`Photo ${index + 1}`}
                                        style={{ maxWidth: '100%', maxHeight: '46vh', objectFit: 'contain', borderRadius: 10 }}
                                    />
                                    <div className="small text-warning fw-semibold">
                                        Could not initialise live canvas animation for this photo.
                                    </div>
                                </div>
                            ) : (
                                <div
                                    ref={liveViewportRef}
                                    style={{
                                        width: 280,
                                        height: 280,
                                        borderRadius: '50%',
                                        position: 'relative',
                                        cursor: 'grab',
                                        background: '#0f172a',
                                        border: '2px solid rgba(251, 191, 36, 0.45)',
                                        overflow: 'hidden',
                                    }}
                                    title="Tap to react · drag to tilt"
                                >
                                    <canvas ref={liveCanvasRef} width={512} height={512} style={{ width: '100%', height: '100%', borderRadius: '50%', display: 'block' }} />
                                    {animStatus === 'loading' && (
                                        <div
                                            className="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center gap-2"
                                            style={{ background: 'rgba(15, 23, 42, 0.78)', color: '#ffffff' }}
                                        >
                                            <span className="spinner-border spinner-border-sm text-warning" role="status" aria-hidden="true" />
                                            <span className="small fw-semibold">Animating…</span>
                                        </div>
                                    )}
                                </div>
                            )}
                            <div className="d-flex flex-wrap justify-content-center gap-2">
                                {[
                                    { key: 'laugh', label: '😂 Laugh' },
                                    { key: 'dance', label: '💃 Dance' },
                                    { key: 'love', label: '❤️ Love' },
                                    { key: 'hello', label: '👋 Wave' },
                                ].map((act) => (
                                    <button
                                        key={act.key}
                                        type="button"
                                        className="btn btn-sm btn-outline-light rounded-pill px-3"
                                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                        disabled={animStatus !== 'ready'}
                                        onClick={() => liveStageRef.current?.triggerReaction(act.key, { playSound: true })}
                                    >
                                        {act.label}
                                    </button>
                                ))}
                                <button
                                    type="button"
                                    className="btn btn-sm btn-success rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1"
                                    style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                    data-spin="true"
                                    data-testid="share-animation-to-feed-btn"
                                    disabled={postFeedBusy || exportBusy || animStatus !== 'ready'}
                                    onClick={async () => {
                                        if (!liveStageRef.current || postFeedBusy || exportBusy) return;
                                        setPostFeedBusy(true);
                                        try {
                                            const captured = await liveStageRef.current.recordAnimatedVideoFile();
                                            if (!captured || !captured.file) {
                                                throw new Error('Unable to record animated clip.');
                                            }
                                            const csrf = getCsrfToken(bootstrap);
                                            const uploadFd = new FormData();
                                            uploadFd.append('video_file', captured.file, captured.file.name);
                                            uploadFd.append('trim_start', '0');
                                            uploadFd.append('trim_end', '3.2');
                                            uploadFd.append('client_duration', '3.2');
                                            if (csrf) {
                                                uploadFd.append('token', csrf);
                                                uploadFd.append('csrf_token', csrf);
                                            }
                                            const uploadRes = await apiFetch(
                                                '/api/video/upload-file',
                                                { method: 'POST', body: uploadFd, timeoutMs: 45000 },
                                                bootstrap
                                            );
                                            const videoUrl = uploadRes?.streamUrl || uploadRes?.rawUrl;
                                            if (!videoUrl) {
                                                throw new Error(uploadRes?.error || 'Could not upload animated video.');
                                            }

                                            const authorLabel = post?.fullName && post.fullName !== 'Family Historian'
                                                ? ` from ${post.fullName}'s memory`
                                                : '';
                                            const caption = `🎬 Animated a cherished family moment${authorLabel} (${captured.label || 'Living Motion'}) ✨`;
                                            const postFd = new FormData();
                                            postFd.append('postMessage', caption);
                                            postFd.append('attached_video_url', videoUrl);
                                            postFd.append('attached_video_urls', JSON.stringify([videoUrl]));
                                            const targetFamCode = post?.postFamCode || bootstrap?.famCode || '';
                                            if (targetFamCode) {
                                                postFd.append('postFamCode', String(targetFamCode));
                                            }
                                            if (csrf) {
                                                postFd.append('token', csrf);
                                                postFd.append('csrf_token', csrf);
                                            }
                                            const postRes = await apiFetch(
                                                '/member/profilePage/post',
                                                { method: 'POST', body: postFd, timeoutMs: 45000 },
                                                bootstrap
                                            );
                                            const newPostData = postRes?.message && typeof postRes.message === 'object'
                                                ? postRes.message
                                                : null;
                                            if (typeof window !== 'undefined' && newPostData) {
                                                window.dispatchEvent(new CustomEvent('post-created', { detail: newPostData }));
                                            }
                                            notifyToast('Animated memory shared to your family feed!', 'success');
                                            onClose?.();
                                        } catch (err) {
                                            notifyToast(err?.message || 'Could not share animated video to the feed.', 'error');
                                        } finally {
                                            setPostFeedBusy(false);
                                        }
                                    }}
                                >
                                    {postFeedBusy ? (
                                        <>
                                            <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
                                            <span>Posting to Feed…</span>
                                        </>
                                    ) : (
                                        <span>✨ Share to Feed</span>
                                    )}
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-sm btn-warning rounded-pill px-3 fw-semibold"
                                    style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                    disabled={exportBusy || postFeedBusy || animStatus !== 'ready'}
                                    onClick={async () => {
                                        if (!liveStageRef.current || exportBusy || postFeedBusy) return;
                                        setExportBusy(true);
                                        try {
                                            await liveStageRef.current.downloadAnimatedVideo();
                                        } catch {
                                            notifyToast('Video export is unavailable for this photo.', 'error');
                                        } finally {
                                            setExportBusy(false);
                                        }
                                    }}
                                >
                                    {exportBusy ? 'Recording…' : '⬇️ Download MP4'}
                                </button>
                                <button
                                    type="button"
                                    className="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold"
                                    style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                    disabled={exportBusy || postFeedBusy || animStatus !== 'ready'}
                                    onClick={async () => {
                                        if (!liveStageRef.current || exportBusy || postFeedBusy) return;
                                        setExportBusy(true);
                                        try {
                                            await liveStageRef.current.shareAnimatedVideo();
                                        } catch {
                                            notifyToast('Could not share animated video.', 'error');
                                        } finally {
                                            setExportBusy(false);
                                        }
                                    }}
                                >
                                    📤 Share
                                </button>
                            </div>
                        </div>
                    ) : (
                        <img
                            src={currentSrc}
                            alt={`Photo ${index + 1}`}
                            style={{ maxWidth: '100%', maxHeight: '58vh', objectFit: 'contain', borderRadius: 10 }}
                        />
                    )}

                    {images.length > 1 && (
                        <div className="d-flex align-items-center justify-content-center gap-3">
                            <button
                                type="button"
                                className={styles.btnBase}
                                onClick={() => setIndex((i) => (i - 1 + images.length) % images.length)}
                            >
                                Previous
                            </button>
                            <button
                                type="button"
                                className={styles.btnBase}
                                onClick={() => setIndex((i) => (i + 1) % images.length)}
                            >
                                Next
                            </button>
                        </div>
                    )}

                    {post && (
                        <div className="w-100 d-flex flex-column gap-2 mt-2" style={{ maxWidth: 680 }}>
                            <div className="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div className="d-flex align-items-center flex-wrap gap-2" role="group" aria-label="Media reactions">
                                    {reactionList.map((r) => (
                                        <button
                                            key={r.key}
                                            type="button"
                                            className={`${styles.reactionChip} ${post.user_reaction === r.key ? styles.reactionChipActive : ''}`}
                                            style={{ background: '#1e293b', color: '#f8fafc', borderColor: post.user_reaction === r.key ? '#f43f5e' : '#334155', minHeight: '44px' }}
                                            onClick={() => onReactPost?.(post.post_no, r.key)}
                                        >
                                            <span dangerouslySetInnerHTML={{ __html: getHeirloomGlassSvg(r.key, 24) }} />
                                            <span>{r.label}</span>
                                        </button>
                                    ))}
                                </div>
                                <div className="d-flex align-items-center gap-2 flex-wrap">
                                    <button
                                        type="button"
                                        className={`btn btn-sm rounded-pill d-inline-flex align-items-center gap-1 ${animateHere ? 'btn-warning text-dark fw-semibold' : 'btn-outline-light'}`}
                                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                        aria-pressed={animateHere}
                                        onClick={() => setAnimateHere((v) => !v)}
                                        title="Animate this photo right here with 60fps mesh motion and video export"
                                    >
                                        <span aria-hidden="true">🎬</span>
                                        <span>{animateHere ? 'View Original' : 'Animate Here'}</span>
                                    </button>
                                    <a
                                        href={`/member/emojiStudio?tab=photo-avatar&photo_url=${encodeURIComponent(currentSrc)}`}
                                        className="btn btn-sm btn-outline-light rounded-pill d-inline-flex align-items-center gap-1"
                                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                        title="Transform this photo into a Stylised Living Avatar"
                                    >
                                        <span aria-hidden="true">✨</span>
                                        <span>Living Avatar</span>
                                    </a>
                                </div>
                            </div>

                            <div className="d-flex align-items-center flex-wrap gap-1" aria-label="Quick emojis">
                                {QUICK_MEDIA_EMOJIS.map((em) => (
                                    <button
                                        key={em}
                                        type="button"
                                        className="btn btn-sm btn-dark border-secondary rounded-pill"
                                        style={{ minWidth: '44px', minHeight: '44px', fontSize: '1.2rem' }}
                                        aria-label={`Insert ${em}`}
                                        onClick={() => setCommentDraft((prev) => `${prev}${em}`)}
                                    >
                                        {em}
                                    </button>
                                ))}
                            </div>

                            <form onSubmit={handleSubmitMediaComment} className="d-flex gap-2">
                                <input type="hidden" name="token" value={getCsrfToken(bootstrap)} />
                                <input
                                    type="text"
                                    className="form-control rounded-pill px-3 flex-grow-1"
                                    style={{ minHeight: 44 }}
                                    placeholder="Comment on this photo…"
                                    value={commentDraft}
                                    onInput={(e) => setCommentDraft(e.target.value)}
                                    aria-label="Comment on this photo"
                                />
                                <button
                                    type="submit"
                                    className={`${styles.btnBase} ${styles.btnPrimary}`}
                                    disabled={submitting || !commentDraft.trim()}
                                >
                                    {submitting ? 'Sending…' : 'Send'}
                                </button>
                            </form>
                        </div>
                    )}
                </div>
            </div>
        </div>
        </Portal>
    );
}
