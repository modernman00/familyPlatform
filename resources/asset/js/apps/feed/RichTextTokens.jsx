import { useState, useRef, useEffect, useMemo } from 'preact/hooks';
import { isEmojiOnlyMessage, tokenizeRichText } from './feedUtils';
import { applyKinshipToTokens, buildMentionContext } from './kinshipMentions';

function getKnownLivingEmojiIds() {
    if (typeof window === 'undefined') return null;
    const cfg = window.ATG_LIVING_EMOJIS;
    if (!cfg || !Array.isArray(cfg.catalog) || cfg.catalog.length === 0) return null;
    const set = new Set();
    const toneable = new Set(cfg.toneable || []);
    cfg.catalog.forEach((e) => {
        set.add(e.id);
        if (toneable.has(e.id)) {
            (cfg.tones || []).forEach((t) => set.add(e.id.replace(/_3d$/, `_${t}_3d`)));
        }
    });
    return set;
}

function getLivingEmojiTitle(emojiId) {
    const cleanId = String(emojiId || '').trim();
    const baseId = cleanId.replace(/_(medium_light|medium_dark|light|medium|dark)_3d$/i, '_3d');
    if (typeof window !== 'undefined' && Array.isArray(window.ATG_LIVING_EMOJIS?.catalog)) {
        const entry = window.ATG_LIVING_EMOJIS.catalog.find((e) => e.id === baseId || e.id === cleanId);
        if (entry?.title) return entry.title;
    }
    return baseId
        .replace(/_3d$/i, '')
        .split('_')
        .filter(Boolean)
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ') || cleanId;
}

/**
 * Declaratively renders rich post/comment tokens (3D living family emojis, Me-stickers,
 * hashtags, mentions, links) without dangerouslySetInnerHTML.
 */
export function RichTextTokens({
    text,
    arEmoji = null,
    onSelectHashtag,
    isStage = false,
    bootstrap = null,
    postTags = null,
}) {
    const [playingMap, setPlayingMap] = useState({});
    const timersRef = useRef({});

    useEffect(() => () => {
        Object.values(timersRef.current).forEach((t) => clearTimeout(t));
    }, []);

    const triggerPlay = (idx) => {
        if (timersRef.current[idx]) clearTimeout(timersRef.current[idx]);
        setPlayingMap((prev) => ({ ...prev, [idx]: true }));
        timersRef.current[idx] = setTimeout(() => {
            setPlayingMap((prev) => {
                if (!prev[idx]) return prev;
                const next = { ...prev };
                delete next[idx];
                return next;
            });
        }, 900);
    };

    const mentionContext = useMemo(
        () => buildMentionContext(bootstrap, postTags),
        [bootstrap, postTags]
    );

    const rawTokens = tokenizeRichText(text, arEmoji);
    if (rawTokens.length === 0) return null;
    const tokens = applyKinshipToTokens(rawTokens, mentionContext);

    const emojiOnly = isEmojiOnlyMessage(text, arEmoji);
    const knownIds = getKnownLivingEmojiIds();
    const liveClass = emojiOnly
        ? 'atg-emoji-container atg-live-emoji atg-live-emoji--jumbo'
        : 'atg-emoji-container atg-live-emoji';
    const stickerClass = emojiOnly
        ? 'atg-me-sticker atg-me-sticker--jumbo'
        : 'atg-me-sticker';

    return tokens.map((tok, i) => {
        if (tok.type === 'me_sticker') {
            return (
                <span
                    key={i}
                    className={playingMap[i] ? `${stickerClass} atg-live-emoji--play` : stickerClass}
                    role="button"
                    tabIndex={0}
                    data-testid="me-sticker"
                    title="Tap to replay"
                    onClick={(e) => {
                        e.stopPropagation();
                        triggerPlay(i);
                        window.AtgEmojiEngine?.tapSticker?.(e.currentTarget, tok.reaction);
                    }}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            e.currentTarget.click();
                        }
                    }}
                >
                    <img
                        src={`/api/emoji/avatar-sticker/${encodeURIComponent(tok.uid)}/${encodeURIComponent(tok.reaction)}?v=live2`}
                        loading="lazy"
                        width={emojiOnly ? 112 : 64}
                        height={emojiOnly ? 112 : 64}
                        alt={`Avatar sticker: ${tok.reaction}`}
                        onError={(e) => {
                            const wrap = e.currentTarget.closest('.atg-me-sticker');
                            if (wrap) wrap.style.display = 'none';
                        }}
                    />
                </span>
            );
        }

        if (tok.type === 'living_emoji') {
            if (knownIds && !knownIds.has(tok.emojiId)) {
                return <span key={i}>{tok.value}</span>;
            }
            return (
                <span
                    key={i}
                    className={playingMap[i] ? `${liveClass} atg-live-emoji--play` : liveClass}
                    role="button"
                    tabIndex={0}
                    data-emoji-id={tok.emojiId}
                    title="Tap to play · tap again for 3D / AR"
                    onClick={(e) => {
                        e.stopPropagation();
                        triggerPlay(i);
                        window.AtgEmojiEngine?.tapLive?.(e.currentTarget, tok.emojiId);
                    }}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            e.currentTarget.click();
                        }
                    }}
                >
                    <img
                        src={`/public/img/emoji/${tok.emojiId}.svg?v=20261107a`}
                        onError={(e) => {
                            const img = e.currentTarget;
                            const step = img.dataset.fb || '';
                            if (!step) {
                                img.dataset.fb = '1';
                                img.src = `/img/emoji/${tok.emojiId}.svg?v=20261107a`;
                            } else if (step === '1') {
                                img.dataset.fb = '2';
                                img.src = `/public/img/emoji/${tok.emojiId}.png?v=20261107a`;
                            } else if (step === '2') {
                                img.dataset.fb = '3';
                                img.src = `/img/emoji/${tok.emojiId}.png?v=20261107a`;
                            }
                        }}
                        className="atg-3d-emoji"
                        decoding="async"
                        width={emojiOnly ? 68 : 26}
                        height={emojiOnly ? 68 : 26}
                        alt={getLivingEmojiTitle(tok.emojiId)}
                    />
                </span>
            );
        }

        if (tok.type === 'hashtag') {
            return (
                <button
                    key={i}
                    type="button"
                    className="btn btn-link p-0 border-0 align-baseline fw-semibold text-decoration-underline"
                    style={{ color: isStage ? '#fde68a' : '#a31621' }}
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
                    style={{ color: isStage ? '#93c5fd' : '#a31621' }}
                >
                    {tok.label || tok.value}
                </a>
            );
        }

        if (tok.type === 'mention') {
            const label = tok.displayValue || tok.value;
            const chipTitle = tok.tooltip || `Mentioned: ${tok.value}`;
            const stageClass = isStage ? ' mention-chip--stage' : '';
            if (tok.href) {
                return (
                    <a
                        key={i}
                        href={tok.href}
                        className={`mention-chip mention-chip--link${stageClass}`}
                        title={chipTitle}
                        onClick={(e) => e.stopPropagation()}
                    >
                        {label}
                    </a>
                );
            }
            return (
                <span key={i} className={`mention-chip${stageClass}`} title={chipTitle}>
                    {label}
                </span>
            );
        }

        return <span key={i}>{tok.value}</span>;
    });
}
