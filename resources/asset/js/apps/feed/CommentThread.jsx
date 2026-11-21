import { useState, useRef } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { getCsrfToken, notifyToast } from '../shared/bootstrap';
import { HEIRLOOM_REACTIONS, getHeirloomGlassSvg } from '../../components/profilePage/heirloomGlassReactions';
import {
    canEditComment,
    canModerateComment,
    formatRelativeTime,
    getAvatarUrl,
    getInitials,
    tokenizeRichText,
} from './feedUtils';

const QUICK_EMOJIS = ['❤️', '👏', '😂', '🙏', '🎉', '💐'];

export function CommentThread({
    post,
    bootstrap,
    isExpanded,
    onMutateComments,
    onSelectHashtag,
}) {
    const currentUserId = String(bootstrap?.userId || '');
    const familyRoster = Array.isArray(bootstrap?.familyRoster) ? bootstrap.familyRoster : [];
    const comments = Array.isArray(post?.comments) ? post.comments : [];

    const [draft, setDraft] = useState('');
    const [replyingTo, setReplyingTo] = useState(null);
    const [submitting, setSubmitting] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [editText, setEditText] = useState('');
    const [savingEdit, setSavingEdit] = useState(false);
    const [expandedThreads, setExpandedThreads] = useState({});
    const [openReactId, setOpenReactId] = useState(null);
    const [attachedFile, setAttachedFile] = useState(null);
    const [showEmojis, setShowEmojis] = useState(false);
    const [mentionQuery, setMentionQuery] = useState('');
    const [brokenAvatars, setBrokenAvatars] = useState({});
    const inputRef = useRef(null);

    const rootComments = comments.filter((c) => !c.parent_comment_no);
    const repliesFor = (rootNo) =>
        comments.filter((c) => String(c.parent_comment_no) === String(rootNo));

    const handleDraftChange = (value) => {
        setDraft(value);
        const match = value.match(/@([A-Za-z0-9_.-]{1,24})$/);
        setMentionQuery(match ? match[1].toLowerCase() : '');
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
        inputRef.current?.focus();
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const text = draft.trim();
        if ((!text && !attachedFile) || submitting) return;

        const tempId = `temp_${Date.now()}_${Math.random().toString(36).slice(2, 6)}`;
        const first = bootstrap?.firstName || 'You';
        const last = bootstrap?.lastName || '';
        const optimisticComment = {
            comment_no: tempId,
            post_no: String(post.post_no),
            id: currentUserId,
            firstName: first,
            lastName: last,
            fullName: `${first} ${last}`.trim(),
            profileImg: bootstrap?.avatar || '',
            comment: text,
            parent_comment_no: replyingTo ? String(replyingTo.comment_no) : null,
            reactions: [],
            user_reaction: null,
            date_created: new Date().toISOString(),
            _optimistic: true,
        };

        const previousComments = [...comments];
        onMutateComments(post.post_no, [...previousComments, optimisticComment]);
        setDraft('');
        setMentionQuery('');
        const replyTarget = replyingTo;
        const fileToSend = attachedFile;
        setReplyingTo(null);
        setAttachedFile(null);
        setSubmitting(true);

        try {
            const formData = new FormData();
            formData.append('post_no', String(post.post_no));
            formData.append('comment', text);
            if (replyTarget?.comment_no) {
                formData.append('parent_comment_no', String(replyTarget.comment_no));
            }
            if (fileToSend) {
                formData.append('comment_media', fileToSend);
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
                    [...previousComments, { ...optimisticComment, ...serverComment, comment_no: String(serverComment.comment_no || tempId), _optimistic: false }]
                );
            }
        } catch (err) {
            onMutateComments(post.post_no, previousComments);
            setDraft(text);
            setReplyingTo(replyTarget);
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
        const nextReaction = comment.user_reaction === reactionKey ? null : reactionKey;
        onMutateComments(
            post.post_no,
            comments.map((c) => {
                if (String(c.comment_no) !== String(comment.comment_no)) return c;
                return { ...c, user_reaction: nextReaction };
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

    const renderRichComment = (text) =>
        tokenizeRichText(text).map((tok, i) => {
            if (tok.type === 'hashtag') {
                return (
                    <button
                        key={i}
                        type="button"
                        className="btn btn-link p-0 border-0 align-baseline fw-semibold text-decoration-underline"
                        style={{ color: '#a31621' }}
                        onClick={() => onSelectHashtag?.(tok.tag)}
                    >
                        {tok.value}
                    </button>
                );
            }
            if (tok.type === 'url') {
                return (
                    <a key={i} href={tok.value} target="_blank" rel="noopener noreferrer">
                        {tok.value}
                    </a>
                );
            }
            if (tok.type === 'mention') {
                return <strong key={i}>{tok.value}</strong>;
            }
            return <span key={i}>{tok.value}</span>;
        });

    const renderSingleComment = (comment, isReplyItem = false) => {
        const avatarKey = `${comment.comment_no}_${comment.id}`;
        const fullName = comment.fullName || `${comment.firstName || ''} ${comment.lastName || ''}`.trim() || 'Family Member';
        const isEditing = String(editingId) === String(comment.comment_no);
        const reactionEntries = Object.values(HEIRLOOM_REACTIONS).filter(
            (r, idx, arr) => arr.findIndex((x) => x.key === r.key) === idx
        );
        const mediaSrc = comment.gif_url || comment.media_url || '';

        return (
            <div
                key={comment.comment_no}
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
                            style={{ backgroundColor: '#f0f2f5', display: 'inline-block', maxWidth: '92%' }}
                        >
                            <div className="fw-semibold text-dark" style={{ fontSize: '0.8125rem', lineHeight: 1.2 }}>
                                {fullName}
                            </div>
                            {comment.comment && (
                                <div
                                    className="mt-1 comment-bubble-text"
                                    style={{ fontSize: '0.875rem', color: '#050505', wordBreak: 'break-word', lineHeight: 1.35 }}
                                >
                                    {renderRichComment(comment.comment)}
                                </div>
                            )}
                            {mediaSrc && (
                                <img
                                    src={mediaSrc}
                                    alt="Comment attachment"
                                    loading="lazy"
                                    className="mt-1 rounded-3 comment-gif"
                                    style={{ display: 'block', width: 'auto', height: 'auto', maxWidth: '100%', maxHeight: '220px', objectFit: 'contain' }}
                                />
                            )}
                        </div>
                    )}

                    {/* Compact Action Links Row */}
                    <div className="d-flex align-items-center flex-wrap gap-2 mt-1 ps-1" style={{ fontSize: '0.76rem' }}>
                        <span className="text-muted">
                            {comment._optimistic ? 'Sending…' : formatRelativeTime(comment.post_time || comment.date_created)}
                        </span>
                        <button
                            type="button"
                            className="btn btn-link btn-sm p-0 text-decoration-none fw-semibold text-muted"
                            style={{ fontSize: '0.76rem', minHeight: '44px' }}
                            onClick={() => setOpenReactId(openReactId === comment.comment_no ? null : comment.comment_no)}
                        >
                            {comment.user_reaction ? `Reacted (${comment.user_reaction})` : 'React'}
                        </button>
                        {!isReplyItem && (
                            <button
                                type="button"
                                className="btn btn-link btn-sm p-0 text-decoration-none fw-semibold text-muted"
                                style={{ fontSize: '0.76rem', minHeight: '44px' }}
                                onClick={() => {
                                    setReplyingTo(comment);
                                    inputRef.current?.focus();
                                }}
                            >
                                Reply
                            </button>
                        )}
                        {canEditComment(comment, currentUserId) && !isEditing && (
                            <button
                                type="button"
                                className="btn btn-link btn-sm p-0 text-decoration-none fw-semibold text-muted"
                                style={{ fontSize: '0.76rem', minHeight: '44px' }}
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
                                style={{ fontSize: '0.76rem', minHeight: '44px' }}
                                onClick={() => handleDeleteComment(comment)}
                            >
                                Delete
                            </button>
                        )}
                    </div>

                    {openReactId === comment.comment_no && (
                        <div className="d-flex flex-wrap gap-1 mt-1 p-1 rounded-pill border bg-white shadow-sm" style={{ width: 'fit-content' }} role="group" aria-label="Comment reactions">
                            {reactionEntries.map((r) => (
                                <button
                                    key={r.key}
                                    type="button"
                                    className={`btn btn-sm rounded-circle p-1 ${comment.user_reaction === r.key ? 'bg-light border border-primary' : 'border-0 bg-transparent'}`}
                                    style={{ minWidth: '44px', minHeight: '44px' }}
                                    onClick={() => handleCommentReaction(comment, r.key)}
                                    title={r.label}
                                >
                                    <span dangerouslySetInnerHTML={{ __html: getHeirloomGlassSvg(r.key, 22) }} />
                                </button>
                            ))}
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
                    {replyingTo && (
                        <div className="ps-2 mb-1 text-muted d-flex align-items-center gap-2" style={{ fontSize: '0.78rem' }}>
                            <span>Replying to <strong>{replyingTo.fullName || `${replyingTo.firstName} ${replyingTo.lastName}`}</strong></span>
                            <button
                                type="button"
                                className="btn btn-link btn-sm text-muted p-0 text-decoration-none"
                                style={{ minWidth: '44px', minHeight: '44px' }}
                                onClick={() => setReplyingTo(null)}
                                aria-label="Cancel reply"
                            >
                                ✕
                            </button>
                        </div>
                    )}

                    {mentionMatches.length > 0 && (
                        <div className="mention-autocomplete shadow-md mb-2" role="listbox" aria-label="Mention family member">
                            <div className="mention-autocomplete__list">
                                {mentionMatches.map((m) => (
                                    <button
                                        key={m.id || `${m.firstName}_${m.lastName}`}
                                        type="button"
                                        className="mention-autocomplete__item d-flex align-items-center gap-2 w-100 text-start px-2 py-1 border-0"
                                        style={{ minHeight: '44px' }}
                                        onClick={() => insertMention(m)}
                                    >
                                        <span className="mention-autocomplete__name">@{m.firstName} {m.lastName}</span>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {showEmojis && (
                        <div className="d-flex flex-wrap gap-1 mb-2">
                            {QUICK_EMOJIS.map((em) => (
                                <button
                                    key={em}
                                    type="button"
                                    className="btn btn-sm btn-light border rounded-pill"
                                    style={{ minWidth: '44px', minHeight: '44px' }}
                                    onClick={() => {
                                        setDraft((prev) => prev + em);
                                        inputRef.current?.focus();
                                    }}
                                >
                                    {em}
                                </button>
                            ))}
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="comment-composer">
                        <input type="hidden" name="token" value={getCsrfToken(bootstrap)} />
                        <div className="comment-composer__field">
                            <div className="comment-composer__row">
                                <input
                                    ref={inputRef}
                                    type="text"
                                    className="comment-composer__input"
                                    placeholder="Write a comment..."
                                    aria-label="Write a comment"
                                    value={draft}
                                    onInput={(e) => handleDraftChange(e.target.value)}
                                />
                                <label
                                    className="comment-composer__tool mb-0 d-inline-flex align-items-center justify-content-center"
                                    style={{ cursor: 'pointer', minWidth: '44px', minHeight: '44px' }}
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
                                    style={{ minWidth: '44px', minHeight: '44px' }}
                                    aria-label="Toggle emoji picker"
                                    title="Add Emoji"
                                    onClick={() => setShowEmojis((v) => !v)}
                                >
                                    <i className="bi bi-emoji-smile" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                        <button
                            type="submit"
                            className={`comment-composer__send ${(draft.trim() || attachedFile) ? 'is-active' : ''}`}
                            aria-label="Send"
                            title="Post comment"
                            disabled={submitting || (!draft.trim() && !attachedFile)}
                            style={{ minWidth: '44px', minHeight: '44px' }}
                        >
                            {submitting ? (
                                <span className="st-spinner-border" role="status" aria-hidden="true" />
                            ) : (
                                <i className="bi bi-send-fill" aria-hidden="true" />
                            )}
                        </button>
                    </form>

                    {attachedFile && (
                        <div className="mt-1 small text-muted">
                            Attached: {attachedFile.name}{' '}
                            <button
                                type="button"
                                className="btn btn-link btn-sm p-0 text-danger"
                                style={{ minHeight: '44px' }}
                                onClick={() => setAttachedFile(null)}
                            >
                                Remove
                            </button>
                        </div>
                    )}
                </div>
            )}

            <div className="d-flex flex-column gap-2">
                {visibleRoots.map((root) => {
                    const replies = repliesFor(root.comment_no);
                    const showReplies = Boolean(expandedThreads[root.comment_no]) || replies.length <= 2;
                    const visibleReplies = showReplies ? replies : replies.slice(0, 1);

                    return (
                        <div key={root.comment_no} className="d-flex flex-column gap-2">
                            {renderSingleComment(root, false)}
                            {visibleReplies.map((rep) => renderSingleComment(rep, true))}
                            {replies.length > 2 && !expandedThreads[root.comment_no] && (
                                <button
                                    type="button"
                                    className="btn btn-link btn-sm text-primary text-start text-decoration-none fw-semibold"
                                    style={{ marginLeft: '2.25rem', minHeight: '44px', width: 'fit-content' }}
                                    onClick={() =>
                                        setExpandedThreads((prev) => ({ ...prev, [root.comment_no]: true }))
                                    }
                                >
                                    View {replies.length - 1} more replies
                                </button>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
