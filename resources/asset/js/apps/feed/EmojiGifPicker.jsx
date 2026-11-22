import { useState, useEffect, useMemo } from 'preact/hooks';
import {
    FAMILY_GIFS,
    GIF_CATEGORIES,
    isAllowedGifUrl,
} from '../../components/profilePage/gifCatalog';

const SKIN_TONES = [
    { tone: 0, swatch: '🖐️', label: 'Default' },
    { tone: 1, swatch: '🖐🏻', label: 'Light' },
    { tone: 2, swatch: '🖐🏼', label: 'Medium-Light' },
    { tone: 3, swatch: '🖐🏽', label: 'Medium' },
    { tone: 4, swatch: '🖐🏾', label: 'Medium-Dark' },
    { tone: 5, swatch: '🖐🏿', label: 'Dark' },
];

const DEFAULT_STANDARD_EMOJIS = [
    '❤️', '😂', '👏', '🙏', '🎉', '💐', '😍', '🥰',
    '😊', '🙌', '🥳', '✨', '🔥', '👍', '🤝', '🫶',
    '😢', '😮', '🤩', '💯', '🏡', '🎂', '👶', '👨‍👩‍👧‍👦',
];

const SKIN_KEY = 'preferredEmojiSkinTone';

function readStoredSkinTone() {
    try {
        const val = parseInt(localStorage.getItem(SKIN_KEY) || '0', 10);
        return Number.isNaN(val) || val < 0 || val > 5 ? 0 : val;
    } catch {
        return 0;
    }
}

/**
 * Shared 4-tab Emoji & GIF Picker (Standard + Skin Tones, ✨ Living 3D Emojis,
 * 🙋 Me-Stickers, and 🎬 Family GIFs). Dynamically imported to keep initial bundle lean.
 */
