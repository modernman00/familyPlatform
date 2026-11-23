import { useState, useRef, useEffect } from 'preact/hooks';
import { memo } from 'preact/compat';
import { apiFetch } from '../shared/apiClient';
import { getCsrfToken, notifyToast } from '../shared/bootstrap';
import { HEIRLOOM_REACTIONS, getHeirloomGlassSvg } from '../../components/profilePage/heirloomGlassReactions';
import { isAllowedGifUrl } from '../../components/profilePage/gifCatalog';
import { EmojiGifPicker } from './EmojiGifPicker';
import { RichTextTokens } from './RichTextTokens';
import {
    canEditComment,
    canModerateComment,
    formatRelativeTime,
    getAvatarUrl,
    getInitials,
    isVideoPath,
} from './feedUtils';

function getTopCommentReactions(reactions) {
    if (!Array.isArray(reactions)) return [];
    return [...reactions]
        .filter((r) => r && Number(r.count) > 0 && !['comment_no', 'total', 'totalReactions'].includes(r.label))
        .sort((a, b) => Number(b.count) - Number(a.count))
        .slice(0, 3);
}

function updateCommentReactionCounts(reactions, prevKey, nextKey) {
    const map = new Map();
    if (Array.isArray(reactions)) {
        reactions.forEach((r) => {
            if (r?.label) map.set(String(r.label), Number(r.count || 0));
        });
    }
    if (prevKey) {
        const cur = map.get(prevKey) || 0;
        if (cur <= 1) map.delete(prevKey);
        else map.set(prevKey, cur - 1);
    }
    if (nextKey) {
        map.set(nextKey, (map.get(nextKey) || 0) + 1);
    }
    return Array.from(map.entries()).map(([label, count]) => ({ label, count }));
}

