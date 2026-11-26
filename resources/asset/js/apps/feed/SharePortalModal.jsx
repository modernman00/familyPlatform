import { useState, useEffect } from 'preact/hooks';
import { apiFetch } from '../shared/apiClient';
import { notifyToast } from '../shared/bootstrap';
import { Portal } from '../shared/Portal';
import { getAvatarUrl, getPostImageUrl, isOwnPost, stripStyleToken } from './feedUtils';

function cleanRawCaption(post) {
    let raw = stripStyleToken(post?.displayMessage || post?.postMessage || '').trim();
    raw = raw
        .replace(
            /(?:https?:\/\/[^\s]+|\/resources\/(?:videos|images|profile)\/[^\s]+|post_vid_[^\s]+|[a-f0-9_]+_Sho_[^\s]+|\.(?:webm|mp4|mov|jpg|jpeg|png|gif|webp|avif)(?:#[^\s]*)?)/gi,
            ''
        )
        .replace(/\s+/g, ' ')
        .trim();
    return raw;
}

export function formatExternalAuthorName(post) {
    if (!post) return 'A relative';
    const preference = String(post.external_share_name || '').toLowerCase();
    const handle = String(post.app_handle || post.handle || '').replace(/^@+/, '').trim();
    if (preference === 'anonymous') {
        return 'A family member';
    }
    if (preference === 'handle' && handle) {
        return `@${handle}`;
    }
    const f = (post.firstName || '').trim();
    const l = (post.lastName || '').trim();
    const cleanFirst = f ? f.charAt(0).toUpperCase() + f.slice(1).toLowerCase() : '';
    const cleanLast = l ? l.charAt(0).toUpperCase() + l.slice(1).toLowerCase() : '';

    if (preference === 'first_only' && cleanFirst) {
        return cleanFirst;
    }
    if (preference === 'full_name' && (cleanFirst || cleanLast)) {
        return `${cleanFirst} ${cleanLast}`.trim();
    }
    // Default / first_initial:
    if (cleanFirst && cleanLast) {
        return `${cleanFirst} ${cleanLast.charAt(0)}.`;
    }
    if (cleanFirst) {
        return cleanFirst;
    }
    // Fallback: parse fullName
    const full = (post.fullName || '').trim();
    if (!full || full.toLowerCase() === 'family member' || full.toLowerCase() === 'a relative') {
        return handle ? `@${handle}` : 'A family member';
    }
    const parts = full.split(/\s+/);
    if (parts.length > 1) {
        const first = parts[0].charAt(0).toUpperCase() + parts[0].slice(1).toLowerCase();
        const initial = parts[parts.length - 1].charAt(0).toUpperCase();
        return `${first} ${initial}.`;
    }
    return parts[0].charAt(0).toUpperCase() + parts[0].slice(1).toLowerCase();
}

export function getCleanShareText(post) {
    if (!post) return 'Check out this memory on FamilyPlatform';
    const author = formatExternalAuthorName(post);
    const raw = cleanRawCaption(post);

    if (raw.length > 0) {
        const stripped = raw.replace(/^["'“]|["'”]$/g, '').trim();
        const snippet = stripped.length > 140 ? `${stripped.slice(0, 137)}…` : stripped;
        return `“${snippet}” — ${author} on FamilyPlatform`;
    }

    const hasVideo = Boolean(
        post.video ||
            (Array.isArray(post.videos) && post.videos.length > 0) ||
            (typeof post.postMessage === 'string' &&
                (/\/resources\/videos\//i.test(post.postMessage) ||
                    /\.(?:webm|mp4)/i.test(post.postMessage)))
    );

    if (hasVideo) {
        return `Watch ${author}’s video on FamilyPlatform`;
    }
    if (Array.isArray(post.images) && post.images.length > 0) {
        return `View ${author}’s photo on FamilyPlatform`;
    }
    if (post.voice_capsule_url) {
        return `Listen to ${author}’s voice capsule on FamilyPlatform`;
    }
    return `${author} shared a post on FamilyPlatform`;
}

export function getWhatsAppShareText(post, shareUrl) {
    const author = formatExternalAuthorName(post);
    const raw = cleanRawCaption(post);
    const hasVideo = Boolean(
        post && (post.video || (Array.isArray(post.videos) && post.videos.length > 0))
    );
    const hasPhoto = Boolean(post && Array.isArray(post.images) && post.images.length > 0);
    const kind = hasVideo
        ? ['🎬', 'a video']
        : hasPhoto
            ? ['📸', 'a photo']
            : post?.voice_capsule_url
                ? ['🎙️', 'a voice note']
                : ['💬', 'an update'];

    const lines = [`${kind[0]} *${author}* shared ${kind[1]} on FamilyPlatform`];
    if (raw) {
        const teaser =
            raw.length > 120 ? `${raw.slice(0, 117).replace(/\s+\S*$/, '')}…` : raw;
        lines.push('', `_“${teaser.replace(/[_*]/g, '')}”_`);
    }
    lines.push('', 'Tap to view in our private family hub 👇', shareUrl);
    return lines.join('\n');
}

export function SharePortalModal({ isOpen, post, bootstrap, onUpdatePost, onClose }) {
    const [copied, setCopied] = useState(false);
    const [reposting, setReposting] = useState(false);
    const [togglingPerm, setTogglingPerm] = useState(false);

    useEffect(() => {
        if (!isOpen) {
            setCopied(false);
            setReposting(false);
            return undefined;
        }
        const onKey = (e) => {
            if (e.key === 'Escape') onClose?.();
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [isOpen, onClose]);

    if (!isOpen || !post || !post.post_no) return null;

    const currentUserId = String(bootstrap?.userId || '');
    const ownPost = isOwnPost(post, currentUserId);
    const allowSharing = post.allow_sharing !== false && post.allow_sharing !== 0 && post.allow_sharing !== '0';

    const recordShareAndNotifyAuthor = (channel = 'link') => {
        if (!post?.post_no) return;
        apiFetch(
            `/api/posts/${encodeURIComponent(post.post_no)}/share`,
            {
                method: 'POST',
                body: { channel },
            },
            bootstrap
        ).catch(() => {});
    };

    const handleRepostToFeed = async () => {
        if (reposting) return;
        setReposting(true);
        try {
            const res = await apiFetch(
                `/post/${encodeURIComponent(post.post_no)}/repost`,
                {
                    method: 'POST',
                    body: { reflection_message: '' },
                    timeoutMs: 30000,
                },
                bootstrap
            );
            notifyToast(
                ownPost
                    ? 'Reposted to your family feed!'
                    : (res?.author_notified ? `Shared to family feed (${author} notified via Web Push)!` : 'Shared to your family feed!'),
                'success'
            );
            onClose?.();
        } catch (err) {
            notifyToast(err?.message || 'Unable to share to family feed.', 'error');
        } finally {
            setReposting(false);
        }
    };

    const handleToggleSharePermission = async () => {
        if (!ownPost || togglingPerm) return;
        const nextAllow = !allowSharing;
        setTogglingPerm(true);
        onUpdatePost?.(post.post_no, { allow_sharing: nextAllow });
        try {
            await apiFetch(
                `/post/${encodeURIComponent(post.post_no)}`,
                {
                    method: 'PUT',
                    body: { allow_sharing: nextAllow },
                    timeoutMs: 30000,
                },
                bootstrap
            );
            notifyToast(
                nextAllow
                    ? 'Family members can share this post (you will be notified)'
                    : 'Sharing locked — only you can share this post',
                'success'
            );
        } catch (err) {
            onUpdatePost?.(post.post_no, { allow_sharing: !nextAllow });
            notifyToast(err?.message || 'Could not update sharing setting.', 'error');
        } finally {
            setTogglingPerm(false);
        }
    };

    const origin =
        typeof window !== 'undefined' && window.location?.origin
            ? window.location.origin
            : 'https://familyplatform.co.uk';
    const shareUrl = `${origin}/post/${encodeURIComponent(post.post_no)}`;
    const author = formatExternalAuthorName(post);

    const hasVideo = Boolean(
        post.video ||
            (Array.isArray(post.videos) && post.videos.length > 0) ||
            (typeof post.postMessage === 'string' &&
                (/\/resources\/videos\//i.test(post.postMessage) ||
                    /\.(?:webm|mp4)/i.test(post.postMessage)))
    );
    const hasPhoto = Boolean(Array.isArray(post.images) && post.images.length > 0);

    const shareTitle = hasVideo
        ? `${author}'s Video`
        : hasPhoto
            ? `${author}'s Photo`
            : `${author}'s Post`;

    const shareText = getCleanShareText(post);
    const waText = getWhatsAppShareText(post, shareUrl);

    let previewImg = getAvatarUrl(post);
    if (hasPhoto && post.images[0]) {
        previewImg = getPostImageUrl(post.images[0]) || post.images[0];
    } else if (post.link_preview?.image) {
        previewImg = String(post.link_preview.image).replace(/&amp;/g, '&').trim();
    } else if (Array.isArray(post.videos) && post.videos[0]?.thumbnailUrl) {
        previewImg = post.videos[0].thumbnailUrl;
    }

    const whatsappUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(waText)}`;
    const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
    const xUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`;
    const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`;
    const telegramUrl = `https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}`;
    const smsUrl = `sms:?&body=${encodeURIComponent(`${shareText} ${shareUrl}`)}`;
    const emailUrl = `mailto:?subject=${encodeURIComponent(shareTitle)}&body=${encodeURIComponent(`${shareText}\n\n${shareUrl}`)}`;

    const handleCopyLink = async () => {
        recordShareAndNotifyAuthor('copy');
        try {
            const toastMsg = ownPost ? 'Post link copied to clipboard!' : `Post link copied (${author} notified)!`;
            if (typeof window !== 'undefined' && typeof window.$clipboard === 'function') {
                window.$clipboard(shareUrl, toastMsg);
            } else if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(shareUrl);
                notifyToast(toastMsg, 'success');
            } else {
                window.prompt('Copy this share link:', shareUrl);
            }
            setCopied(true);
            setTimeout(() => setCopied(false), 2500);
        } catch {
            window.prompt('Copy this share link:', shareUrl);
        }
    };

    const handleSystemShare = async () => {
        recordShareAndNotifyAuthor('system');
        if (typeof navigator !== 'undefined' && typeof navigator.share === 'function') {
            try {
                await navigator.share({
                    title: shareTitle,
                    text: shareText,
                    url: shareUrl,
                });
                return;
            } catch (err) {
                if (err && err.name === 'AbortError') return;
            }
        }
        await handleCopyLink();
    };

    const socialChannels = [
        {
            key: 'whatsapp',
            label: 'WhatsApp',
            href: whatsappUrl,
            bg: '#15803d',
            icon: <i className="bi bi-whatsapp" style={{ fontSize: '1.3rem' }} aria-hidden="true" />,
        },
        {
            key: 'facebook',
            label: 'Facebook',
            href: facebookUrl,
            bg: '#1877f2',
            icon: <i className="bi bi-facebook" style={{ fontSize: '1.3rem' }} aria-hidden="true" />,
        },
        {
            key: 'x',
            label: 'X / Twitter',
            href: xUrl,
            bg: '#0f172a',
            icon: (
                <svg
                    viewBox="0 0 24 24"
                    style={{ width: '1.15rem', height: '1.15rem', fill: '#ffffff', display: 'inline-block' }}
                    aria-hidden="true"
                >
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                </svg>
            ),
        },
        {
            key: 'linkedin',
            label: 'LinkedIn',
            href: linkedinUrl,
            bg: '#0a66c2',
            icon: <i className="bi bi-linkedin" style={{ fontSize: '1.25rem' }} aria-hidden="true" />,
        },
        {
            key: 'telegram',
            label: 'Telegram',
            href: telegramUrl,
            bg: '#0284c7',
            icon: <i className="bi bi-telegram" style={{ fontSize: '1.3rem' }} aria-hidden="true" />,
        },
        {
            key: 'sms',
            label: 'Messages',
            href: smsUrl,
            bg: '#059669',
            sameTab: true,
            icon: <i className="bi bi-chat-dots-fill" style={{ fontSize: '1.25rem' }} aria-hidden="true" />,
        },
        {
            key: 'email',
            label: 'Email',
            href: emailUrl,
            bg: '#475569',
            sameTab: true,
            icon: <i className="bi bi-envelope-fill" style={{ fontSize: '1.2rem' }} aria-hidden="true" />,
        },
    ];

    return (
        <Portal>
            <div
                className="share-portal-modal-overlay"
                data-testid="share-portal-modal"
                style={{
                    position: 'fixed',
                    inset: 0,
                    zIndex: 10550,
                    background: 'rgba(15, 23, 42, 0.72)',
                    backdropFilter: 'blur(8px)',
                    WebkitBackdropFilter: 'blur(8px)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    padding: '16px',
                }}
                role="dialog"
                aria-modal="true"
                aria-labelledby="sharePortalModalTitle"
                onClick={(e) => {
                    if (e.target === e.currentTarget) onClose?.();
                }}
            >
                <div
                    className="card border-0 shadow-lg rounded-4 w-100 overflow-hidden"
                    style={{
                        maxWidth: '520px',
                        background: 'var(--card-bg, #ffffff)',
                        color: 'var(--text-main, #0f172a)',
                        border: '1px solid var(--border-subtle, rgba(226, 232, 240, 0.8))',
                    }}
                    onClick={(e) => e.stopPropagation()}
                >
                    {/* Modal Header */}
                    <div className="d-flex align-items-center justify-content-between p-3 pb-2 border-bottom">
                        <div className="d-flex align-items-center gap-2">
                            <div
                                className="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style={{
                                    width: '40px',
                                    height: '40px',
                                    background: 'rgba(21, 128, 61, 0.14)',
                                    color: '#15803d',
                                }}
                            >
                                <i
                                    className="bi bi-send-fill"
                                    style={{ fontSize: '1.1rem', transform: 'rotate(-15deg)', display: 'inline-block' }}
                                    aria-hidden="true"
                                />
                            </div>
                            <div>
                                <h2
                                    className="h5 mb-0 fw-bold"
                                    id="sharePortalModalTitle"
                                    style={{ color: 'var(--text-main, #0f172a)', fontSize: '1.1rem' }}
                                >
                                    Share to WhatsApp &amp; Social Media
                                </h2>
                                <div className="text-muted" style={{ fontSize: '0.76rem' }}>
                                    Includes rich preview card for family &amp; friends
                                </div>
                            </div>
                        </div>
                        <button
                            type="button"
                            className="btn btn-sm btn-light rounded-circle d-inline-flex align-items-center justify-content-center"
                            aria-label="Close share dialog"
                            data-testid="close-share-portal"
                            style={{ minWidth: '44px', minHeight: '44px', fontWeight: 700 }}
                            onClick={onClose}
                        >
                            ✕
                        </button>
                    </div>

                    {/* Modal Body */}
                    <div className="p-3 p-sm-4 pt-3">
                        {/* 1. Rich Inset Preview Card */}
                        <div
                            className="p-3 mb-3 rounded-3 d-flex gap-3 align-items-center border"
                            style={{
                                background: 'var(--surface-secondary, rgba(148, 163, 184, 0.1))',
                                borderColor: 'var(--border-subtle, rgba(148, 163, 184, 0.25))',
                            }}
                        >
                            <img
                                src={previewImg}
                                alt="Memory preview"
                                className="rounded-3 flex-shrink-0"
                                style={{
                                    width: '64px',
                                    height: '64px',
                                    objectFit: 'cover',
                                    background: '#1e293b',
                                    border: '1px solid rgba(0,0,0,0.08)',
                                }}
                                onError={(e) => {
                                    e.currentTarget.onerror = null;
                                    e.currentTarget.src = '/resources/images/profile/avatarM.png';
                                }}
                            />
                            <div className="overflow-hidden flex-grow-1 min-w-0">
                                <h3
                                    className="h6 fw-bold mb-1 text-truncate"
                                    style={{ fontSize: '0.95rem', color: 'var(--text-main, #0f172a)' }}
                                    title={shareTitle}
                                >
                                    {shareTitle}
                                </h3>
                                <p
                                    className="text-muted small mb-0"
                                    style={{
                                        fontSize: '0.82rem',
                                        lineHeight: 1.4,
                                        display: '-webkit-box',
                                        WebkitLineClamp: 2,
                                        WebkitBoxOrient: 'vertical',
                                        overflow: 'hidden',
                                    }}
                                >
                                    {shareText}
                                </p>
                            </div>
                        </div>

                        {/* Author Share Permission Control or Relative Web Push Notification Notice */}
                        {ownPost ? (
                            <div
                                className="p-2.5 mb-3 rounded-3 d-flex align-items-center justify-content-between gap-2 border"
                                style={{
                                    background: allowSharing ? 'rgba(16, 185, 129, 0.08)' : 'rgba(245, 158, 11, 0.1)',
                                    borderColor: allowSharing ? 'rgba(16, 185, 129, 0.3)' : 'rgba(245, 158, 11, 0.35)',
                                }}
                                data-testid="share-portal-author-permission-bar"
                            >
                                <div className="d-flex align-items-center gap-2 min-w-0">
                                    <i
                                        className={`bi ${allowSharing ? 'bi-bell-fill text-success' : 'bi-lock-fill text-warning'}`}
                                        aria-hidden="true"
                                    />
                                    <div className="small">
                                        <div className="fw-bold" style={{ fontSize: '0.82rem', color: 'var(--text-main, #0f172a)' }}>
                                            {allowSharing ? 'Family sharing enabled' : 'Sharing locked to you'}
                                        </div>
                                        <div className="text-muted" style={{ fontSize: '0.74rem' }}>
                                            {allowSharing
                                                ? 'Relatives can share & you receive a Web Push alert'
                                                : 'Other family members cannot share this post'}
                                        </div>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    className={`btn btn-sm rounded-pill px-3 fw-semibold ${allowSharing ? 'btn-outline-secondary' : 'btn-success text-white'}`}
                                    style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0, fontSize: '0.78rem' }}
                                    disabled={togglingPerm}
                                    data-spin="true"
                                    data-testid="share-portal-toggle-permission-btn"
                                    onClick={handleToggleSharePermission}
                                >
                                    {togglingPerm ? 'Saving…' : (allowSharing ? 'Lock Sharing' : 'Allow Sharing')}
                                </button>
                            </div>
                        ) : (
                            <div
                                className="px-3 py-2 mb-3 rounded-3 d-flex align-items-center gap-2 border"
                                style={{
                                    background: 'rgba(14, 165, 233, 0.08)',
                                    borderColor: 'rgba(14, 165, 233, 0.25)',
                                    fontSize: '0.78rem',
                                }}
                                data-testid="share-portal-author-notify-notice"
                            >
                                <i className="bi bi-bell-fill text-primary" aria-hidden="true" />
                                <span>
                                    <strong>{author}</strong> allows sharing on this post and will receive a Web Push notification when you share it.
                                </span>
                            </div>
                        )}

                        {/* 2. Primary WhatsApp & Family Feed Repost Actions */}
                        <div className="d-flex flex-column gap-2 mb-3">
                            <a
                                href={whatsappUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                data-testid="share-portal-whatsapp-cta"
                                onClick={() => recordShareAndNotifyAuthor('whatsapp')}
                                className="btn w-100 py-2 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 text-white text-decoration-none shadow-sm"
                                style={{
                                    backgroundColor: '#15803d',
                                    border: 'none',
                                    minHeight: '48px',
                                    fontSize: '0.96rem',
                                    whiteSpace: 'nowrap',
                                    flexShrink: 0,
                                }}
                            >
                                <i className="bi bi-whatsapp" style={{ fontSize: '1.2rem' }} aria-hidden="true" />
                                <span>Share via WhatsApp</span>
                            </a>

                            <button
                                type="button"
                                data-testid="share-portal-repost-feed-btn"
                                data-spin="true"
                                disabled={reposting}
                                onClick={handleRepostToFeed}
                                className="btn btn-outline-primary w-100 py-2 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2"
                                style={{
                                    minHeight: '46px',
                                    fontSize: '0.9rem',
                                    whiteSpace: 'nowrap',
                                    flexShrink: 0,
                                }}
                            >
                                {reposting ? (
                                    <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true" />
                                ) : (
                                    <i className="bi bi-arrow-repeat" style={{ fontSize: '1.1rem' }} aria-hidden="true" />
                                )}
                                <span>{reposting ? 'Sharing to Family Feed…' : 'Repost to Family Feed'}</span>
                            </button>
                        </div>

                        {/* 3. Direct Social Media Channels Grid */}
                        <div className="text-muted small fw-semibold mb-2" style={{ fontSize: '0.78rem' }}>
                            Or share directly to social media &amp; messaging apps:
                        </div>

                        <div
                            className="mb-3"
                            style={{
                                display: 'grid',
                                gridTemplateColumns: 'repeat(4, minmax(0, 1fr))',
                                gap: '10px',
                            }}
                        >
                            {socialChannels.map((ch) => (
                                <a
                                    key={ch.key}
                                    href={ch.href}
                                    target={ch.sameTab ? undefined : '_blank'}
                                    rel={ch.sameTab ? undefined : 'noopener noreferrer'}
                                    data-testid={`share-channel-${ch.key}`}
                                    onClick={() => recordShareAndNotifyAuthor(ch.key)}
                                    className="btn p-2 rounded-3 d-flex flex-column align-items-center justify-content-center gap-1 text-white text-decoration-none shadow-xs"
                                    style={{
                                        background: ch.bg,
                                        minHeight: '64px',
                                        minWidth: '44px',
                                        border: '1px solid rgba(255,255,255,0.14)',
                                        whiteSpace: 'nowrap',
                                        flexShrink: 0,
                                    }}
                                    title={`Share on ${ch.label}`}
                                    aria-label={`Share on ${ch.label}`}
                                >
                                    {ch.icon}
                                    <span style={{ fontSize: '0.72rem', fontWeight: 600 }}>{ch.label}</span>
                                </a>
                            ))}

                            {/* Device / System Share Sheet button */}
                            <button
                                type="button"
                                data-testid="share-channel-system"
                                className="btn p-2 rounded-3 d-flex flex-column align-items-center justify-content-center gap-1 text-white shadow-xs"
                                style={{
                                    background: '#1d4ed8',
                                    minHeight: '64px',
                                    minWidth: '44px',
                                    border: '1px solid rgba(255,255,255,0.14)',
                                    whiteSpace: 'nowrap',
                                    flexShrink: 0,
                                }}
                                onClick={handleSystemShare}
                                title="More device share options"
                                aria-label="Share via System"
                            >
                                <i className="bi bi-box-arrow-up" style={{ fontSize: '1.25rem' }} aria-hidden="true" />
                                <span style={{ fontSize: '0.72rem', fontWeight: 600 }}>More Apps</span>
                            </button>
                        </div>

                        {/* 4. Bottom Read-Only URL Field with Attached Copy Button */}
                        <div className="d-flex align-items-center gap-2">
                            <input
                                type="text"
                                className="form-control form-control-sm rounded-3"
                                readOnly
                                value={shareUrl}
                                aria-label="Memory share URL"
                                style={{
                                    fontSize: '0.85rem',
                                    minHeight: '44px',
                                    background: 'var(--surface-secondary, rgba(148, 163, 184, 0.12))',
                                    color: 'var(--text-main, #334155)',
                                }}
                            />
                            <button
                                className="btn btn-dark btn-sm fw-bold rounded-3 d-inline-flex align-items-center justify-content-center gap-1 px-3"
                                type="button"
                                data-testid="share-portal-copy-btn"
                                style={{
                                    minHeight: '44px',
                                    minWidth: '96px',
                                    whiteSpace: 'nowrap',
                                    flexShrink: 0,
                                }}
                                onClick={handleCopyLink}
                            >
                                <i
                                    className={`bi ${copied ? 'bi-check-circle-fill text-success' : 'bi-copy'}`}
                                    aria-hidden="true"
                                />
                                <span>{copied ? 'Copied!' : 'Copy Link'}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Portal>
    );
}
