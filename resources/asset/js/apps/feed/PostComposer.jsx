import { useState, useRef } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { getCsrfToken, notifyToast } from '../shared/bootstrap';
import {
    TEXT_STAGE_CATALOG,
    getAvatarUrl,
    getInitials,
    normalizePost,
} from './feedUtils';
import styles from './FeedShell.module.css';

const QUICK_EMOJIS = ['❤️', '🎉', '🙏', '😂', '🏡', '✨'];

export function PostComposer({ bootstrap, onOptimisticInsert, onReplaceOptimistic, onRollbackOptimistic, composerRef }) {
    const [message, setMessage] = useState('');
    const [selectedStyle, setSelectedStyle] = useState('');
    const [files, setFiles] = useState([]);
    const [videoUrl, setVideoUrl] = useState('');
    const [showVideoInput, setShowVideoInput] = useState(false);
    const [showPollBuilder, setShowPollBuilder] = useState(false);
    const [pollOptions, setPollOptions] = useState(['', '']);
    const [audienceScope, setAudienceScope] = useState('all');
    const [showOptions, setShowOptions] = useState(false);
    const [isExpanded, setIsExpanded] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [brokenAvatar, setBrokenAvatar] = useState(false);
    const fileInputRef = useRef(null);

    const audiences = Array.isArray(bootstrap?.postAudiences) ? bootstrap.postAudiences : [];
    const authorName = `${bootstrap?.firstName || 'Family'} ${bootstrap?.lastName || 'Member'}`.trim();

    const handleFileSelect = (e) => {
        const picked = Array.from(e.target.files || []).slice(0, 6);
        setFiles((prev) => [...prev, ...picked].slice(0, 6));
        if (fileInputRef.current) fileInputRef.current.value = '';
    };

    const removeFile = (idx) => {
        setFiles((prev) => prev.filter((_, i) => i !== idx));
    };

    const updatePollOption = (idx, val) => {
        setPollOptions((prev) => prev.map((opt, i) => (i === idx ? val : opt)));
    };

    const addPollOption = () => {
        if (pollOptions.length < 6) setPollOptions((prev) => [...prev, '']);
    };

    const canSubmit = Boolean(
        message.trim() ||
        files.length > 0 ||
        videoUrl.trim() ||
        (showPollBuilder && pollOptions.filter((o) => o.trim()).length >= 2)
    );

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!canSubmit || submitting) return;

        const cleanMsg = message.trim();
        const validPollOpts = showPollBuilder ? pollOptions.map((o) => o.trim()).filter(Boolean) : [];
        const tempPostNo = `temp_post_${Date.now()}`;

        const optimisticPost = normalizePost({
            post_no: tempPostNo,
            id: String(bootstrap?.userId || ''),
            firstName: bootstrap?.firstName || 'You',
            lastName: bootstrap?.lastName || '',
            profilePics: bootstrap?.avatar || '',
            postMessage: cleanMsg,
            post_style: selectedStyle || null,
            images: files.map((f) => URL.createObjectURL(f)),
            videos: videoUrl.trim() ? [videoUrl.trim()] : [],
            post_likes: 0,
            comments: [],
            date_created: new Date().toISOString(),
            _optimistic: true,
        });

        onOptimisticInsert?.(optimisticPost);
        setSubmitting(true);

        const prevMessage = message;
        const prevStyle = selectedStyle;
        const prevFiles = files;
        const prevVideoUrl = videoUrl;
        const prevPollOpts = pollOptions;

        setMessage('');
        setSelectedStyle('');
        setFiles([]);
        setVideoUrl('');
        setShowVideoInput(false);
        setShowPollBuilder(false);
        setShowOptions(false);
        setIsExpanded(false);
        setPollOptions(['', '']);

        try {
            const formData = new FormData();
            formData.append('postMessage', cleanMsg);
            if (prevStyle) formData.append('post_style', prevStyle);
            if (audienceScope && audienceScope !== 'all') {
                formData.append('target_scope', audienceScope);
            }
            prevFiles.forEach((file) => {
                formData.append('post_img[]', file);
            });
            if (prevVideoUrl.trim()) {
                formData.append('attached_video_urls', JSON.stringify([prevVideoUrl.trim()]));
            }
            if (validPollOpts.length >= 2) {
                formData.append('poll_options', JSON.stringify(validPollOpts));
            }

            const res = await apiFetch('/member/profilePage/post', {
                method: 'POST',
                body: formData,
                timeoutMs: 60000,
            }, bootstrap);

            const hydrated = res?.message && typeof res.message === 'object' ? normalizePost(res.message) : null;
            if (hydrated && hydrated.post_no) {
                onReplaceOptimistic?.(tempPostNo, {
                    ...hydrated,
                    firstName: hydrated.firstName || optimisticPost.firstName,
                    lastName: hydrated.lastName || optimisticPost.lastName,
                    profilePics: hydrated.profilePics || optimisticPost.profilePics,
                });
            } else {
                onReplaceOptimistic?.(tempPostNo, { ...optimisticPost, _optimistic: false });
            }
            notifyToast('Shared with your family!', 'success');
        } catch (err) {
            onRollbackOptimistic?.(tempPostNo);
            setMessage(prevMessage);
            setSelectedStyle(prevStyle);
            setFiles(prevFiles);
            setVideoUrl(prevVideoUrl);
            setPollOptions(prevPollOpts);
            notifyToast(err?.message || 'Could not share your post. Please try again.', 'error');
        } finally {
            setSubmitting(false);
        }
    };

    const hasDraft = Boolean(
        isExpanded ||
        message.trim() ||
        files.length > 0 ||
        showVideoInput ||
        showPollBuilder ||
        showOptions
    );

    return (
        <section className={`${styles.composerCard} post-composer post-composer--hero mb-3`} aria-label="Create a family post">
            <div className={styles.composerAccent} aria-hidden="true" />
            <form onSubmit={handleSubmit}>
                <input type="hidden" name="token" value={getCsrfToken(bootstrap)} />

                {/* Classic Hero Header */}
                <div className={styles.composerHeader}>
                    <div className="d-flex align-items-center gap-2 min-w-0">
                        <span className={styles.composerBadge} aria-hidden="true">
                            <i className="bi bi-chat-heart-fill" />
                        </span>
                        <div className="min-w-0">
                            <div className={styles.composerTitle}>Share a Moment</div>
                            <div className={`${styles.composerSubtitle} d-none d-sm-block`}>
                                Photos, milestones, stories &amp; polls for your family
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        className={`${styles.btnBase} ${styles.btnPrimary}`}
                        disabled={!canSubmit || submitting}
                        aria-label="Post"
                    >
                        {submitting ? (
                            <span className="st-spinner-border" role="status" aria-hidden="true" />
                        ) : (
                            <i className="bi bi-plus-lg" aria-hidden="true" />
                        )}
                        <span>{submitting ? 'Sharing…' : 'Post'}</span>
                    </button>
                </div>

                {/* Classic Avatar + Prompt Row */}
                <div className={styles.composerRow}>
                    {brokenAvatar ? (
                        <div className={styles.avatarFallback}>{getInitials(bootstrap?.firstName, bootstrap?.lastName)}</div>
                    ) : (
                        <img
                            src={getAvatarUrl(bootstrap)}
                            alt={authorName}
                            className={styles.avatar}
                            onError={() => setBrokenAvatar(true)}
                        />
                    )}
                    <div className="flex-grow-1 min-w-0">
                        <textarea
                            ref={composerRef}
                            rows={hasDraft ? 3 : 1}
                            className={`${styles.composerTextarea} ${hasDraft ? styles.composerTextareaExpanded : ''}`}
                            placeholder="Share a moment with your family..."
                            value={message}
                            onFocus={() => setIsExpanded(true)}
                            onInput={(e) => setMessage(e.target.value)}
                            aria-label="Share a family moment"
                        />
                    </div>
                </div>

                {/* Expandable Emoji, 3D Stage & Audience Controls */}
                {(showOptions || selectedStyle) && (
                    <div className="d-flex align-items-center flex-wrap gap-2 mt-2">
                        {QUICK_EMOJIS.map((em) => (
                            <button
                                key={em}
                                type="button"
                                className={styles.btnBase}
                                style={{ padding: '0.25rem 0.625rem' }}
                                onClick={() => setMessage((m) => `${m}${em}`)}
                                aria-label={`Insert ${em}`}
                            >
                                {em}
                            </button>
                        ))}
                        <select
                            className="form-select"
                            style={{ width: 'auto', minHeight: 44 }}
                            aria-label="Choose 3D text stage"
                            value={selectedStyle}
                            onChange={(e) => setSelectedStyle(e.target.value)}
                        >
                            <option value="">Standard Card</option>
                            {TEXT_STAGE_CATALOG.map((st) => (
                                <option key={st.slug} value={st.slug}>
                                    {st.label} Stage
                                </option>
                            ))}
                        </select>

                        {audiences.length > 0 && (
                            <select
                                className="form-select"
                                style={{ width: 'auto', minHeight: 44 }}
                                aria-label="Post audience"
                                value={audienceScope}
                                onChange={(e) => setAudienceScope(e.target.value)}
                            >
                                <option value="all">All Family Circles</option>
                                {audiences.map((aud) => (
                                    <option key={aud.scope || aud.code} value={aud.scope || aud.code}>
                                        {aud.label || aud.name || aud.code}
                                    </option>
                                ))}
                            </select>
                        )}
                    </div>
                )}

                {/* Attached Images Preview */}
                {files.length > 0 && (
                    <div className="d-flex flex-wrap gap-2 mt-2">
                        {files.map((f, idx) => (
                            <div key={`${f.name}_${idx}`} className="d-flex align-items-center gap-2 px-2 py-1 rounded border">
                                <span className="small text-truncate" style={{ maxWidth: 160 }}>{f.name}</span>
                                <button
                                    type="button"
                                    className={styles.btnBase}
                                    style={{ minHeight: 44, padding: '0.25rem 0.5rem' }}
                                    onClick={() => removeFile(idx)}
                                >
                                    Remove
                                </button>
                            </div>
                        ))}
                    </div>
                )}

                {/* Video URL Input */}
                {showVideoInput && (
                    <div className="mt-2">
                        <input
                            type="url"
                            className="form-control"
                            style={{ minHeight: 44 }}
                            placeholder="Paste a video URL (YouTube, Vimeo, or MP4)…"
                            value={videoUrl}
                            onInput={(e) => setVideoUrl(e.target.value)}
                            aria-label="Video URL"
                        />
                    </div>
                )}

                {/* Poll Builder */}
                {showPollBuilder && (
                    <div className={`${styles.pollArena} mt-2`}>
                        <div className="fw-bold mb-2" style={{ fontSize: '0.88rem' }}>
                            Organise a Family Poll
                        </div>
                        <div className="d-flex flex-column gap-2">
                            {pollOptions.map((opt, idx) => (
                                <input
                                    key={idx}
                                    type="text"
                                    className="form-control"
                                    style={{ minHeight: 44 }}
                                    placeholder={`Option ${idx + 1}`}
                                    value={opt}
                                    onInput={(e) => updatePollOption(idx, e.target.value)}
                                    aria-label={`Poll option ${idx + 1}`}
                                />
                            ))}
                        </div>
                        {pollOptions.length < 6 && (
                            <button type="button" className={`${styles.btnBase} mt-2`} onClick={addPollOption}>
                                + Add Option
                            </button>
                        )}
                    </div>
                )}

                <div className={styles.composerActions}>
                    <div className={styles.actionGroup}>
                        <label className={styles.btnBase} style={{ margin: 0, cursor: 'pointer' }}>
                            <i className="bi bi-camera-fill text-success" aria-hidden="true" />
                            <span>Photo</span>
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/*"
                                multiple
                                style={{ display: 'none' }}
                                onChange={handleFileSelect}
                            />
                        </label>
                        <button
                            type="button"
                            className={styles.btnBase}
                            onClick={() => setShowVideoInput((v) => !v)}
                        >
                            <i className="bi bi-play-btn-fill text-danger" aria-hidden="true" />
                            <span>Video</span>
                        </button>
                        <button
                            type="button"
                            className={styles.btnBase}
                            onClick={() => setShowPollBuilder((v) => !v)}
                        >
                            <i className="bi bi-bar-chart-fill text-primary" aria-hidden="true" />
                            <span>Poll</span>
                        </button>
                        <button
                            type="button"
                            className={styles.btnBase}
                            onClick={() => setShowOptions((v) => !v)}
                            aria-expanded={showOptions ? 'true' : 'false'}
                        >
                            <i className="bi bi-palette-fill text-warning" aria-hidden="true" />
                            <span>Style &amp; Circle</span>
                        </button>
                    </div>

                    {hasDraft && (
                        <button
                            type="submit"
                            className={`${styles.btnBase} ${styles.btnPrimary}`}
                            disabled={!canSubmit || submitting}
                        >
                            {submitting && <span className="st-spinner-border" role="status" aria-hidden="true" />}
                            <span>{submitting ? 'Sharing…' : 'Share Post'}</span>
                        </button>
                    )}
                </div>
            </form>
        </section>
    );
}