export const CommentThread = memo(function CommentThread({
    post,
    bootstrap,
    isExpanded,
    onMutateComments,
    onSelectHashtag,
    onOpenCommentViewers,
}) {
    const currentUserId = String(bootstrap?.userId || '');
    const familyRoster = Array.isArray(bootstrap?.familyRoster) ? bootstrap.familyRoster : [];
    const comments = Array.isArray(post?.comments) ? post.comments : [];

    const [draft, setDraft] = useState('');
    const [replyingTo, setReplyingTo] = useState(null);
    const [replyDraft, setReplyDraft] = useState('');
    const [replySubmitting, setReplySubmitting] = useState(false);
    const [replyAttachedFile, setReplyAttachedFile] = useState(null);
    const [replyAttachedPreviewUrl, setReplyAttachedPreviewUrl] = useState('');
    const [replySelectedGifUrl, setReplySelectedGifUrl] = useState('');
    const [replySelectedSticker, setReplySelectedSticker] = useState(null);
    const [replySelectedLivingEmojis, setReplySelectedLivingEmojis] = useState([]);
    const [replyPickerTab, setReplyPickerTab] = useState(null);
    const [submitting, setSubmitting] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [editText, setEditText] = useState('');
    const [savingEdit, setSavingEdit] = useState(false);
    const [expandedThreads, setExpandedThreads] = useState({});
    const [openReactId, setOpenReactId] = useState(null);
    const [attachedFile, setAttachedFile] = useState(null);
    const [attachedPreviewUrl, setAttachedPreviewUrl] = useState('');
    const [selectedGifUrl, setSelectedGifUrl] = useState('');
    const [selectedSticker, setSelectedSticker] = useState(null);
    const [selectedLivingEmojis, setSelectedLivingEmojis] = useState([]);
    const [pickerTab, setPickerTab] = useState(null); // null | 'standard' | 'living' | 'me' | 'gif'
    const [mentionQuery, setMentionQuery] = useState('');
    const [mentionIndex, setMentionIndex] = useState(0);
    const [brokenAvatars, setBrokenAvatars] = useState({});
    const inputRef = useRef(null);
    const replyInputRef = useRef(null);
    const reactCloseTimerRef = useRef(null);

    useEffect(() => () => {
        if (reactCloseTimerRef.current) clearTimeout(reactCloseTimerRef.current);
    }, []);

    useEffect(() => {
        if (!attachedFile || typeof URL === 'undefined' || typeof URL.createObjectURL !== 'function') {
            setAttachedPreviewUrl('');
            return undefined;
        }
        if (!String(attachedFile.type || '').startsWith('image/')) {
            setAttachedPreviewUrl('');
            return undefined;
        }
        const objectUrl = URL.createObjectURL(attachedFile);
        setAttachedPreviewUrl(objectUrl);
        return () => {
            if (typeof URL.revokeObjectURL === 'function') {
                URL.revokeObjectURL(objectUrl);
            }
        };
    }, [attachedFile]);

    useEffect(() => {
        if (!replyAttachedFile || typeof URL === 'undefined' || typeof URL.createObjectURL !== 'function') {
            setReplyAttachedPreviewUrl('');
            return undefined;
        }
        if (!String(replyAttachedFile.type || '').startsWith('image/')) {
            setReplyAttachedPreviewUrl('');
            return undefined;
        }
        const objectUrl = URL.createObjectURL(replyAttachedFile);
        setReplyAttachedPreviewUrl(objectUrl);
        return () => {
            if (typeof URL.revokeObjectURL === 'function') {
                URL.revokeObjectURL(objectUrl);
            }
        };
    }, [replyAttachedFile]);

    const rootComments = comments.filter((c) => !c.parent_comment_no);
    const repliesFor = (rootNo) =>
        comments.filter((c) => String(c.parent_comment_no) === String(rootNo));

    const handleDraftChange = (value) => {
        const extracted = [];
        const cleaned = String(value || '').replace(/:([a-z0-9_]+_3d):/gi, (_, id) => {
            extracted.push(id.toLowerCase());
            return '';
        });
        if (extracted.length > 0) {
            setSelectedLivingEmojis((prev) => [...prev, ...extracted]);
        }
        const nextVal = extracted.length > 0 ? cleaned.replace(/^\s+/, '') : value;
        setDraft(nextVal);
        const match = nextVal.match(/@([A-Za-z0-9_.-]{1,24})$/);
        setMentionQuery(match ? match[1].toLowerCase() : '');
        setMentionIndex(0);
    };

    const mentionMatches = mentionQuery
        ? familyRoster
            .filter((m) => {
                const full = `${m.firstName || ''} ${m.lastName || ''}`.toLowerCase();
                return full.includes(mentionQuery);
            })
            .slice(0, 5)
        : [];

    const insertMention = (member) => {
        const name = `${member.firstName || ''} ${member.lastName || ''}`.trim();
        const next = draft.replace(/@[A-Za-z0-9_.-]{1,24}$/, `@${name} `);
        setDraft(next);
        setMentionQuery('');
        setMentionIndex(0);
        inputRef.current?.focus();
    };

    const handleInputKeyDown = (e) => {
        if (mentionMatches.length === 0) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setMentionIndex((i) => (i + 1) % mentionMatches.length);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setMentionIndex((i) => (i - 1 + mentionMatches.length) % mentionMatches.length);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const chosen = mentionMatches[mentionIndex] || mentionMatches[0];
            if (chosen) insertMention(chosen);
        } else if (e.key === 'Escape') {
            setMentionQuery('');
        }
    };

    const hasSubmittableReplyContent = Boolean(
        replyDraft.trim() || replyAttachedFile || replySelectedGifUrl || replySelectedSticker || replySelectedLivingEmojis.length > 0
    );

    const handleReplyDraftChange = (value) => {
        const extracted = [];
        const cleaned = String(value || '').replace(/:([a-z0-9_]+_3d):/gi, (_, id) => {
            extracted.push(id.toLowerCase());
            return '';
        });
        if (extracted.length > 0) {
            setReplySelectedLivingEmojis((prev) => [...prev, ...extracted]);
        }
        setReplyDraft(extracted.length > 0 ? cleaned.replace(/^\s+/, '') : value);
    };

    const handleOpenInlineReply = (targetComment) => {
        setReplyingTo(targetComment);
        setReplyDraft('');
        setReplyAttachedFile(null);
        setReplySelectedGifUrl('');
        setReplySelectedSticker(null);
        setReplySelectedLivingEmojis([]);
        setReplyPickerTab(null);
        setTimeout(() => replyInputRef.current?.focus(), 20);
    };

    const handleCancelInlineReply = () => {
        setReplyingTo(null);
        setReplyDraft('');
        setReplyAttachedFile(null);
        setReplySelectedGifUrl('');
        setReplySelectedSticker(null);
        setReplySelectedLivingEmojis([]);
        setReplyPickerTab(null);
    };

    const handleInlineReplySubmit = async (e) => {
        e.preventDefault();
        if (!replyingTo || !hasSubmittableReplyContent || replySubmitting) return;

        let text = replyDraft.trim();
        if (replySelectedLivingEmojis.length > 0) {
            const livingTokens = replySelectedLivingEmojis.map((id) => `:${id}:`).join(' ');
            text = text ? `${text} ${livingTokens}` : livingTokens;
        }
        if (replySelectedSticker?.token) {
            text = text ? `${text} ${replySelectedSticker.token}` : replySelectedSticker.token;
        }

        const tempId = `temp_${Date.now()}_${Math.random().toString(36).slice(2, 6)}`;
        const first = bootstrap?.firstName || 'You';
        const last = bootstrap?.lastName || '';
        const rootParentId = String(replyingTo.parent_comment_no || replyingTo.comment_no);
        const optimisticComment = {
            comment_no: tempId,
            post_no: String(post.post_no),
            id: currentUserId,
            firstName: first,
            lastName: last,
            fullName: `${first} ${last}`.trim(),
            profileImg: bootstrap?.avatar || '',
            comment: text,
            gif_url: replySelectedGifUrl || '',
            parent_comment_no: rootParentId,
            reactions: [],
            user_reaction: null,
            date_created: new Date().toISOString(),
            _optimistic: true,
            _clientKey: tempId,
        };

        const previousComments = [...comments];
        onMutateComments(post.post_no, [...previousComments, optimisticComment]);
        setExpandedThreads((prev) => ({ ...prev, [rootParentId]: true }));

        const prevDraft = replyDraft;
        const replyTarget = replyingTo;
        const fileToSend = replyAttachedFile;
        const gifToSend = replySelectedGifUrl;
        const stickerToSend = replySelectedSticker;
        const livingToSend = [...replySelectedLivingEmojis];

        setReplyDraft('');
        setReplyAttachedFile(null);
        setReplySelectedGifUrl('');
        setReplySelectedSticker(null);
        setReplySelectedLivingEmojis([]);
        setReplyPickerTab(null);
        setReplyingTo(null);
        setReplySubmitting(true);

        try {
            const formData = new FormData();
            formData.append('post_no', String(post.post_no));
            formData.append('comment', text);
            formData.append('parent_comment_no', rootParentId);
            if (fileToSend) {
                formData.append('comment_media', fileToSend);
            }
            if (gifToSend && isAllowedGifUrl(gifToSend)) {
                formData.append('gif_url', gifToSend);
            }

            const res = await apiFetch('/postCommentProfile', {
                method: 'POST',
                body: formData,
                timeoutMs: 45000,
            }, bootstrap);

            const serverComment = res?.message && typeof res.message === 'object' ? res.message : null;
            if (serverComment) {
                onMutateComments(
                    post.post_no,
                    [
                        ...previousComments,
                        {
                            ...optimisticComment,
                            ...serverComment,
                            gif_url: serverComment.gif_url || gifToSend || '',
                            comment_no: String(serverComment.comment_no || tempId),
                            _optimistic: false,
                        },
                    ]
                );
            }
        } catch (err) {
            onMutateComments(post.post_no, previousComments);
            setReplyingTo(replyTarget);
            setReplyDraft(prevDraft);
            setReplySelectedGifUrl(gifToSend);
            setReplySelectedSticker(stickerToSend);
            setReplySelectedLivingEmojis(livingToSend);
            notifyToast(err?.message || 'Could not post reply. Please try again.', 'error');
        } finally {
            setReplySubmitting(false);
        }
    };

    const hasSubmittableContent = Boolean(
        draft.trim() || attachedFile || selectedGifUrl || selectedSticker || selectedLivingEmojis.length > 0
    );

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!hasSubmittableContent || submitting) return;

        let text = draft.trim();
        if (selectedLivingEmojis.length > 0) {
            const livingTokens = selectedLivingEmojis.map((id) => `:${id}:`).join(' ');
            text = text ? `${text} ${livingTokens}` : livingTokens;
        }
        if (selectedSticker?.token) {
            text = text ? `${text} ${selectedSticker.token}` : selectedSticker.token;
        }

        const tempId = `temp_${Date.now()}_${Math.random().toString(36).slice(2, 6)}`;
        const first = bootstrap?.firstName || 'You';
        const last = bootstrap?.lastName || '';
        const rootParentId = replyingTo
            ? String(replyingTo.parent_comment_no || replyingTo.comment_no)
            : null;
        const optimisticComment = {
            comment_no: tempId,
            post_no: String(post.post_no),
            id: currentUserId,
            firstName: first,
            lastName: last,
            fullName: `${first} ${last}`.trim(),
            profileImg: bootstrap?.avatar || '',
            comment: text,
            gif_url: selectedGifUrl || '',
            parent_comment_no: rootParentId,
            reactions: [],
            user_reaction: null,
            date_created: new Date().toISOString(),
            _optimistic: true,
            _clientKey: tempId,
        };

        const previousComments = [...comments];
        onMutateComments(post.post_no, [...previousComments, optimisticComment]);

        const prevDraft = draft;
        const replyTarget = replyingTo;
        const fileToSend = attachedFile;
        const gifToSend = selectedGifUrl;
        const stickerToSend = selectedSticker;
        const livingToSend = [...selectedLivingEmojis];

        setDraft('');
        setMentionQuery('');
        setReplyingTo(null);
        setAttachedFile(null);
        setSelectedGifUrl('');
        setSelectedSticker(null);
        setSelectedLivingEmojis([]);
        setPickerTab(null);
        setSubmitting(true);

        try {
            const formData = new FormData();
            formData.append('post_no', String(post.post_no));
            formData.append('comment', text);
            if (replyTarget?.comment_no) {
                formData.append('parent_comment_no', String(replyTarget.parent_comment_no || replyTarget.comment_no));
            }
            if (fileToSend) {
                formData.append('comment_media', fileToSend);
            }
            if (gifToSend && isAllowedGifUrl(gifToSend)) {
                formData.append('gif_url', gifToSend);
            }

            const res = await apiFetch('/postCommentProfile', {
                method: 'POST',
                body: formData,
                timeoutMs: 45000,
            }, bootstrap);

            const serverComment = res?.message && typeof res.message === 'object' ? res.message : null;
            if (serverComment) {
                onMutateComments(
                    post.post_no,
                    [
                        ...previousComments,
                        {
                            ...optimisticComment,
                            ...serverComment,
                            gif_url: serverComment.gif_url || gifToSend || '',
                            comment_no: String(serverComment.comment_no || tempId),
                            _optimistic: false,
                        },
                    ]
                );
            }
        } catch (err) {
            onMutateComments(post.post_no, previousComments);
            setDraft(prevDraft);
            setReplyingTo(replyTarget);
            setSelectedGifUrl(gifToSend);
            setSelectedSticker(stickerToSend);
            setSelectedLivingEmojis(livingToSend);
            notifyToast(err?.message || 'Could not post comment. Please try again.', 'error');
        } finally {
            setSubmitting(false);
        }
    };

    const handleSaveEdit = async (comment) => {
        const text = editText.trim();
        if (!text || savingEdit) return;
        const previousComments = [...comments];
        setSavingEdit(true);
        onMutateComments(
            post.post_no,
            comments.map((c) => (String(c.comment_no) === String(comment.comment_no) ? { ...c, comment: text } : c))
        );
        try {
            await apiFetch(`/comment/${encodeURIComponent(comment.comment_no)}`, {
                method: 'PUT',
                body: { comment: text },
            }, bootstrap);
            setEditingId(null);
            setEditText('');
            notifyToast('Comment updated', 'success');
        } catch (err) {
            onMutateComments(post.post_no, previousComments);
            notifyToast(err?.message || 'Could not update comment.', 'error');
        } finally {
            setSavingEdit(false);
        }
    };

    const handleDeleteComment = async (comment) => {
        const previousComments = [...comments];
        onMutateComments(
            post.post_no,
            comments.filter((c) => String(c.comment_no) !== String(comment.comment_no))
        );
        try {
            await apiFetch(`/comment/${encodeURIComponent(comment.comment_no)}`, {
                method: 'DELETE',
            }, bootstrap);
            notifyToast('Comment removed', 'success');
        } catch (err) {
            onMutateComments(post.post_no, previousComments);
            notifyToast(err?.message || 'Could not delete comment.', 'error');
        }
    };

    const handleCommentReaction = async (comment, reactionKey) => {
        const previousComments = [...comments];
        const prevKey = comment.user_reaction || null;
        const nextReaction = prevKey === reactionKey ? null : reactionKey;
        onMutateComments(
            post.post_no,
            comments.map((c) => {
                if (String(c.comment_no) !== String(comment.comment_no)) return c;
                return {
                    ...c,
                    user_reaction: nextReaction,
                    reactions: updateCommentReactionCounts(c.reactions, prevKey, nextReaction),
                };
            })
        );
        setOpenReactId(null);

        try {
            const formData = new FormData();
            formData.append('comment_no', String(comment.comment_no));
            formData.append('reaction', reactionKey);
            formData.append('label', reactionKey);
            await apiFetch('/api/reactions/add', {
                method: 'POST',
                body: formData,
            }, bootstrap);
        } catch (err) {
            onMutateComments(post.post_no, previousComments);
            notifyToast(err?.message || 'Could not save reaction.', 'error');
        }
    };

    const reactionEntries = Object.values(HEIRLOOM_REACTIONS).filter(
        (r, idx, arr) => arr.findIndex((x) => x.key === r.key) === idx
    );

    const renderSingleComment = (comment, isReplyItem = false) => {
        const avatarKey = `${comment.comment_no}_${comment.id}`;
        const fullName = comment.fullName || `${comment.firstName || ''} ${comment.lastName || ''}`.trim() || 'Family Member';
        const isEditing = String(editingId) === String(comment.comment_no);
        const mediaSrc = comment.gif_url || comment.media_url || '';
        const topReactions = getTopCommentReactions(comment.reactions);
        const activeMeta = comment.user_reaction ? HEIRLOOM_REACTIONS[comment.user_reaction] : null;
        const isReactOpen = openReactId === comment.comment_no;

        return (
            <div
                key={comment._clientKey || comment.comment_no}
                id={`comment${comment.comment_no}`}
                className="d-flex align-items-start gap-2 feed-comment-item"
                style={isReplyItem ? { marginLeft: '2.25rem' } : undefined}
                data-comment-no={comment.comment_no}
                data-reply={isReplyItem ? 'true' : 'false'}
            >
                {brokenAvatars[avatarKey] ? (
                    <div
                        className="rounded-circle flex-shrink-0 d-inline-flex align-items-center justify-content-center fw-bold text-white"
                        style={{ width: '32px', height: '32px', backgroundColor: '#a31621', fontSize: '0.72rem', marginTop: '2px' }}
                    >
                        {getInitials(comment.firstName, comment.lastName)}
                    </div>
                ) : (
                    <img
                        src={getAvatarUrl(comment)}
                        alt={fullName}
                        className="rounded-circle flex-shrink-0 feed-comment-avatar"
                        width="32"
                        height="32"
                        style={{
                            width: '32px',
                            height: '32px',
                            minWidth: '32px',
                            minHeight: '32px',
                            objectFit: 'cover',
                            marginTop: '2px',
                        }}
                        onError={() => setBrokenAvatars((prev) => ({ ...prev, [avatarKey]: true }))}
                    />
                )}

                <div className="flex-grow-1 min-w-0">
                    {isEditing ? (
                        <div className="d-flex gap-2 align-items-center flex-wrap" style={{ maxWidth: '95%' }}>
                            <input
                                type="text"
                                className="form-control form-control-sm rounded-pill"
                                value={editText}
                                onInput={(e) => setEditText(e.target.value)}
                                aria-label="Edit comment"
                                style={{ fontSize: '0.875rem', padding: '8px 14px', minHeight: '44px' }}
                            />
                            <button
                                type="button"
                                className="btn btn-primary btn-sm rounded-pill px-3"
                                style={{ fontSize: '0.78rem', minHeight: '44px' }}
                                disabled={savingEdit}
                                onClick={() => handleSaveEdit(comment)}
                            >
                                {savingEdit ? 'Saving…' : 'Save'}
                            </button>
                            <button
                                type="button"
                                className="btn btn-link btn-sm text-muted"
                                style={{ fontSize: '0.78rem', minHeight: '44px' }}
                                onClick={() => { setEditingId(null); setEditText(''); }}
                            >
                                Cancel
                            </button>
                        </div>
                    ) : (
                        <div
                            className="px-3 py-2 rounded-3 comment-bubble-box"
                            style={{ display: 'inline-block', maxWidth: '92%' }}
                        >
                            <div className="fw-semibold comment-bubble-author" style={{ fontSize: '0.8125rem', lineHeight: 1.2 }}>
                                {fullName}
                            </div>
                            {comment.comment && (
                                <div
                                    className="mt-1 comment-bubble-text"
                                    style={{ fontSize: '0.92rem', wordBreak: 'break-word', lineHeight: 1.4 }}
                                >
                                    <RichTextTokens
                                        text={comment.comment}
                                        onSelectHashtag={onSelectHashtag}
                                    />
                                </div>
                            )}
                            {mediaSrc && (
                                isVideoPath(mediaSrc) ? (
                                    <video
                                        src={mediaSrc}
                                        controls
                                        playsInline
                                        preload="metadata"
                                        className="mt-1 rounded-3 comment-gif"
                                        data-testid="comment-video"
                                        style={{ display: 'block', width: 'auto', height: 'auto', maxWidth: '100%', maxHeight: '240px', objectFit: 'contain', background: '#0b0f19' }}
                                    />
                                ) : (
                                    <img
                                        src={mediaSrc}
                                        alt="Comment attachment"
                                        loading="lazy"
                                        className="mt-1 rounded-3 comment-gif"
                                        data-testid="comment-gif"
                                        style={{ display: 'block', width: 'auto', height: 'auto', maxWidth: '100%', maxHeight: '220px', objectFit: 'contain' }}
                                    />
                                )
                            )}
                        </div>
                    )}

                    {/* Comment Reaction Summary Pill */}
                    {topReactions.length > 0 && (
                        <div className="d-flex align-items-center gap-1 mt-1 ps-1">
                            <div
                                className="reaction-count-pill d-inline-flex gap-1 align-items-center"
                                data-testid="comment-reaction-summary"
                                style={{
                                    background: 'var(--card-bg, #ffffff)',
                                    border: '1px solid var(--border-color, #e2e8f0)',
                                    borderRadius: '20px',
                                    padding: '3px 9px',
                                    fontSize: '0.8rem',
                                    boxShadow: '0 1px 4px rgba(0,0,0,0.08)',
                                }}
                            >
                                {topReactions.map((r) => (
                                    <span key={r.label} className="d-inline-flex align-items-center gap-1">
                                        <span
                                            className="d-inline-flex align-items-center justify-content-center"
                                            dangerouslySetInnerHTML={{ __html: getHeirloomGlassSvg(r.label, 20) }}
                                        />
                                        <span style={{ color: 'var(--text-muted, #475569)', fontSize: '0.78rem', fontWeight: 600 }}>
                                            {r.count}
                                        </span>
                                    </span>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Compact Action Links Row */}
                    <div className="d-flex align-items-center flex-wrap gap-2 mt-1 ps-1" style={{ fontSize: '0.78rem' }}>
                        <span className="text-muted">
                            {comment._optimistic ? 'Sending…' : formatRelativeTime(comment.post_time || comment.date_created)}
                        </span>
                        <div
                            className="position-relative d-inline-flex align-items-center"
                            onMouseEnter={() => {
                                if (reactCloseTimerRef.current) clearTimeout(reactCloseTimerRef.current);
                                setOpenReactId(comment.comment_no);
                            }}
                            onMouseLeave={() => {
                                if (reactCloseTimerRef.current) clearTimeout(reactCloseTimerRef.current);
                                reactCloseTimerRef.current = setTimeout(() => setOpenReactId(null), 180);
                            }}
                        >
                            {isReactOpen && (
                                <div
                                    style={{ position: 'absolute', bottom: '100%', left: 0, paddingBottom: '6px', zIndex: 100 }}
                                    onMouseEnter={() => {
                                        if (reactCloseTimerRef.current) clearTimeout(reactCloseTimerRef.current);
                                        setOpenReactId(comment.comment_no);
                                    }}
                                    onMouseLeave={() => {
                                        if (reactCloseTimerRef.current) clearTimeout(reactCloseTimerRef.current);
                                        reactCloseTimerRef.current = setTimeout(() => setOpenReactId(null), 180);
                                    }}
                                >
                                    <div
                                        className="comment-reaction-float-bar heirloom-glass-dock d-flex gap-1 p-1 rounded-pill border bg-white shadow-sm"
                                        style={{ whiteSpace: 'nowrap' }}
                                        role="group"
                                        aria-label="Comment reactions"
                                    >
                                        {reactionEntries.map((r) => (
                                            <button
                                                key={r.key}
                                                type="button"
                                                className={`heirloom-reaction-btn btn btn-sm rounded-circle p-1 ${comment.user_reaction === r.key ? 'active bg-light border border-primary' : 'border-0 bg-transparent'}`}
                                                style={{ minWidth: '44px', minHeight: '44px' }}
                                                onClick={() => handleCommentReaction(comment, r.key)}
                                                title={r.label}
                                                aria-label={r.label}
                                            >
                                                <span
                                                    className="heirloom-glass-glyph d-inline-flex align-items-center justify-content-center"
                                                    dangerouslySetInnerHTML={{ __html: getHeirloomGlassSvg(r.key, 34) }}
                                                />
                                            </button>
                                        ))}
                                    </div>
                                </div>
                            )}
                            <button
                                type="button"
                                className={`btn btn-link btn-sm p-0 text-decoration-none fw-semibold d-inline-flex align-items-center gap-1 comment-meta-btn ${activeMeta ? '' : 'text-muted'}`}
                                style={{
                                    fontSize: '0.78rem',
                                    minHeight: '44px',
                                    color: activeMeta?.tone || undefined,
                                }}
                                aria-expanded={isReactOpen}
                                onClick={() => setOpenReactId(isReactOpen ? null : comment.comment_no)}
                            >
                                {activeMeta && (
                                    <span
                                        className="d-inline-flex align-items-center justify-content-center"
                                        dangerouslySetInnerHTML={{ __html: getHeirloomGlassSvg(activeMeta.key, 22) }}
                                    />
                                )}
                                <span>{activeMeta ? activeMeta.label : 'React'}</span>
                            </button>
                        </div>

                        <button
                            type="button"
                            data-testid="comment-reply-btn"
                            className="btn btn-link btn-sm p-0 text-decoration-none fw-semibold text-muted"
                            style={{ fontSize: '0.78rem', minHeight: '44px' }}
                            onClick={() => handleOpenInlineReply(comment)}
                        >
                            Reply
                        </button>
                        {canEditComment(comment, currentUserId) && !isEditing && (
                            <button
                                type="button"
                                className="btn btn-link btn-sm p-0 text-decoration-none fw-semibold text-muted"
                                style={{ fontSize: '0.78rem', minHeight: '44px' }}
                                onClick={() => {
                                    setEditingId(comment.comment_no);
                                    setEditText(comment.comment);
                                }}
                            >
                                Edit
                            </button>
                        )}
                        {canModerateComment(post, comment, currentUserId) && (
                            <button
                                type="button"
                                className="btn btn-link btn-sm p-0 text-decoration-none fw-semibold text-danger"
                                style={{ fontSize: '0.78rem', minHeight: '44px' }}
                                onClick={() => handleDeleteComment(comment)}
                            >
                                Delete
                            </button>
                        )}
                        {(() => {
                            const cViews = Number(comment.views_count ?? comment.view_count ?? 0);
                            const isAuthor = canEditComment(comment, currentUserId);
                            return isAuthor ? (
                                <button
                                    type="button"
                                    className="btn btn-link btn-sm p-0 text-decoration-none text-muted d-inline-flex align-items-center gap-1 comment-views-count"
                                    style={{ fontSize: '0.78rem', minHeight: '44px' }}
                                    title="Click to see who viewed your comment"
                                    onClick={() => onOpenCommentViewers?.(comment)}
                                >
                                    <i className="bi bi-eye text-primary" aria-hidden="true" />
                                    <span>{cViews} {cViews === 1 ? 'view' : 'views'}</span>
                                </button>
                            ) : (
                                <span className="text-muted d-inline-flex align-items-center gap-1 comment-views-count" title={`${cViews} views`}>
                                    <i className="bi bi-eye" aria-hidden="true" />
                                    <span>{cViews} {cViews === 1 ? 'view' : 'views'}</span>
                                </span>
                            );
                        })()}
                    </div>

                    {/* Inline Reply Composer directly below the comment being replied to */}
                    {String(replyingTo?.comment_no || '') === String(comment.comment_no) && (
                        <div className="mt-2 position-relative" data-testid="inline-reply-composer">
                            <div className="d-flex align-items-center justify-content-between mb-1 ps-1">
                                <span className="small text-muted" data-testid="comment-replying-to">
                                    Replying to <strong>{comment.fullName || `${comment.firstName} ${comment.lastName}`}</strong>
                                </span>
                                <button
                                    type="button"
                                    className="btn btn-link btn-sm text-muted p-0 text-decoration-none fw-semibold"
                                    style={{ minHeight: '44px', fontSize: '0.78rem' }}
                                    onClick={handleCancelInlineReply}
                                    aria-label="Cancel reply"
                                >
                                    Cancel
                                </button>
                            </div>

                            {replyPickerTab && (
                                <EmojiGifPicker
                                    initialTab={replyPickerTab}
                                    onSelectEmoji={(em) => {
                                        setReplyDraft((prev) => `${prev}${em}`);
                                        replyInputRef.current?.focus();
                                    }}
                                    onSelectLivingEmoji={(emojiId) => {
                                        if (emojiId && /^[a-z0-9_]+_3d$/i.test(emojiId)) {
                                            setReplySelectedLivingEmojis((prev) => [...prev, emojiId]);
                                        }
                                        setReplyPickerTab(null);
                                        replyInputRef.current?.focus();
                                    }}
                                    onSelectMeSticker={(st) => {
                                        setReplySelectedSticker(st);
                                        setReplyPickerTab(null);
                                        replyInputRef.current?.focus();
                                    }}
                                    onSelectGif={(url) => {
                                        if (isAllowedGifUrl(url)) {
                                            setReplySelectedGifUrl(url);
                                            setReplyPickerTab(null);
                                        }
                                    }}
                                    onClose={() => setReplyPickerTab(null)}
                                />
                            )}

                            <form onSubmit={handleInlineReplySubmit} className="comment-composer">
                                <input type="hidden" name="token" value={getCsrfToken(bootstrap)} />
                                <div className="comment-composer__field">
                                    {(replySelectedGifUrl || replySelectedSticker || replySelectedLivingEmojis.length > 0 || replyAttachedFile) && (
                                        <div className="comment-composer__attachments">
                                            {replySelectedLivingEmojis.map((emojiId, idx) => (
                                                <div key={`${emojiId}_${idx}`} className="comment-composer__preview-chip comment-composer__sticker">
                                                    <img
                                                        src={`/public/img/emoji/${emojiId}.svg?v=20261107a`}
                                                        alt={`Living emoji: ${emojiId.replace(/_3d$/i, '').replace(/_/g, ' ')}`}
                                                        width="48"
                                                        height="48"
                                                        style={{ display: 'block', width: '48px', height: '48px', borderRadius: '10px', objectFit: 'contain' }}
                                                    />
                                                    <button
                                                        type="button"
                                                        className="comment-composer__preview-remove"
                                                        aria-label="Remove living emoji"
                                                        onClick={() => setReplySelectedLivingEmojis((prev) => prev.filter((_, i) => i !== idx))}
                                                    >
                                                        ✕
                                                    </button>
                                                </div>
                                            ))}
                                            {replySelectedGifUrl && (
                                                <div className="comment-composer__preview-chip">
                                                    <img src={replySelectedGifUrl} alt="Selected GIF" className="comment-composer__preview-media" />
                                                    <button
                                                        type="button"
                                                        className="comment-composer__preview-remove"
                                                        aria-label="Remove GIF"
                                                        onClick={() => setReplySelectedGifUrl('')}
                                                    >
                                                        ✕
                                                    </button>
                                                </div>
                                            )}
                                            {replySelectedSticker && (
                                                <div className="comment-composer__preview-chip comment-composer__sticker">
                                                    <img
                                                        src={replySelectedSticker.url}
                                                        alt={`My avatar: ${replySelectedSticker.label}`}
                                                        width="56"
                                                        height="56"
                                                        style={{ display: 'block', width: '56px', height: '56px', borderRadius: '10px', objectFit: 'contain' }}
                                                    />
                                                    <button
                                                        type="button"
                                                        className="comment-composer__preview-remove"
                                                        aria-label="Remove sticker"
                                                        onClick={() => setReplySelectedSticker(null)}
                                                    >
                                                        ✕
                                                    </button>
                                                </div>
                                            )}
                                            {replyAttachedFile && (
                                                <div className="comment-composer__preview-chip">
                                                    {replyAttachedPreviewUrl ? (
                                                        <img src={replyAttachedPreviewUrl} alt={replyAttachedFile.name || 'Attached image'} className="comment-composer__preview-media" />
                                                    ) : (
                                                        <span className="comment-composer__preview-name">
                                                            <i className="bi bi-paperclip me-1" aria-hidden="true" />
                                                            {replyAttachedFile.name}
                                                        </span>
                                                    )}
                                                    <button
                                                        type="button"
                                                        className="comment-composer__preview-remove"
                                                        aria-label="Remove attachment"
                                                        onClick={() => setReplyAttachedFile(null)}
                                                    >
                                                        ✕
                                                    </button>
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    <div className="comment-composer__row">
                                        <input
                                            ref={replyInputRef}
                                            type="text"
                                            required={!hasSubmittableReplyContent}
                                            className="comment-composer__input"
                                            placeholder={`Reply to ${comment.firstName || comment.fullName || 'comment'}...`}
                                            aria-label="Write a reply"
                                            value={replyDraft}
                                            onInput={(e) => handleReplyDraftChange(e.target.value)}
                                        />
                                        <div className="comment-composer__tools">
                                            <label className="comment-composer__tool mb-0" title="Attach Photo or Video" aria-label="Attach photo or video to reply">
                                                <i className="bi bi-camera" aria-hidden="true" />
                                                <input
                                                    type="file"
                                                    accept="image/*,video/*"
                                                    style={{ display: 'none' }}
                                                    onChange={(e) => setReplyAttachedFile(e.target.files?.[0] || null)}
                                                />
                                            </label>
                                            <button
                                                type="button"
                                                className="comment-composer__tool"
                                                aria-label="Add emoji to reply"
                                                aria-expanded={replyPickerTab === 'standard' || replyPickerTab === 'living' || replyPickerTab === 'me'}
                                                title="Add Emoji"
                                                onClick={() => setReplyPickerTab((t) => (t && t !== 'gif' ? null : 'standard'))}
                                            >
                                                <i className="bi bi-emoji-smile" aria-hidden="true" />
                                            </button>
                                            <button
                                                type="button"
                                                className="comment-composer__tool"
                                                aria-label="Add GIF to reply"
                                                aria-expanded={replyPickerTab === 'gif'}
                                                title="Add Family GIF"
                                                onClick={() => setReplyPickerTab((t) => (t === 'gif' ? null : 'gif'))}
                                            >
                                                <i className="bi bi-filetype-gif" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button
                                    type="submit"
                                    className={`comment-composer__send ${hasSubmittableReplyContent ? 'is-active' : ''}`}
                                    aria-label="Send reply"
                                    title="Post reply"
                                    disabled={replySubmitting || !hasSubmittableReplyContent}
                                >
                                    {replySubmitting ? (
                                        <span className="st-spinner-border" role="status" aria-hidden="true" />
                                    ) : (
                                        <i className="bi bi-send-fill" aria-hidden="true" />
                                    )}
                                </button>
                            </form>
                        </div>
                    )}
                </div>
            </div>
        );
    };

    if (!isExpanded && comments.length === 0) {
        return null;
    }

    const visibleRoots = isExpanded ? rootComments : rootComments.slice(-2);

    return (
        <div className="comments-section pt-2" style={{ overflow: 'visible' }}>
            {isExpanded && (
                <div className="mb-3 position-relative" style={{ overflow: 'visible' }}>
                    {mentionMatches.length > 0 && (
                        <div className="mention-autocomplete shadow-md mb-2" role="listbox" aria-label="Mention family member">
                            <div className="mention-autocomplete__list">
                                {mentionMatches.map((m, idx) => (
                                    <button
                                        key={m.id || `${m.firstName}_${m.lastName}`}
                                        type="button"
                                        className={`mention-autocomplete__item d-flex align-items-center gap-2 w-100 text-start px-2 py-1 border-0 ${mentionIndex === idx ? 'mention-autocomplete__item--active' : ''}`}
                                        style={{ minHeight: '44px' }}
                                        aria-selected={mentionIndex === idx}
                                        onClick={() => insertMention(m)}
                                    >
                                        <span className="mention-autocomplete__name">@{m.firstName} {m.lastName}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {pickerTab && (
                        <EmojiGifPicker
                            initialTab={pickerTab}
                            onSelectEmoji={(em) => {
                                setDraft((prev) => `${prev}${em}`);
                                inputRef.current?.focus();
                            }}
                            onSelectLivingEmoji={(emojiId) => {
                                if (emojiId && /^[a-z0-9_]+_3d$/i.test(emojiId)) {
                                    setSelectedLivingEmojis((prev) => [...prev, emojiId]);
                                }
                                setPickerTab(null);
                                inputRef.current?.focus();
                            }}
                            onSelectMeSticker={(st) => {
                                setSelectedSticker(st);
                                setPickerTab(null);
                                inputRef.current?.focus();
                            }}
                            onSelectGif={(url) => {
                                if (isAllowedGifUrl(url)) {
                                    setSelectedGifUrl(url);
                                    setPickerTab(null);
                                }
                            }}
                            onClose={() => setPickerTab(null)}
                        />
                    )}

                    <form onSubmit={handleSubmit} className="comment-composer">
                        <input type="hidden" name="token" value={getCsrfToken(bootstrap)} />
                        <div className="comment-composer__field">
                            {(selectedGifUrl || selectedSticker || selectedLivingEmojis.length > 0 || attachedFile) && (
                                <div className="comment-composer__attachments">
                                    {selectedLivingEmojis.map((emojiId, idx) => (
                                        <div
                                            key={`${emojiId}_${idx}`}
                                            className="comment-composer__preview-chip comment-composer__sticker"
                                            data-testid="comment-living-emoji-preview"
                                        >
                                            <img
                                                src={`/public/img/emoji/${emojiId}.svg?v=20261107a`}
                                                onError={(e) => {
                                                    if (!e.currentTarget.dataset.fb) {
                                                        e.currentTarget.dataset.fb = '1';
                                                        e.currentTarget.src = `/img/emoji/${emojiId}.svg?v=20261107a`;
                                                    }
                                                }}
                                                alt={`Living emoji: ${emojiId.replace(/_3d$/i, '').replace(/_/g, ' ')}`}
                                                width="52"
                                                height="52"
                                                style={{ display: 'block', width: '52px', height: '52px', borderRadius: '10px', objectFit: 'contain' }}
                                            />
                                            <button
                                                type="button"
                                                className="comment-composer__preview-remove"
                                                aria-label="Remove living emoji"
                                                onClick={() => setSelectedLivingEmojis((prev) => prev.filter((_, i) => i !== idx))}
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    ))}

                                    {selectedGifUrl && (
                                        <div className="comment-composer__preview-chip" data-testid="comment-gif-preview">
                                            <img
                                                src={selectedGifUrl}
                                                alt="Selected GIF"
                                                className="comment-composer__preview-media"
                                            />
                                            <button
                                                type="button"
                                                className="comment-composer__preview-remove"
                                                aria-label="Remove GIF"
                                                onClick={() => setSelectedGifUrl('')}
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    )}

                                    {selectedSticker && (
                                        <div className="comment-composer__preview-chip comment-composer__sticker" data-testid="comment-sticker-preview">
                                            <img
                                                src={selectedSticker.url}
                                                alt={`My avatar: ${selectedSticker.label}`}
                                                width="64"
                                                height="64"
                                                style={{ display: 'block', width: '64px', height: '64px', borderRadius: '10px', objectFit: 'contain' }}
                                            />
                                            <button
                                                type="button"
                                                className="comment-composer__preview-remove"
                                                aria-label="Remove sticker"
                                                onClick={() => setSelectedSticker(null)}
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    )}

                                    {attachedFile && (
                                        <div className="comment-composer__preview-chip" data-testid="comment-file-preview">
                                            {attachedPreviewUrl ? (
                                                <img
                                                    src={attachedPreviewUrl}
                                                    alt={attachedFile.name || 'Attached image'}
                                                    className="comment-composer__preview-media"
                                                />
                                            ) : (
                                                <span className="comment-composer__preview-name">
                                                    <i className="bi bi-paperclip me-1" aria-hidden="true" />
                                                    {attachedFile.name}
                                                </span>
                                            )}
                                            <button
                                                type="button"
                                                className="comment-composer__preview-remove"
                                                aria-label="Remove attachment"
                                                onClick={() => setAttachedFile(null)}
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    )}
                                </div>
                            )}

                            <div className="comment-composer__row">
                                <input
                                    ref={inputRef}
                                    type="text"
                                    required={!hasSubmittableContent}
                                    className="comment-composer__input"
                                    placeholder="Write a comment..."
                                    aria-label="Write a comment"
                                    value={draft}
                                    onInput={(e) => handleDraftChange(e.target.value)}
                                    onKeyDown={handleInputKeyDown}
                                />
                                <div className="comment-composer__tools">
                                    <label
                                        className="comment-composer__tool mb-0"
                                        title="Attach Photo or Video"
                                        aria-label="Attach photo or video"
                                    >
                                        <i className="bi bi-camera" aria-hidden="true" />
                                        <input
                                            type="file"
                                            accept="image/*,video/*"
                                            style={{ display: 'none' }}
                                            onChange={(e) => setAttachedFile(e.target.files?.[0] || null)}
                                        />
                                    </label>
                                    <button
                                        type="button"
                                        className="comment-composer__tool"
                                        aria-label="Add emoji"
                                        aria-expanded={pickerTab === 'standard' || pickerTab === 'living' || pickerTab === 'me'}
                                        title="Add Emoji"
                                        onClick={() => setPickerTab((t) => (t && t !== 'gif' ? null : 'standard'))}
                                    >
                                        <i className="bi bi-emoji-smile" aria-hidden="true" />
                                    </button>
                                    <button
                                        type="button"
                                        className="comment-composer__tool"
                                        data-testid="comment-gif-btn"
                                        aria-label="Add GIF"
                                        aria-expanded={pickerTab === 'gif'}
                                        title="Add Family GIF"
                                        onClick={() => setPickerTab((t) => (t === 'gif' ? null : 'gif'))}
                                    >
                                        <i className="bi bi-filetype-gif" aria-hidden="true" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button
                            type="submit"
                            className={`comment-composer__send ${hasSubmittableContent ? 'is-active' : ''}`}
                            aria-label="Send"
                            title="Post comment"
                            disabled={submitting || !hasSubmittableContent}
                        >
                            {submitting ? (
                                <span className="st-spinner-border" role="status" aria-hidden="true" />
                            ) : (
                                <i className="bi bi-send-fill" aria-hidden="true" />
                            )}
                        </button>
                    </form>
                </div>
            )}

            <div className="d-flex flex-column gap-2">
                {visibleRoots.map((root) => {
                    const replies = repliesFor(root.comment_no);
                    const isThreadExpanded = Boolean(expandedThreads[root.comment_no]);
                    const showReplies = isThreadExpanded || replies.length <= 2;
                    const visibleReplies = showReplies ? replies : replies.slice(0, 2);
                    const hiddenReplyCount = Math.max(0, replies.length - 2);

                    return (
                        <div key={root.comment_no} className="d-flex flex-column gap-2">
                            {renderSingleComment(root, false)}
                            {visibleReplies.map((rep) => renderSingleComment(rep, true))}
                            {replies.length > 2 && (
                                <button
                                    type="button"
                                    data-testid="thread-collapse-toggle"
                                    className="btn btn-link btn-sm text-primary text-start text-decoration-none fw-semibold"
                                    style={{ marginLeft: '2.25rem', minHeight: '44px', width: 'fit-content' }}
                                    onClick={() =>
                                        setExpandedThreads((prev) => ({
                                            ...prev,
                                            [root.comment_no]: !isThreadExpanded,
                                        }))
                                    }
                                >
                                    {isThreadExpanded
                                        ? 'Collapse replies'
                                        : `View ${hiddenReplyCount} more ${hiddenReplyCount === 1 ? 'reply' : 'replies'}`}
                                </button>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
});
