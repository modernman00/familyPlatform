import { useState } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { notifyToast } from '../shared/bootstrap';
import { HEIRLOOM_REACTIONS, getHeirloomGlassSvg } from '../../components/profilePage/heirloomGlassReactions';
import { CommentThread } from './CommentThread';
import { FeedVideoGallery } from './FeedVideoGallery';
import {
    formatRelativeTime,
    getAvatarUrl,
    getInitials,
    getMediaCaption,
    getPollArenaMeta,
    getPollCleanIntro,
    getPostImageUrl,
    getPostStageMeta,
    hasPollArena,
    isGifMedia,
    isOwnPost,
    stripStyleToken,
    tokenizeRichText,
} from './feedUtils';

const OPTION_LETTERS = ['A', 'B', 'C', 'D', 'E', 'F'];

export function PostCard({
    post,
    bootstrap,
    isHighlighted = false,
    onReactPost,
    onVotePoll,
    onMutateComments,
    onUpdatePost,
    onDeletePost,
    onSelectHashtag,
    onOpenLightbox,
    onOpenVideo,
}) {
    const currentUserId = String(bootstrap?.userId || '');
    const ownPost = isOwnPost(post, currentUserId);
    const [brokenAvatar, setBrokenAvatar] = useState(false);
    const [showReactionPicker, setShowReactionPicker] = useState(false);
    const [commentsExpanded, setCommentsExpanded] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [editDraft, setEditDraft] = useState('');
    const [savingEdit, setSavingEdit] = useState(false);
    const [confirmingDelete, setConfirmingDelete] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [votingPoll, setVotingPoll] = useState(false);
    const [savedMemory, setSavedMemory] = useState(() => {
        if (post._saved) return true;
        try {
            const list = JSON.parse(localStorage.getItem('fp_saved_memories') || '[]');
            return Array.isArray(list) && list.includes(String(post.post_no));
        } catch {
            return false;
        }
    });
    const [sliderPos, setSliderPos] = useState(50);
    const [brokenPreviewImgUrl, setBrokenPreviewImgUrl] = useState(null);
    const [brokenPhotoUrls, setBrokenPhotoUrls] = useState({});

    const authorName = post.fullName || `${post.firstName || ''} ${post.lastName || ''}`.trim() || 'Family Member';
    const stageMeta = getPostStageMeta(post);
    const pollMeta = hasPollArena(post) ? getPollArenaMeta(post) : null;
    const reactionList = Object.values(HEIRLOOM_REACTIONS).filter(
        (r, idx, arr) => arr.findIndex((x) => x.key === r.key) === idx
    );

    const isOfficial = Boolean(post.is_official || post.id === 'FAMILY_PLATFORM_OFFICIAL');
    const isHistorian = Boolean(post.is_historian || post.id === 'FAMILY_HISTORIAN');
    const isTaggedSelf = Array.isArray(post.post_tags)
        && post.post_tags.some((t) => String(t.user_id || t.id) === currentUserId);

    const images = Array.isArray(post.images) ? post.images : [];
    const videos = Array.isArray(post.videos) ? post.videos : [];
    const hasMedia = images.length > 0 || videos.length > 0 || Boolean(post.comparison);
    const hasLinkPreview = Boolean(
        post.link_preview && (post.link_preview.image || post.link_preview.title || post.link_preview.url)
    );
    const captionText = getMediaCaption(post) || (hasMedia || hasLinkPreview ? '' : stripStyleToken(post.postMessage || ''));

    const cardAccentStyle = {
        borderRadius: isOfficial || isHistorian || isTaggedSelf ? '12px' : '8px',
        backgroundColor: 'var(--card-bg, #ffffff)',
        ...(isOfficial
            ? { borderLeft: '4px solid #059669' }
            : isHistorian
                ? { borderLeft: '4px solid #4338ca' }
                : isTaggedSelf
                    ? { borderLeft: '4px solid #a31621' }
                    : {}),
    };

    const renderTokens = (rawText) =>
        tokenizeRichText(rawText).map((tok, i) => {
            if (tok.type === 'hashtag') {
                return (
                    <button
                        key={i}
                        type="button"
                        className="btn btn-link p-0 border-0 align-baseline fw-semibold text-decoration-underline"
                        style={{ color: stageMeta ? '#fde68a' : '#a31621' }}
                        onClick={() => onSelectHashtag?.(tok.tag)}
                    >
                        {tok.value}
                    </button>
                );
            }
            if (tok.type === 'url' || tok.type === 'link') {
                const isExternal = tok.value.startsWith('http://') || tok.value.startsWith('https://');
                return (
                    <a
                        key={i}
                        href={tok.value}
                        target={isExternal ? '_blank' : undefined}
                        rel={isExternal ? 'noopener noreferrer' : undefined}
                        className="fw-semibold text-decoration-underline"
                        style={{ color: stageMeta ? '#93c5fd' : '#a31621' }}
                    >
                        {tok.label || tok.value}
                    </a>
                );
            }
            if (tok.type === 'mention') {
                return <strong key={i}>{tok.value}</strong>;
            }
            return <span key={i}>{tok.value}</span>;
        });

    const handleEditClick = () => {
        const postNoInput = document.getElementById('editPostNo');
        const textarea = document.getElementById('postMessage');
        const editor = document.getElementById('postMessageEditor');
        const modalEl = document.getElementById('postModal');
        const notice = document.getElementById('editPostNotice');

        if (postNoInput && textarea && modalEl && window.bootstrap?.Modal) {
            const currentStyle = post?.post_style || '';
            const styleInput = document.getElementById('composerPostStyleInput');
            const cleanText = stripStyleToken(post.postMessage || '');
            const applyEditModalState = () => {
                if (styleInput) styleInput.value = currentStyle;
                if (typeof window.__setComposerTextStage === 'function') {
                    window.__setComposerTextStage(currentStyle);
                }
                postNoInput.value = String(post.post_no);
                textarea.value = cleanText;
                if (editor) {
                    editor.textContent = cleanText;
                    editor.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (notice) notice.classList.remove('d-none');
                const modalTitle = document.getElementById('postModalLabel');
                if (modalTitle) modalTitle.textContent = 'Edit Post';
            };
            applyEditModalState();
            modalEl.addEventListener('shown.bs.modal', applyEditModalState, { once: true });
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return;
        }

        setEditDraft(stripStyleToken(post.postMessage));
        setIsEditing(true);
    };

    const handleSaveEdit = async () => {
        const clean = editDraft.trim();
        if (!clean || savingEdit) return;
        setSavingEdit(true);
        const previousMsg = post.postMessage;
        const previousDisplay = post.displayMessage;
        onUpdatePost?.(post.post_no, { postMessage: clean, displayMessage: clean });
        try {
            const res = await apiFetch(`/post/${encodeURIComponent(post.post_no)}`, {
                method: 'PUT',
                body: { postMessage: clean },
                timeoutMs: 45000,
            }, bootstrap);
            if (res?.message && typeof res.message === 'object') {
                onUpdatePost?.(post.post_no, res.message);
            }
            setIsEditing(false);
            notifyToast('Post updated', 'success');
        } catch (err) {
            onUpdatePost?.(post.post_no, { postMessage: previousMsg, displayMessage: previousDisplay });
            notifyToast(err?.message || 'Could not update post.', 'error');
        } finally {
            setSavingEdit(false);
        }
    };

    const handleRepost = async () => {
        try {
            await apiFetch(`/post/${encodeURIComponent(post.post_no)}/repost`, {
                method: 'POST',
                body: { reflection_message: '' },
                timeoutMs: 30000,
            }, bootstrap);
            notifyToast('Reposted to your family feed', 'success');
        } catch (err) {
            notifyToast(err?.message || 'Unable to repost.', 'error');
        }
    };

    const handleConfirmDelete = async () => {
        if (deleting) return;
        setDeleting(true);
        try {
            await apiFetch(`/post/${encodeURIComponent(post.post_no)}`, {
                method: 'DELETE',
                timeoutMs: 45000,
            }, bootstrap);
            onDeletePost?.(post.post_no);
            notifyToast('Post deleted', 'success');
        } catch (err) {
            notifyToast(err?.message || 'Unable to delete post.', 'error');
        } finally {
            setDeleting(false);
            setConfirmingDelete(false);
        }
    };

    const handleUntagSelf = async () => {
        try {
            await apiFetch(`/api/posts/${encodeURIComponent(post.post_no)}/untag`, {
                method: 'POST',
            }, bootstrap);
            const filteredTags = (post.post_tags || []).filter(
                (t) => String(t.user_id || t.id) !== currentUserId
            );
            onUpdatePost?.(post.post_no, { post_tags: filteredTags });
            notifyToast('Tag removed from post', 'success');
        } catch (err) {
            notifyToast(err?.message || 'Could not remove tag.', 'error');
        }
    };

    const handleVoteOption = async (optionId) => {
        if (votingPoll) return;
        setVotingPoll(true);
        try {
            await onVotePoll?.(post.post_no, optionId);
        } finally {
            setVotingPoll(false);
        }
    };

    const handleShare = async () => {
        const shareUrl = `${window.location.origin}/member/profilePage#post-${post.post_no}`;
        const shareText = stripStyleToken(post.postMessage || '').slice(0, 140);
        if (typeof navigator !== 'undefined' && typeof navigator.share === 'function') {
            try {
                await navigator.share({ title: `${authorName} on FamilyPlatform`, text: shareText, url: shareUrl });
                return;
            } catch {
                // Fall back to clipboard
            }
        }
        if (typeof window !== 'undefined' && typeof window.$clipboard === 'function') {
            window.$clipboard(shareUrl, 'Post link copied!');
        } else if (typeof navigator !== 'undefined' && navigator.clipboard) {
            await navigator.clipboard.writeText(shareUrl).catch(() => {});
            notifyToast('Post link copied!', 'success');
        }
    };

    const pollOptions = post?.poll?.options || [];
    const totalPollVotes = Number(post?.poll?.total_votes)
        || pollOptions.reduce((sum, o) => sum + Number(o.vote_count || 0), 0);
    const votedIds = Array.isArray(post?.poll?.user_voted_option_id)
        ? post.poll.user_voted_option_id.map(String)
        : (post?.poll?.user_voted_option_id ? [String(post.poll.user_voted_option_id)] : []);
    let leadingOptionId = null;
    if (totalPollVotes > 0 && pollOptions.length > 0) {
        const maxVotes = Math.max(...pollOptions.map((o) => Number(o.vote_count || 0)));
        if (maxVotes > 0) {
            const leader = pollOptions.find((o) => Number(o.vote_count || 0) === maxVotes);
            if (leader) leadingOptionId = String(leader.option_id);
        }
    }

    const activeReactionMeta = post.user_reaction ? HEIRLOOM_REACTIONS[post.user_reaction] : null;
    const commentCount = Array.isArray(post.comments) ? post.comments.length : 0;
    const viewCount = Number(post.views_count ?? post.view_count ?? 0);
    const famBadgeStyle = post.target_scope === 'birth'
        ? { color: '#7f1d1d', backgroundColor: '#fee2e2', borderColor: '#fca5a5', fontSize: '0.78rem', fontWeight: 'normal', padding: '2px 8px', whiteSpace: 'nowrap', flexShrink: 0 }
        : post.target_scope === 'married'
            ? { color: '#14532d', backgroundColor: '#dcfce7', borderColor: '#86efac', fontSize: '0.78rem', fontWeight: 'normal', padding: '2px 8px', whiteSpace: 'nowrap', flexShrink: 0 }
            : { fontSize: '0.78rem', fontWeight: 'normal', padding: '2px 8px', whiteSpace: 'nowrap', flexShrink: 0 };

    return (
        <article
            id={`post${post.post_no}`}
            data-post-no={post.post_no}
            className={`card border-0 shadow-sm mb-4 p-3 feed-post-card ${isHighlighted ? 'highlighted-post' : ''}`}
            style={cardAccentStyle}
        >
            {/* Tagged with You Heritage Ribbon */}
            {isTaggedSelf && (
                <div
                    className="d-flex align-items-center justify-content-between mb-2 py-1 px-2 rounded-2 shadow-2xs"
                    style={{ background: 'rgba(163,22,33,0.06)', border: '1px solid rgba(163,22,33,0.18)' }}
                >
                    <div className="d-flex align-items-center gap-1" style={{ color: '#8a1019', fontSize: '0.78rem', fontWeight: 600 }}>
                        <i className="bi bi-tag-fill" style={{ fontSize: '0.82rem' }} aria-hidden="true" />
                        <span>You are tagged in this family memory</span>
                    </div>
                    <span className="badge rounded-pill bg-white text-muted border px-2 py-1 shadow-2xs" style={{ fontSize: '0.7rem', fontWeight: 500 }}>
                        Living Memory
                    </span>
                </div>
            )}

            {/* Classic Post Author Header */}
            <div className="d-flex align-items-center mb-3">
                {brokenAvatar ? (
                    <div
                        className="rounded-circle me-3 d-inline-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                        style={{ width: '40px', height: '40px', backgroundColor: '#a31621', fontSize: '0.85rem' }}
                    >
                        {getInitials(post.firstName, post.lastName)}
                    </div>
                ) : (
                    <img
                        src={getAvatarUrl(post)}
                        alt={authorName}
                        className="rounded-circle me-3 flex-shrink-0"
                        style={{ width: '40px', height: '40px', objectFit: 'cover' }}
                        onError={() => setBrokenAvatar(true)}
                    />
                )}

                <div className="flex-grow-1 min-w-0">
                    <div className="d-flex justify-content-between align-items-center gap-2">
                        <div className="min-w-0">
                            <div className="d-flex align-items-center flex-wrap gap-1">
                                <h3 className="h6 mb-0 fw-semibold" style={{ fontSize: '1.05rem', color: 'var(--text-main, #0f172a)' }}>
                                    {authorName}
                                </h3>
                                {isOfficial && (
                                    <span
                                        className="badge rounded-pill text-white ms-1 px-2 py-1 d-inline-flex align-items-center gap-1 shadow-2xs"
                                        style={{ background: 'linear-gradient(135deg, #059669 0%, #10b981 100%)', fontSize: '0.72rem', fontWeight: 600 }}
                                        title="Verified FamilyPlatform Account"
                                    >
                                        <i className="bi bi-patch-check-fill" style={{ color: '#ffffff', fontSize: '0.75rem' }} aria-hidden="true" />
                                        <span>Official Update</span>
                                    </span>
                                )}
                                {isHistorian && (
                                    <span
                                        className="badge rounded-pill text-white ms-1 px-2 py-1 d-inline-flex align-items-center gap-1 shadow-2xs"
                                        style={{ background: 'linear-gradient(135deg, #312e81 0%, #4338ca 100%)', color: '#ffffff', fontSize: '0.72rem', fontWeight: 600, whiteSpace: 'nowrap', flexShrink: 0 }}
                                        title="Automated Family Historian Digest"
                                    >
                                        <i className="bi bi-journal-richtext" style={{ color: '#fde68a', fontSize: '0.75rem' }} aria-hidden="true" />
                                        <span>Do You Know?</span>
                                    </span>
                                )}
                                {Array.isArray(post.post_tags) && post.post_tags.length > 0 && (
                                    <span className="text-secondary small fw-normal ms-1">
                                        is with{' '}
                                        {post.post_tags.slice(0, 2).map((tag, idx) => (
                                            <span key={String(tag.user_id || idx)}>
                                                <a
                                                    href={`/member/profilePage?id=${encodeURIComponent(tag.user_id || tag.id || '')}`}
                                                    className="fw-semibold text-decoration-underline"
                                                    style={{ color: 'var(--text-main, #0f172a)' }}
                                                >
                                                    {tag.fullName || `${tag.firstName || ''} ${tag.lastName || ''}`.trim()}
                                                </a>
                                                {idx === 0 && post.post_tags.length === 2 ? ' and ' : (idx === 0 && post.post_tags.length > 2 ? ', ' : '')}
                                            </span>
                                        ))}
                                        {post.post_tags.length > 2 && ` and ${post.post_tags.length - 2} others`}
                                    </span>
                                )}
                            </div>
                            <div className="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                <small className="text-muted" style={{ fontSize: '0.85rem' }}>
                                    {post._optimistic ? 'Sharing…' : formatRelativeTime(post.post_time || post.date_created)}
                                </small>
                                {!isOfficial && post.postFamCode && (
                                    <span
                                        className={`badge rounded-pill ${post.target_scope === 'birth' ? 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' : (post.target_scope === 'married' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis')}`}
                                        style={famBadgeStyle}
                                    >
                                        <i className={`bi me-1 ${post.target_scope === 'birth' ? 'bi-heart-pulse-fill' : (post.target_scope === 'married' ? 'bi-house-heart-fill' : 'bi-people')}`} aria-hidden="true" />
                                        #{String(post.postFamCode).replace(/^#+/, '')}
                                    </span>
                                )}
                                {!isOfficial && !post.postFamCode && !ownPost && (
                                    <span className="badge rounded-pill bg-secondary-subtle text-secondary-emphasis" style={famBadgeStyle}>
                                        <i className="bi bi-people me-1" aria-hidden="true" /> Connected Kin
                                    </span>
                                )}
                            </div>
                        </div>

                        {/* Classic Three-Dots Kebab Dropdown */}
                        <div className="dropdown flex-shrink-0">
                            <button
                                className="btn btn-sm btn-link text-muted p-1 rounded-circle d-inline-flex align-items-center justify-content-center"
                                type="button"
                                id={`kebab${post.post_no}`}
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                style={{ textDecoration: 'none', minWidth: '44px', minHeight: '44px' }}
                                title="Post options"
                                aria-label="Post options"
                            >
                                <i className="bi bi-three-dots" style={{ fontSize: '1.25rem' }} aria-hidden="true" />
                            </button>
                            <ul
                                className="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                aria-labelledby={`kebab${post.post_no}`}
                                style={{ borderRadius: '12px', fontSize: '0.9rem', minWidth: '180px' }}
                            >
                                {ownPost ? (
                                    <>
                                        <li>
                                            <button
                                                type="button"
                                                className="dropdown-item py-2 d-flex align-items-center gap-2"
                                                style={{ minHeight: '44px' }}
                                                onClick={handleEditClick}
                                            >
                                                <i className="bi bi-pencil-square text-primary" aria-hidden="true" />
                                                <span>Edit Post</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                type="button"
                                                className="dropdown-item py-2 d-flex align-items-center gap-2"
                                                style={{ minHeight: '44px' }}
                                                onClick={handleRepost}
                                            >
                                                <i className="bi bi-arrow-repeat text-success" aria-hidden="true" />
                                                <span>Repost</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                type="button"
                                                className="dropdown-item py-2 d-flex align-items-center gap-2"
                                                style={{ minHeight: '44px' }}
                                                onClick={handleShare}
                                            >
                                                <i className="bi bi-send-fill text-success" style={{ transform: 'rotate(-15deg)', display: 'inline-block' }} aria-hidden="true" />
                                                <span>Share Post</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                type="button"
                                                className="dropdown-item py-2 d-flex align-items-center gap-2 text-danger"
                                                style={{ minHeight: '44px' }}
                                                onClick={() => setConfirmingDelete(true)}
                                            >
                                                <i className="bi bi-trash3" aria-hidden="true" />
                                                <span>Delete Post</span>
                                            </button>
                                        </li>
                                    </>
                                ) : (
                                    <li>
                                        <button
                                            type="button"
                                            className="dropdown-item py-2 d-flex align-items-center gap-2 text-danger"
                                            style={{ minHeight: '44px', whiteSpace: 'nowrap' }}
                                            onClick={async () => {
                                                if (typeof window.openReportModal === 'function') {
                                                    window.openReportModal('post', post.post_no);
                                                    return;
                                                }
                                                try {
                                                    await apiFetch('/member/content/report', {
                                                        method: 'POST',
                                                        body: {
                                                            target_type: 'post',
                                                            target_id: post.post_no,
                                                            reason: 'inappropriate',
                                                            details: 'Flagged from family feed',
                                                        },
                                                    }, bootstrap);
                                                    notifyToast('Report submitted to family moderators', 'success');
                                                } catch (err) {
                                                    notifyToast(err?.message || 'Unable to submit report.', 'error');
                                                }
                                            }}
                                        >
                                            <i className="bi bi-flag-fill" aria-hidden="true" />
                                            <span>Report Post</span>
                                        </button>
                                    </li>
                                )}
                                {isTaggedSelf && (
                                    <li>
                                        <button
                                            type="button"
                                            className="dropdown-item py-2 d-flex align-items-center gap-2 text-warning"
                                            style={{ minHeight: '44px' }}
                                            onClick={handleUntagSelf}
                                        >
                                            <i className="bi bi-tag-fill" aria-hidden="true" />
                                            <span>Remove Tag from Post</span>
                                        </button>
                                    </li>
                                )}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {/* Delete Confirmation Banner */}
            {confirmingDelete && (
                <div
                    className="p-3 mb-3 rounded-3 border d-flex align-items-center justify-content-between flex-wrap gap-2"
                    style={{ background: '#fff1f2', borderColor: '#fecdd3', color: '#881337' }}
                >
                    <span className="fw-semibold">Delete this family memory?</span>
                    <div className="d-flex gap-2">
                        <button
                            type="button"
                            className="btn btn-danger btn-sm rounded-pill px-3 fw-semibold"
                            style={{ minHeight: '44px' }}
                            disabled={deleting}
                            onClick={handleConfirmDelete}
                        >
                            {deleting ? 'Deleting…' : 'Confirm Delete'}
                        </button>
                        <button
                            type="button"
                            className="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold"
                            style={{ minHeight: '44px' }}
                            onClick={() => setConfirmingDelete(false)}
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            )}

            {/* Inline Edit Fallback (used when #postModal is not mounted, e.g. unit tests) */}
            {isEditing ? (
                <div className="mb-3">
                    <textarea
                        className="form-control"
                        rows={3}
                        value={editDraft}
                        onInput={(e) => setEditDraft(e.target.value)}
                        aria-label="Edit post message"
                    />
                    <div className="d-flex gap-2 mt-2">
                        <button
                            type="button"
                            className="btn btn-primary btn-sm rounded-pill px-3 fw-semibold"
                            style={{ minHeight: '44px' }}
                            disabled={savingEdit || !editDraft.trim()}
                            onClick={handleSaveEdit}
                        >
                            {savingEdit ? 'Saving…' : 'Save Changes'}
                        </button>
                        <button
                            type="button"
                            className="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold"
                            style={{ minHeight: '44px' }}
                            onClick={() => setIsEditing(false)}
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            ) : (
                !hasMedia && !pollMeta && captionText && (
                    <div className="post-content px-1 mb-3">
                        {stageMeta ? (
                            <div
                                className={`fp-text-stage fp-text-stage--${stageMeta.slug}`}
                                data-testid="feed-text-stage-card"
                            >
                                <div className="fp-text-stage__orb fp-text-stage__orb--a" aria-hidden="true" />
                                <div className="fp-text-stage__orb fp-text-stage__orb--b" aria-hidden="true" />
                                <div className="fp-text-stage__grid" aria-hidden="true" />
                                <div className="fp-text-stage__header">
                                    <span className="fp-stage-badge">
                                        {stageMeta.slug === 'alert_pulse' && (
                                            <span className="fp-stage-badge__pulse" aria-hidden="true" />
                                        )}
                                        <span>{stageMeta.badge}</span>
                                    </span>
                                </div>
                                <div
                                    className={`fp-text-stage__body ${captionText.length <= 95 ? 'fp-text-stage__body--hero' : 'fp-text-stage__body--prose'}`}
                                >
                                    {renderTokens(captionText)}
                                </div>
                            </div>
                        ) : (
                            <div
                                className="post-text-body"
                                style={{
                                    fontSize: '0.975rem',
                                    lineHeight: 1.55,
                                    whiteSpace: 'pre-line',
                                    wordBreak: 'break-word',
                                    color: 'var(--text-main, #0f172a)',
                                }}
                            >
                                {renderTokens(captionText)}
                            </div>
                        )}
                    </div>
                )
            )}

            {/* OpenGraph Link Preview (for text posts without native media) */}
            {post.link_preview && (post.link_preview.image || post.link_preview.title || post.link_preview.url) && !hasMedia && !stageMeta && (() => {
                const lp = post.link_preview;
                const cleanPreviewImg = String(lp.image || '').replace(/&amp;/g, '&').trim();
                const showPreviewImg = cleanPreviewImg !== '' && brokenPreviewImgUrl !== lp.image;
                const lpDomainLower = String(lp.domain || lp.url || '').toLowerCase();
                const lpUrlLower = String(lp.url || lp.original_url || '').toLowerCase();
                const isSocialVideo = Boolean(
                    lp.is_social_video
                    || lp.is_video
                    || lpUrlLower.includes('/reel/')
                    || lpUrlLower.includes('/share/v/')
                    || lpUrlLower.includes('/share/r/')
                    || lpUrlLower.includes('/videos/')
                    || lpUrlLower.includes('facebook.com/watch')
                    || lpUrlLower.includes('fb.watch')
                    || lpUrlLower.includes('tiktok.com/')
                    || lpUrlLower.includes('youtube.com/')
                    || lpUrlLower.includes('youtu.be/')
                );
                const platformIconClass = lpDomainLower.includes('facebook.com') || lpDomainLower.includes('fb.watch') || lpDomainLower.includes('fb.com')
                    ? 'bi-facebook text-primary'
                    : lpDomainLower.includes('youtube.com') || lpDomainLower.includes('youtu.be')
                        ? 'bi-youtube text-danger'
                        : lpDomainLower.includes('instagram.com') || lpDomainLower.includes('instagr.am')
                            ? 'bi-instagram text-danger'
                            : lpDomainLower.includes('tiktok.com')
                                ? 'bi-tiktok'
                                : lpDomainLower.includes('twitter.com') || lpDomainLower.includes('x.com')
                                    ? 'bi-twitter-x'
                                    : lpDomainLower.includes('linkedin.com') || lpDomainLower.includes('lnkd.in')
                                        ? 'bi-linkedin text-primary'
                                        : lp.is_party_event
                                            ? 'bi-balloon-fill text-warning'
                                            : 'bi-globe2';

                return (
                    <div className="link-preview-container mb-3 px-1">
                        <a
                            href={lp.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="link-preview-card text-decoration-none rounded-3 overflow-hidden shadow-sm"
                        >
                            {showPreviewImg && (
                                <div className="link-preview-card__media">
                                    <img
                                        src={cleanPreviewImg}
                                        alt={lp.title || 'Link preview'}
                                        className="w-100 h-100 object-fit-cover"
                                        loading="lazy"
                                        referrerPolicy="no-referrer"
                                        onError={() => setBrokenPreviewImgUrl(lp.image)}
                                    />
                                    <span className="link-preview-card__platform-pill">
                                        {lp.site_name || lp.domain || 'Web Link'}
                                    </span>
                                    {isSocialVideo && (
                                        <span className="link-preview-card__play-badge" aria-hidden="true">
                                            <i className="bi bi-play-fill" />
                                        </span>
                                    )}
                                </div>
                            )}
                            <div className="p-3 link-preview-card__body">
                                <div className="d-flex justify-content-between align-items-center mb-1 link-preview-card__eyebrow">
                                    <small className="text-uppercase fw-semibold">{lp.domain || lp.site_name || 'External Link'}</small>
                                    <small className="fw-medium link-preview-card__type">
                                        <i className="bi bi-lightning-charge-fill me-1" aria-hidden="true" /> Rich Preview
                                    </small>
                                </div>
                                <div className="d-flex align-items-start gap-3">
                                    {!showPreviewImg && (
                                        <div className="link-preview-card__icon-tile" aria-hidden="true">
                                            <i className={`bi ${platformIconClass}`} />
                                        </div>
                                    )}
                                    <div className="min-w-0 flex-grow-1">
                                        <h6 className="fw-bold mb-1 link-preview-card__title">{lp.title || lp.url}</h6>
                                        {lp.description && (
                                            <p className="small mb-2 link-preview-card__desc">{lp.description}</p>
                                        )}
                                    </div>
                                </div>
                                <div className="pt-2 d-flex justify-content-between align-items-center link-preview-card__footer">
                                    <span className="small text-truncate link-preview-card__url" style={{ maxWidth: '58%' }}>
                                        {lp.site_name || lp.domain || 'Web Link'}
                                    </span>
                                    <span className="badge rounded-pill px-3 py-1 link-preview-card__cta d-inline-flex align-items-center">
                                        {lp.cta_label || 'Open Link →'}
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                );
            })()}

            {/* Then & Now Comparison Slider */}
            {post.comparison && (
                <div className="then-now px-1 mb-3">
                    <div className="then-now__stage vse-skeleton-surface" style={{ aspectRatio: '4 / 5', maxHeight: '520px' }}>
                        <img
                            className="then-now__img w-100 h-100 object-fit-cover"
                            src={getPostImageUrl(post.comparison.after_img)}
                            alt={post.comparison.after_label || 'Now'}
                            loading="lazy"
                        />
                        <div
                            className="then-now__img then-now__img--then"
                            style={{ clipPath: `inset(0 ${100 - sliderPos}% 0 0)` }}
                        >
                            <img
                                className="w-100 h-100 object-fit-cover"
                                src={getPostImageUrl(post.comparison.before_img)}
                                alt={post.comparison.before_label || 'Then'}
                                loading="lazy"
                            />
                        </div>
                        <span className="then-now__tag then-now__tag--then">{post.comparison.before_label || 'Then'}</span>
                        {post.comparison.delta_badge && (
                            <span className="then-now__delta-pill">{post.comparison.delta_badge}</span>
                        )}
                        <span className="then-now__tag then-now__tag--now">{post.comparison.after_label || 'Now'}</span>
                        <div className="then-now__handle" style={{ left: `${sliderPos}%` }} aria-hidden="true">
                            <i className="bi bi-arrows-expand-vertical" />
                        </div>
                        <input
                            type="range"
                            min="0"
                            max="100"
                            value={sliderPos}
                            onInput={(e) => setSliderPos(Number(e.target.value))}
                            className="then-now__range"
                            aria-label="Slide to compare Then and Now"
                        />
                    </div>
                </div>
            )}

            {/* Single Photo (Classic Aura Stage — uncropped full render) */}
            {images.length === 1 && !post.comparison && (
                <div className="mb-3 px-1">
                    {brokenPhotoUrls[images[0]] ? (
                        <div className="post-media-stage__fallback">
                            <i className="bi bi-image fs-3" aria-hidden="true" />
                            <span>Photo unavailable</span>
                        </div>
                    ) : (
                        <div className="position-relative feed-media-aura-container">
                            <div className="feed-media-aura-backdrop" aria-hidden="true">
                                <img src={images[0]} alt="" loading="lazy" referrerPolicy="no-referrer" />
                            </div>
                            <div
                                className="position-relative overflow-hidden post-media-stage vse-skeleton-surface"
                                style={{
                                    borderRadius: '12px',
                                    width: '100%',
                                    aspectRatio: 'auto',
                                    minHeight: '220px',
                                    maxHeight: '640px',
                                    background: 'rgba(0,0,0,0.03)',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                }}
                            >
                                <button
                                    type="button"
                                    className="border-0 p-0 bg-transparent w-100 h-100 d-flex align-items-center justify-content-center"
                                    onClick={() => onOpenLightbox?.(images, 0, post)}
                                    onDblClick={() => onReactPost?.(post.post_no, 'love')}
                                    aria-label="View photo 1 of 1"
                                    style={{ borderRadius: '12px', overflow: 'hidden', cursor: 'pointer', minHeight: '44px' }}
                                >
                                    <img
                                        src={images[0]}
                                        alt="Post photo 1"
                                        className="post-media-first-img"
                                        referrerPolicy="no-referrer"
                                        onError={() => setBrokenPhotoUrls((prev) => ({ ...prev, [images[0]]: true }))}
                                        style={{
                                            borderRadius: '12px',
                                            width: '100%',
                                            height: 'auto',
                                            maxHeight: '640px',
                                            objectFit: 'contain',
                                            display: 'block',
                                        }}
                                        loading="lazy"
                                    />
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* Multi-Photo Grid */}
            {images.length > 1 && !post.comparison && (
                <div
                    className="mb-3 px-1"
                    style={{
                        display: 'grid',
                        gap: '0.5rem',
                        gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))',
                    }}
                >
                    {images.map((src, idx) => (
                        <button
                            key={`${src}_${idx}`}
                            type="button"
                            className="border-0 p-0 position-relative overflow-hidden"
                            style={{
                                borderRadius: '12px',
                                minHeight: '180px',
                                maxHeight: '340px',
                                backgroundColor: '#0f172a',
                                cursor: 'pointer',
                            }}
                            onClick={() => onOpenLightbox?.(images, idx, post)}
                            aria-label={`View photo ${idx + 1} of ${images.length}`}
                        >
                            {brokenPhotoUrls[src] ? (
                                <div className="post-media-stage__fallback" style={{ minHeight: '180px' }}>
                                    <i className="bi bi-image fs-4" aria-hidden="true" />
                                    <span>Photo unavailable</span>
                                </div>
                            ) : (
                                <img
                                    src={src}
                                    alt={`Post photo ${idx + 1}`}
                                    className="w-100 h-100 object-fit-cover d-block atg-4k-image-render"
                                    referrerPolicy="no-referrer"
                                    onError={() => setBrokenPhotoUrls((prev) => ({ ...prev, [src]: true }))}
                                    loading="lazy"
                                />
                            )}
                        </button>
                    ))}
                </div>
            )}

            {/* Videos (Classic Horizontal Multi-Video Carousel or Single Video Aura Stage) */}
            {videos.length > 0 && (
                <FeedVideoGallery videos={videos} onOpenVideo={onOpenVideo} />
            )}

            {/* Classic 3D Poll Arena */}
            {pollMeta && (
                <div
                    className={`fp-poll-arena fp-poll-arena--${pollMeta.theme} mx-1 mb-3`}
                    data-testid="feed-poll-arena-card"
                >
                    <div className="fp-poll-arena__orb fp-poll-arena__orb--a" aria-hidden="true" />
                    <div className="fp-poll-arena__orb fp-poll-arena__orb--b" aria-hidden="true" />
                    <div className="fp-poll-arena__grid" aria-hidden="true" />

                    <div className="fp-poll-arena__top">
                        <span className="fp-poll-arena__badge">{pollMeta.badge}</span>
                        <span className="fp-poll-arena__votes-pill">
                            <i className="bi bi-people-fill" aria-hidden="true" />
                            <span>{totalPollVotes} {totalPollVotes === 1 ? 'vote' : 'votes'}</span>
                        </span>
                    </div>

                    {getPollCleanIntro(post) && (
                        <div className="fp-poll-arena__intro">{renderTokens(getPollCleanIntro(post))}</div>
                    )}

                    {post.poll?.question && (
                        <h6 className="fp-poll-arena__question">{post.poll.question}</h6>
                    )}

                    <p className="fp-poll-arena__subtitle mb-3">{pollMeta.subtitle}</p>

                    <div className="fp-poll-arena__options">
                        {pollOptions.map((opt, optIdx) => {
                            const optVotes = Number(opt.vote_count || 0);
                            const pct = opt.percentage !== undefined
                                ? Number(opt.percentage)
                                : (totalPollVotes > 0 ? Math.round((optVotes / totalPollVotes) * 100) : 0);
                            const isVoted = votedIds.includes(String(opt.option_id));
                            const isLeading = leadingOptionId !== null && String(opt.option_id) === leadingOptionId;
                            return (
                                <button
                                    key={opt.option_id}
                                    type="button"
                                    disabled={votingPoll}
                                    onClick={() => handleVoteOption(opt.option_id)}
                                    className={`fp-poll-opt poll-option-btn ${isVoted ? 'is-voted' : ''} ${isLeading ? 'is-leading' : ''}`}
                                    style={{ minHeight: '44px' }}
                                >
                                    <div className="fp-poll-opt__fill" style={{ width: `${pct}%` }} />
                                    <span className="fp-poll-opt__letter">{OPTION_LETTERS[optIdx] || optIdx + 1}</span>
                                    <span className="fp-poll-opt__label">
                                        <span>{opt.option_text || opt.label}</span>
                                        {isVoted && <i className="bi bi-check-circle-fill fp-poll-opt__check" title="Your vote" />}
                                        {isLeading && <span className="fp-poll-opt__crown" title="Leading choice">👑</span>}
                                    </span>
                                    <span className="fp-poll-opt__pct">{pct}%</span>
                                </button>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Classic Actions Bar */}
            <div className="d-flex align-items-center justify-content-between pt-2 pb-1 mb-1 feed-actions-row">
                <div className="d-flex align-items-center gap-2">
                    <div
                        className="position-relative"
                        onMouseLeave={() => setShowReactionPicker(false)}
                    >
                        {showReactionPicker && (
                            <div
                                className="reaction-bar-float heirloom-glass-dock"
                                style={{
                                    position: 'absolute',
                                    bottom: 'calc(100% + 8px)',
                                    left: 0,
                                    display: 'flex',
                                    gap: '6px',
                                    zIndex: 100,
                                    whiteSpace: 'nowrap',
                                }}
                                role="group"
                                aria-label="Choose a reaction"
                            >
                                {reactionList.map((r) => (
                                    <button
                                        key={r.key}
                                        type="button"
                                        title={r.label}
                                        aria-label={r.label}
                                        className={`heirloom-reaction-btn ${post.user_reaction === r.key ? 'active' : ''}`}
                                        style={{ minWidth: '44px', minHeight: '44px' }}
                                        onClick={() => {
                                            setShowReactionPicker(false);
                                            onReactPost?.(post.post_no, r.key);
                                        }}
                                    >
                                        <span
                                            className="heirloom-glass-glyph d-inline-flex align-items-center justify-content-center"
                                            dangerouslySetInnerHTML={{ __html: getHeirloomGlassSvg(r.key, 30) }}
                                        />
                                        <span className="heirloom-tooltip-pill">{r.label}</span>
                                    </button>
                                ))}
                            </div>
                        )}

                        <button
                            type="button"
                            className={`btn btn-action-glyph btn-action-like rounded-circle position-relative heirloom-trigger-btn ${activeReactionMeta ? `is-liked reaction-${activeReactionMeta.key}` : ''}`}
                            style={{ minWidth: '44px', minHeight: '44px' }}
                            aria-expanded={showReactionPicker}
                            aria-label={activeReactionMeta ? `Reacted ${activeReactionMeta.label}` : 'React to this post'}
                            title={activeReactionMeta ? `Reacted: ${activeReactionMeta.label}` : 'React with heirloom glass love and appreciation'}
                            onMouseEnter={() => setShowReactionPicker(true)}
                            onClick={() => setShowReactionPicker((v) => !v)}
                        >
                            {activeReactionMeta ? (
                                <span
                                    className="d-inline-flex align-items-center justify-content-center heirloom-active-icon"
                                    dangerouslySetInnerHTML={{ __html: getHeirloomGlassSvg(activeReactionMeta.key, 24) }}
                                />
                            ) : (
                                <i className="bi bi-heart" style={{ fontSize: '1.35rem', lineHeight: 1 }} aria-hidden="true" />
                            )}
                        </button>
                    </div>

                    <button
                        type="button"
                        className="btn btn-action-glyph btn-action-comment rounded-circle"
                        style={{ minWidth: '44px', minHeight: '44px' }}
                        aria-expanded={commentsExpanded}
                        aria-label="Comment on this post"
                        title="Write or view family comments on this post"
                        onClick={() => setCommentsExpanded((v) => !v)}
                    >
                        <i className="bi bi-chat-quote-fill" style={{ fontSize: '1.3rem', lineHeight: 1 }} aria-hidden="true" />
                    </button>

                    {ownPost && (
                        <button
                            type="button"
                            className="btn btn-action-glyph btn-action-share rounded-circle"
                            style={{ minWidth: '44px', minHeight: '44px' }}
                            aria-label="Share this memory"
                            title="Share with family relatives or via social apps"
                            onClick={handleShare}
                        >
                            <i className="bi bi-send-fill" style={{ fontSize: '1.15rem', lineHeight: 1, transform: 'rotate(-15deg)', display: 'inline-block' }} aria-hidden="true" />
                        </button>
                    )}
                </div>

                <div>
                    <button
                        type="button"
                        className={`btn btn-action-glyph btn-action-bookmark rounded-circle ${savedMemory ? 'is-saved' : ''}`}
                        style={{ minWidth: '44px', minHeight: '44px' }}
                        aria-label="Save this family memory"
                        title="Save memory to family collection"
                        onClick={() => {
                            if (typeof window.__saveMemory === 'function') {
                                window.__saveMemory(post.post_no);
                            }
                            setSavedMemory((prev) => {
                                const next = !prev;
                                try {
                                    const raw = JSON.parse(localStorage.getItem('fp_saved_memories') || '[]');
                                    const set = new Set(Array.isArray(raw) ? raw.map(String) : []);
                                    if (next) set.add(String(post.post_no));
                                    else set.delete(String(post.post_no));
                                    localStorage.setItem('fp_saved_memories', JSON.stringify(Array.from(set)));
                                } catch { /* ignore storage errors */ }
                                notifyToast(next ? 'Saved to your family memories' : 'Removed from saved memories', 'success');
                                return next;
                            });
                        }}
                    >
                        <i className={savedMemory ? 'bi bi-bookmark-fill' : 'bi bi-bookmark'} style={{ fontSize: '1.25rem', lineHeight: 1 }} aria-hidden="true" />
                    </button>
                </div>
            </div>

            {/* Social Proof & Engagement Meta */}
            {(post.post_likes > 0 || viewCount > 0 || commentCount > 0) && (
                <div className="d-flex justify-content-between align-items-center px-1 pb-1 pt-1" style={{ fontSize: '0.85rem' }}>
                    <div>
                        {post.post_likes > 0 && (
                            <span className="d-inline-flex align-items-center gap-1 fw-bold" style={{ fontSize: '0.88rem', color: 'var(--text-main, #0f172a)' }}>
                                <span
                                    className="d-inline-flex align-items-center justify-content-center"
                                    style={{ lineHeight: 1 }}
                                    dangerouslySetInnerHTML={{ __html: getHeirloomGlassSvg(post.user_reaction || 'love', 18) }}
                                />
                                <span>{post.post_likes}</span>
                            </span>
                        )}
                    </div>
                    <div className="d-flex align-items-center gap-2 text-muted" style={{ fontSize: '0.825rem' }}>
                        <span className="d-inline-flex align-items-center gap-1 post-views-count">
                            <i className="bi bi-eye text-secondary" aria-hidden="true" />
                            <span>{viewCount} {viewCount === 1 ? 'view' : 'views'}</span>
                        </span>
                        {commentCount > 0 && (
                            <button
                                type="button"
                                className="btn btn-link btn-sm p-0 text-muted text-decoration-none"
                                style={{ fontSize: '0.825rem', minHeight: '44px' }}
                                onClick={() => setCommentsExpanded((v) => !v)}
                            >
                                • {commentCount} {commentCount === 1 ? 'comment' : 'comments'}
                            </button>
                        )}
                    </div>
                </div>
            )}

            {/* Instagram Inline Caption (for media posts without poll arena) */}
            {hasMedia && !pollMeta && captionText && (
                <div className="post-caption-block px-1 mb-2 pt-1" style={{ fontSize: '0.95rem', lineHeight: 1.5 }}>
                    <a
                        href={`/member/profilePage?id=${encodeURIComponent(post.id || '')}`}
                        className="fw-bold text-decoration-none me-1"
                        style={{ color: 'var(--text-main, #0f172a)', fontWeight: 600 }}
                    >
                        {authorName}
                    </a>
                    <span className="post-caption-text" style={{ whiteSpace: 'pre-line', color: 'var(--text-main, #0f172a)' }}>
                        {renderTokens(captionText)}
                    </span>
                </div>
            )}

            {/* Comments Section */}
            <CommentThread
                post={post}
                bootstrap={bootstrap}
                isExpanded={commentsExpanded}
                onMutateComments={onMutateComments}
                onSelectHashtag={onSelectHashtag}
            />
        </article>
    );
}