export function EmojiGifPicker({
    initialTab = 'standard',
    onSelectEmoji,
    onSelectLivingEmoji,
    onSelectMeSticker,
    onSelectGif,
    onClose,
}) {
    const [activeTab, setActiveTab] = useState(initialTab);
    const [skinTone, setSkinTone] = useState(readStoredSkinTone);
    const [searchQuery, setSearchQuery] = useState('');
    const [emojiDb, setEmojiDb] = useState(null);
    const [meStickers, setMeStickers] = useState(null);
    const [meError, setMeError] = useState(false);
    const [gifCategory, setGifCategory] = useState('all');
    const [gifSearch, setGifSearch] = useState('');

    useEffect(() => {
        setActiveTab(initialTab);
    }, [initialTab]);

    // Async-load full emojibase-data only when Standard tab is active
    useEffect(() => {
        if (activeTab !== 'standard' || emojiDb) return;
        let cancelled = false;
        import('emojibase-data/en/data.json')
            .then((mod) => {
                if (!cancelled && Array.isArray(mod?.default || mod)) {
                    setEmojiDb(mod.default || mod);
                }
            })
            .catch(() => {
                // Keep DEFAULT_STANDARD_EMOJIS fallback
            });
        return () => {
            cancelled = true;
        };
    }, [activeTab, emojiDb]);

    // Load Me-stickers on demand when Me tab is opened
    useEffect(() => {
        if (activeTab !== 'me' || meStickers !== null) return;
        const loader = window.AtgEmojiEngine?.getMeStickers;
        if (typeof loader !== 'function') {
            setMeStickers({ available: false, stickers: [] });
            return;
        }
        setMeError(false);
        loader()
            .then((res) => setMeStickers(res || { available: false, stickers: [] }))
            .catch(() => setMeError(true));
    }, [activeTab, meStickers]);

    const handleSetSkinTone = (tone) => {
        setSkinTone(tone);
        try {
            localStorage.setItem(SKIN_KEY, String(tone));
        } catch {
            // ignore storage errors
        }
    };

    const standardEmojis = useMemo(() => {
        if (!Array.isArray(emojiDb)) return DEFAULT_STANDARD_EMOJIS;
        const q = searchQuery.trim().toLowerCase();
        const filtered = q
            ? emojiDb.filter((e) =>
                (e.label && e.label.toLowerCase().includes(q)) ||
                (Array.isArray(e.tags) && e.tags.some((t) => t.toLowerCase().includes(q)))
            )
            : emojiDb.filter((e) => e.group === 0 || e.group === 1 || e.subgroup === 14);
        return filtered.slice(0, 72).map((item) => {
            if (skinTone > 0 && Array.isArray(item.skins)) {
                const match = item.skins.find((s) => s.tone === skinTone);
                if (match?.emoji) return match.emoji;
            }
            return item.emoji;
        }).filter(Boolean);
    }, [emojiDb, searchQuery, skinTone]);

    const livingCatalog = useMemo(() => {
        const cfg = typeof window !== 'undefined' ? window.ATG_LIVING_EMOJIS : null;
        if (!cfg || !Array.isArray(cfg.catalog)) return [];
        const toneKey = (cfg.tones || [])[skinTone - 1] || null;
        const toneable = new Set(cfg.toneable || []);
        return cfg.catalog.map((e) => ({
            id: toneKey && toneable.has(e.id) ? e.id.replace(/_3d$/, `_${toneKey}_3d`) : e.id,
            title: e.title || e.id,
        }));
    }, [skinTone]);

    const filteredGifs = useMemo(() => {
        const q = gifSearch.trim().toLowerCase();
        return FAMILY_GIFS.filter((g) => {
            if (!isAllowedGifUrl(g.url)) return false;
            if (gifCategory !== 'all' && g.cat !== gifCategory) return false;
            if (q && !g.title.toLowerCase().includes(q)) return false;
            return true;
        });
    }, [gifCategory, gifSearch]);

    return (
        <div
            className="comment-picker p-2 rounded-3 border shadow-sm mb-2"
            role="dialog"
            aria-label="Emoji and GIF picker"
            data-testid="emoji-gif-picker"
            onClick={(e) => e.stopPropagation()}
        >
            {/* Header Tabs + Close */}
            <div className="d-flex align-items-center justify-content-between gap-2 pb-2 mb-2 border-bottom flex-wrap">
                <div className="comment-emoji-tabs d-flex flex-wrap gap-1" role="tablist" aria-label="Expression type">
                    <button
                        type="button"
                        role="tab"
                        aria-selected={activeTab === 'standard'}
                        data-testid="comment-emoji-tab-standard"
                        className={`comment-emoji-tab btn btn-sm rounded-pill ${activeTab === 'standard' ? 'active btn-dark text-white' : 'btn-light'}`}
                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                        onClick={() => setActiveTab('standard')}
                    >
                        😀 Standard
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={activeTab === 'living'}
                        data-testid="comment-emoji-tab-living"
                        className={`comment-emoji-tab btn btn-sm rounded-pill ${activeTab === 'living' ? 'active btn-dark text-white' : 'btn-light'}`}
                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                        onClick={() => setActiveTab('living')}
                    >
                        ✨ Living
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={activeTab === 'me'}
                        data-testid="comment-emoji-tab-me"
                        className={`comment-emoji-tab btn btn-sm rounded-pill ${activeTab === 'me' ? 'active btn-dark text-white' : 'btn-light'}`}
                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                        onClick={() => setActiveTab('me')}
                    >
                        🙋 Me
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={activeTab === 'gif'}
                        data-testid="comment-emoji-tab-gif"
                        className={`comment-emoji-tab btn btn-sm rounded-pill ${activeTab === 'gif' ? 'active btn-dark text-white' : 'btn-light'}`}
                        style={{ minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                        onClick={() => setActiveTab('gif')}
                    >
                        🎬 GIFs
                    </button>
                </div>

                {onClose && (
                    <button
                        type="button"
                        className="btn btn-sm btn-light rounded-circle"
                        style={{ minWidth: '44px', minHeight: '44px' }}
                        aria-label="Close picker"
                        onClick={onClose}
                    >
                        ✕
                    </button>
                )}
            </div>

            {/* Skin Tone Selector (Standard & Living) */}
            {(activeTab === 'standard' || activeTab === 'living') && (
                <div className="d-flex align-items-center justify-content-between px-2 py-1 mb-2 bg-light rounded" role="toolbar" aria-label="Skin tone selector">
                    <span className="text-muted fw-semibold" style={{ fontSize: '0.75rem' }}>Skin tone:</span>
                    <div className="d-flex align-items-center gap-1">
                        {SKIN_TONES.map((st) => (
                            <button
                                key={st.tone}
                                type="button"
                                className={`btn btn-sm p-0 rounded-circle ${skinTone === st.tone ? 'border border-primary shadow-sm bg-white' : 'border-0 bg-transparent opacity-75'}`}
                                style={{ minWidth: '44px', minHeight: '44px', fontSize: '1.1rem' }}
                                title={st.label}
                                aria-label={st.label}
                                onClick={() => handleSetSkinTone(st.tone)}
                            >
                                {st.swatch}
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {/* Tab 1: Standard Emojis */}
            {activeTab === 'standard' && (
                <div data-testid="comment-emoji-picker">
                    <input
                        type="search"
                        className="form-control form-control-sm mb-2"
                        style={{ minHeight: '44px' }}
                        placeholder="Search emojis (e.g. smile, heart, clap)…"
                        aria-label="Search emojis"
                        value={searchQuery}
                        onInput={(e) => setSearchQuery(e.target.value)}
                    />
                    <div className="comment-emoji-grid d-flex flex-wrap gap-1 overflow-y-auto" style={{ maxHeight: '200px' }}>
                        {standardEmojis.map((em, idx) => (
                            <button
                                key={`${em}_${idx}`}
                                type="button"
                                className="comment-emoji-btn btn btn-light border-0 rounded-3 d-inline-flex align-items-center justify-content-center"
                                style={{ minWidth: '44px', minHeight: '44px', fontSize: '1.35rem', lineHeight: 1 }}
                                aria-label={`Insert ${em}`}
                                onClick={() => onSelectEmoji?.(em)}
                            >
                                {em}
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {/* Tab 2: ✨ Living 3D Family Emojis */}
            {activeTab === 'living' && (
                <div className="comment-living-grid d-flex flex-wrap gap-2 overflow-y-auto" style={{ maxHeight: '220px' }} data-testid="comment-living-grid">
                    {livingCatalog.length === 0 ? (
                        <div className="text-muted small p-2">
                            Living 3D Family Emojis are loading or unavailable. Visit <a href="/member/emojiStudio">Emoji Studio</a> to explore them.
                        </div>
                    ) : (
                        livingCatalog.map((le) => (
                            <button
                                key={le.id}
                                type="button"
                                className="comment-living-btn btn btn-light border rounded-3 d-inline-flex flex-column align-items-center justify-content-center p-1"
                                style={{ minWidth: '56px', minHeight: '56px' }}
                                data-emoji-id={le.id}
                                title={le.title}
                                aria-label={`Insert living emoji ${le.title}`}
                                onClick={() => onSelectLivingEmoji?.(le.id)}
                            >
                                <img
                                    src={`/public/img/emoji/${le.id}.svg?v=20261107a`}
                                    onError={(e) => {
                                        if (!e.currentTarget.dataset.fb) {
                                            e.currentTarget.dataset.fb = '1';
                                            e.currentTarget.src = `/img/emoji/${le.id}.svg?v=20261107a`;
                                        }
                                    }}
                                    alt={le.title}
                                    width="38"
                                    height="38"
                                    loading="lazy"
                                    className="atg-3d-emoji"
                                />
                            </button>
                        ))
                    )}
                </div>
            )}

            {/* Tab 3: 🙋 Me-Stickers */}
            {activeTab === 'me' && (
                <div className="me-sticker-grid me-sticker-grid--comment" data-testid="comment-me-grid">
                    {meError && <span className="text-muted small">Could not load your avatar stickers.</span>}
                    {!meError && meStickers === null && <span className="text-muted small">Loading your avatar…</span>}
                    {!meError && meStickers && !meStickers.available && (
                        <a href="/member/emojiStudio" className="btn btn-sm btn-warning rounded-pill fw-semibold" style={{ minHeight: '44px', display: 'inline-flex', alignItems: 'center' }}>
                            🎨 Design your avatar in Emoji Studio to unlock Me-stickers
                        </a>
                    )}
                    {!meError && meStickers?.available && Array.isArray(meStickers.stickers) && (
                        <div className="d-flex flex-wrap gap-2">
                            {meStickers.stickers.map((st) => (
                                <button
                                    key={st.reaction}
                                    type="button"
                                    className="me-sticker-btn btn btn-light border rounded-3 d-inline-flex flex-column align-items-center p-2"
                                    style={{ minWidth: '68px', minHeight: '68px' }}
                                    title={st.label}
                                    aria-label={`Add my avatar: ${st.label}`}
                                    onClick={() => onSelectMeSticker?.(st)}
                                >
                                    <img src={st.url} width="52" height="52" alt={st.label} loading="lazy" />
                                    <span className="small mt-1">{st.label}</span>
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {/* Tab 4: 🎬 Family GIFs */}
            {activeTab === 'gif' && (
                <div data-testid="comment-gif-picker">
                    <div className="d-flex flex-wrap gap-1 mb-2 overflow-x-auto">
                        {GIF_CATEGORIES.map((cat) => (
                            <button
                                key={cat.key}
                                type="button"
                                className={`btn btn-sm rounded-pill fw-semibold gif-cat-pill ${gifCategory === cat.key ? 'active btn-dark text-white' : 'btn-light border'}`}
                                style={{ fontSize: '0.75rem', minHeight: '44px', whiteSpace: 'nowrap', flexShrink: 0 }}
                                onClick={() => setGifCategory(cat.key)}
                            >
                                {cat.label}
                            </button>
                        ))}
                    </div>
                    <input
                        type="search"
                        className="form-control form-control-sm mb-2"
                        style={{ minHeight: '44px' }}
                        placeholder="Filter Family GIFs (e.g. celebrate, love, dance)…"
                        aria-label="Filter Family GIFs"
                        value={gifSearch}
                        onInput={(e) => setGifSearch(e.target.value)}
                    />
                    <div className="d-flex flex-wrap gap-2 overflow-y-auto" style={{ maxHeight: '210px' }}>
                        {filteredGifs.length === 0 ? (
                            <div className="text-muted small p-2">No matching Family GIFs found.</div>
                        ) : (
                            filteredGifs.map((g) => (
                                <button
                                    key={g.id}
                                    type="button"
                                    className="gif-item-card btn p-1 border rounded-3 bg-light"
                                    style={{ minWidth: '88px', minHeight: '72px' }}
                                    title={g.title}
                                    aria-label={`Select GIF: ${g.title}`}
                                    onClick={() => onSelectGif?.(g.url)}
                                >
                                    <img
                                        src={g.url}
                                        alt={g.title}
                                        loading="lazy"
                                        style={{ width: '80px', height: '64px', objectFit: 'cover', borderRadius: '6px', display: 'block' }}
                                    />
                                </button>
                            ))
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
