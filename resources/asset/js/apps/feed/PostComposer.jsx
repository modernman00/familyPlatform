import { useState, useRef, useEffect } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { getCsrfToken, notifyToast } from '../shared/bootstrap';
import {
    TEXT_STAGE_CATALOG,
    suggestTextStageForMessage,
    extractPreviewableUrls,
    getAvatarUrl,
    getInitials,
    normalizePost,
} from './feedUtils';
import styles from './FeedShell.module.css';
import CosmicOrrery from './CosmicOrrery';
import ScenicMotionLayer, { LIVING_SCENIC_SLUGS } from './ScenicMotionLayer';

const QUICK_EMOJIS = ['❤️', '🎉', '🙏', '😂', '🏡', '✨'];

export function PostComposer({ bootstrap, onOptimisticInsert, onReplaceOptimistic, onRollbackOptimistic, composerRef }) {
    const [message, setMessage] = useState('');
    const [selectedStyle, setSelectedStyle] = useState('');
    const [dismissedSuggestionSlug, setDismissedSuggestionSlug] = useState('');
    const [files, setFiles] = useState([]);
    const [videoUrl, setVideoUrl] = useState('');
    const [showVideoInput, setShowVideoInput] = useState(false);
    const [showPollBuilder, setShowPollBuilder] = useState(false);
    const [pollOptions, setPollOptions] = useState(['', '']);
    const [audienceScope, setAudienceScope] = useState('all');
    const [allowSharing, setAllowSharing] = useState(true);
    const [showOptions, setShowOptions] = useState(false);
    const [isExpanded, setIsExpanded] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [brokenAvatar, setBrokenAvatar] = useState(false);
    const [linkPreview, setLinkPreview] = useState(null);
    const [dismissedLinkUrl, setDismissedLinkUrl] = useState('');
    const fileInputRef = useRef(null);

    useEffect(() => {
        const urls = extractPreviewableUrls(message, 1);
        const firstUrl = urls[0] || '';
        if (!firstUrl) {
            setLinkPreview(null);
            return;
        }
        if (firstUrl === dismissedLinkUrl) return;

        let cancelled = false;
        const timer = setTimeout(async () => {
            try {
                const res = await apiFetch(`/api/link-preview?url=${encodeURIComponent(firstUrl)}`, {
                    method: 'GET',
                    timeoutMs: 12000,
                }, bootstrap);
                if (cancelled) return;
                const d = res?.status === 'success' && res?.data ? res.data : (res?.message?.data || null);
                if (d && (d.title || d.image || d.domain)) {
                    setLinkPreview({ ...d, _sourceUrl: firstUrl });
                } else {
                    setLinkPreview(null);
                }
            } catch (_) {
                if (!cancelled) setLinkPreview(null);
            }
        }, 280);

        return () => {
            cancelled = true;
            clearTimeout(timer);
        };
    }, [message, dismissedLinkUrl, bootstrap]);

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
            allow_sharing: allowSharing,
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
        const prevAllowSharing = allowSharing;

        setMessage('');
        setSelectedStyle('');
        setFiles([]);
        setVideoUrl('');
        setShowVideoInput(false);
        setShowPollBuilder(false);
        setShowOptions(false);
        setIsExpanded(false);
        setPollOptions(['', '']);
        setAllowSharing(true);
        setLinkPreview(null);
        setDismissedLinkUrl('');

        try {
            const formData = new FormData();
            formData.append('postMessage', cleanMsg);
            formData.append('allow_sharing', prevAllowSharing ? '1' : '0');
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
    const isTextOnlyPost = files.length === 0 && !videoUrl.trim() && !showPollBuilder && !linkPreview;
    const rawSuggestion = isTextOnlyPost ? suggestTextStageForMessage(message) : null;
    const activeSuggestion =
        rawSuggestion && rawSuggestion.slug !== selectedStyle && rawSuggestion.slug !== dismissedSuggestionSlug
            ? rawSuggestion
            : null;

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
                    <div
                        className={`flex-grow-1 min-w-0 ${
                            selectedStyle && files.length === 0
                                ? `fp-text-stage fp-text-stage--composer fp-text-stage--${selectedStyle}${
                                      TEXT_STAGE_CATALOG.find((s) => s.slug === selectedStyle)?.category === 'scenic'
                                          ? ' fp-text-stage--scenic'
                                          : ''
                                  }`
                                : ''
                        }`}
                    >
                        {selectedStyle && files.length === 0 && (
                            <div className="fp-text-stage__bokeh" aria-hidden="true" />
                        )}
                        {['solar_galaxy', 'andromeda_nebula', 'binary_eclipse', 'supernova_crown', 'cosmic_rings'].includes(selectedStyle) && files.length === 0 && (
                            <CosmicOrrery variant={selectedStyle} />
                        )}
                        {LIVING_SCENIC_SLUGS.includes(selectedStyle) && files.length === 0 && (
                            <ScenicMotionLayer variant={selectedStyle} />
                        )}
                        <textarea
                            ref={composerRef}
                            rows={hasDraft || selectedStyle ? 3 : 1}
                            className={`${styles.composerTextarea} ${hasDraft || selectedStyle ? styles.composerTextareaExpanded : ''}`}
                            style={
                                selectedStyle && files.length === 0
                                    ? {
                                          background: 'transparent',
                                          border: 'none',
                                          color: '#ffffff',
                                          fontWeight: 700,
                                          fontSize: '1.2rem',
                                          textAlign: 'center',
                                          position: 'relative',
                                          zIndex: 2,
                                      }
                                    : undefined
                            }
                            placeholder="Share a moment with your family..."
                            value={message}
                            onFocus={() => setIsExpanded(true)}
                            onInput={(e) => setMessage(e.target.value)}
                            aria-label="Share a family moment"
                        />
                    </div>
                </div>

                {/* Live Text-Only Theme Suggestion (Requires Explicit User Approval) */}
                {activeSuggestion && (
                    <div
                        className="mt-2 px-3 py-2 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2"
                        style={{
                            background: 'rgba(15, 23, 42, 0.04)',
                            border: '1px solid rgba(99, 102, 241, 0.28)',
                        }}
                        role="status"
                        aria-live="polite"
                        data-testid="composer-theme-suggestion"
                    >
                        <div className="d-flex align-items-center gap-2 min-w-0">
                            <span
                                className="fp-stage-converter__dot flex-shrink-0"
                                style={{ background: activeSuggestion.swatch }}
                                aria-hidden="true"
                            />
                            <span className="small fw-semibold">
                                Suggested theme ({activeSuggestion.reason}): <strong>{activeSuggestion.label}</strong>
                            </span>
                        </div>
                        <div className="d-flex align-items-center gap-2 flex-shrink-0">
                            <button
                                type="button"
                                className="btn btn-sm btn-primary rounded-pill fw-semibold px-3"
                                style={{ minHeight: '44px', whiteSpace: 'nowrap' }}
                                data-testid="composer-theme-approve-btn"
                                onClick={() => {
                                    setSelectedStyle(activeSuggestion.slug);
                                    setIsExpanded(true);
                                }}
                            >
                                Apply {activeSuggestion.label}
                            </button>
                            <button
                                type="button"
                                className="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                style={{ minHeight: '44px', whiteSpace: 'nowrap' }}
                                data-testid="composer-theme-dismiss-btn"
                                onClick={() => setDismissedSuggestionSlug(activeSuggestion.slug)}
                            >
                                Dismiss
                            </button>
                        </div>
                    </div>
                )}

                {/* Live Link Preview Card */}
                {linkPreview && (
                    <div className="mt-2 position-relative rounded-3 overflow-hidden shadow-sm link-preview-card" data-testid="composer-link-preview">
                        <button
                            type="button"
                            className="btn btn-sm btn-dark bg-opacity-75 text-white position-absolute top-0 end-0 m-2 rounded-circle d-inline-flex align-items-center justify-content-center"
                            style={{ width: '44px', height: '44px', zIndex: 3 }}
                            aria-label="Dismiss link preview"
                            title="Dismiss link preview"
                            onClick={() => {
                                setDismissedLinkUrl(linkPreview._sourceUrl || '');
                                setLinkPreview(null);
                            }}
                        >
                            <i className="bi bi-x-lg" aria-hidden="true" />
                        </button>
                        {linkPreview.image && (
                            <div className="link-preview-card__media">
                                <img
                                    src={linkPreview.image}
                                    alt={linkPreview.title || 'Link preview image'}
                                    referrerPolicy="no-referrer"
                                    loading="lazy"
                                    className="w-100 h-100 object-fit-cover"
                                />
                            </div>
                        )}
                        <div className="p-3 link-preview-card__body">
                            <div className="d-flex justify-content-between align-items-center mb-1 link-preview-card__eyebrow">
                                <span className="badge rounded-pill text-bg-light border small">{linkPreview.domain || 'Link'}</span>
                                <small className="fw-medium link-preview-card__type">Rich Preview</small>
                            </div>
                            <h6 className="fw-bold mb-1 link-preview-card__title">{linkPreview.title || linkPreview.domain || ''}</h6>
                            {linkPreview.description && (
                                <p className="small mb-2 link-preview-card__desc">{linkPreview.description}</p>
                            )}
                            <div className="pt-1 d-flex justify-content-between align-items-center link-preview-card__footer">
                                <span className="small text-truncate link-preview-card__url" style={{ maxWidth: '65%' }}>
                                    {linkPreview.site_name || linkPreview.domain || 'Web Link'}
                                </span>
                                <span className="badge rounded-pill px-2 py-1 link-preview-card__cta">
                                    {linkPreview.cta_label || 'Open Link →'}
                                </span>
                            </div>
                        </div>
                    </div>
                )}

                {/* Expandable Emoji, Scenic Backdrop & Audience Controls */}
                {(showOptions || selectedStyle) && (
                    <div className="d-flex flex-column gap-2 mt-2">
                        <div className="fp-stage-converter__row" role="radiogroup" aria-label="Choose post backdrop">
                            <button
                                type="button"
                                className={`fp-stage-converter__chip ${!selectedStyle ? 'is-active' : ''}`}
                                onClick={() => setSelectedStyle('')}
                                aria-pressed={!selectedStyle ? 'true' : 'false'}
                            >
                                <span>Plain (Aa)</span>
                            </button>
                            {TEXT_STAGE_CATALOG.map((st) => (
                                <button
                                    key={st.slug}
                                    type="button"
                                    className={`fp-stage-converter__chip ${selectedStyle === st.slug ? 'is-active' : ''}`}
                                    onClick={() => setSelectedStyle(st.slug)}
                                    aria-pressed={selectedStyle === st.slug ? 'true' : 'false'}
                                >
                                    {st.swatch && (
                                        <span
                                            className="fp-stage-converter__dot"
                                            style={{ background: st.swatch }}
                                            aria-hidden="true"
                                        />
                                    )}
                                    <span>{st.label}</span>
                                </button>
                            ))}
                        </div>
                        <div className="d-flex align-items-center flex-wrap gap-2">
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
                                <optgroup label="Scenic Backdrops">
                                    {TEXT_STAGE_CATALOG.filter((s) => s.category === 'scenic').map((st) => (
                                        <option key={st.slug} value={st.slug}>
                                            {st.label} Backdrop
                                        </option>
                                    ))}
                                </optgroup>
                                <optgroup label="3D Milestone Stages">
                                    {TEXT_STAGE_CATALOG.filter((s) => s.category !== 'scenic').map((st) => (
                                        <option key={st.slug} value={st.slug}>
                                            {st.label} Stage
                                        </option>
                                    ))}
                                </optgroup>
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

                            <button
                                type="button"
                                className={`btn btn-sm rounded-pill d-inline-flex align-items-center gap-1 px-3 fw-semibold ${allowSharing ? 'btn-outline-success' : 'btn-outline-secondary'}`}
                                style={{ minHeight: 44, whiteSpace: 'nowrap', flexShrink: 0 }}
                                aria-pressed={allowSharing ? 'true' : 'false'}
                                data-testid="composer-allow-sharing-toggle"
                                title={allowSharing ? 'Relatives can share this post and you will be notified via Web Push' : 'Sharing locked — only you can share this post'}
                                onClick={() => setAllowSharing((v) => !v)}
                            >
                                <i className={`bi ${allowSharing ? 'bi-share-fill' : 'bi-lock-fill'}`} aria-hidden="true" />
                                <span>{allowSharing ? 'Sharing Allowed' : 'Sharing Locked'}</span>
                            </button>
                        </div>
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
