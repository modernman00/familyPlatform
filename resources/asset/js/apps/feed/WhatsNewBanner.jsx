import { useState } from 'preact/hooks';
import styles from './WhatsNewBanner.module.css';

function sanitizeVersionKey(versionKey) {
    return String(versionKey || '2026_11_w4').replace(/[^a-zA-Z0-9_]/g, '_');
}

function sanitizeIconClass(icon) {
    const cleaned = String(icon || 'bi-stars').replace(/[^a-zA-Z0-9_-]/g, '');
    return cleaned || 'bi-stars';
}

function sanitizeHexColor(color) {
    return /^#[0-9a-fA-F]{3,6}$/.test(String(color || '')) ? String(color) : '#a31621';
}

function readInitialDismissed(storageKey) {
    if (typeof window === 'undefined' || !window.localStorage) return false;
    try {
        const val = window.localStorage.getItem(storageKey);
        const alpineVal = window.localStorage.getItem(`_x_${storageKey}`);
        return val === 'true' || alpineVal === 'true';
    } catch (_) {
        return false;
    }
}

export function WhatsNewBanner({ whatsNew }) {
    const items = Array.isArray(whatsNew?.items) ? whatsNew.items : [];
    const safeVersion = sanitizeVersionKey(whatsNew?.version_key);
    const storageKey = `fp_whats_new_${safeVersion}_dismissed`;

    const [dismissed, setDismissed] = useState(() => readInitialDismissed(storageKey));
    const [expanded, setExpanded] = useState(false);

    if (items.length === 0 || dismissed) {
        return null;
    }

    const badge = String(whatsNew?.badge || 'What’s New');
    const title = String(whatsNew?.title || 'Fresh updates for your family circle this week');
    const summary = String(whatsNew?.summary || '');
    const cta = whatsNew?.primary_cta && typeof whatsNew.primary_cta === 'object' ? whatsNew.primary_cta : {};
    const ctaLabel = String(cta.label || 'Explore Updates');
    const ctaUrl = String(cta.url || '/arcade');
    const ctaIcon = sanitizeIconClass(cta.icon || 'bi-stars');
    const playbookUrl = String(whatsNew?.playbook_url || '/playbook');

    const handleDismiss = () => {
        setDismissed(true);
        if (typeof window !== 'undefined' && window.localStorage) {
            try {
                window.localStorage.setItem(storageKey, 'true');
            } catch (_) {
                // Ignore storage quota errors in restricted/private mode
            }
        }
    };

    return (
        <section
            id="whatsNewFeedBanner"
            className={styles.card}
            aria-label="What is new this week on FamilyPlatform"
            data-testid="whats-new-banner"
        >
            <div className={styles.body}>
                <div className={styles.header}>
                    <div className={styles.headerText}>
                        <div className={styles.badgeRow}>
                            <span className={styles.badge}>{badge}</span>
                        </div>
                        <h2 className={styles.title}>{title}</h2>
                    </div>

                    <button
                        type="button"
                        className={styles.dismiss}
                        onClick={handleDismiss}
                        aria-label="Dismiss What’s New banner"
                        title="Dismiss"
                    >
                        <i className="bi bi-x-lg" aria-hidden="true" />
                    </button>
                </div>

                {summary && <p className={styles.summary}>{summary}</p>}

                <div className={styles.actions}>
                    <a href={ctaUrl} className={styles.cta}>
                        <i className={`bi ${ctaIcon}`} aria-hidden="true" />
                        <span>{ctaLabel}</span>
                    </a>

                    <button
                        type="button"
                        className={styles.secondary}
                        onClick={() => setExpanded((prev) => !prev)}
                        aria-expanded={expanded}
                        aria-controls="whatsNewDetailsGrid"
                    >
                        <span>{expanded ? 'Hide details' : `See all ${items.length} updates`}</span>
                        <i className={`bi ${expanded ? 'bi-chevron-up' : 'bi-chevron-down'}`} aria-hidden="true" />
                    </button>

                    <a href={playbookUrl} className={styles.secondary}>
                        <i className="bi bi-book" aria-hidden="true" />
                        <span>Playbook</span>
                    </a>
                </div>

                {expanded && (
                    <div id="whatsNewDetailsGrid" className={styles.grid}>
                        {items.map((item, idx) => (
                            <div key={`${item?.title || 'item'}-${idx}`} className={styles.item}>
                                <div className={styles.itemTitle}>
                                    <i
                                        className={`bi ${sanitizeIconClass(item?.icon)}`}
                                        style={{ color: sanitizeHexColor(item?.color) }}
                                        aria-hidden="true"
                                    />
                                    <span>{String(item?.title || '')}</span>
                                </div>
                                <div className={styles.itemDesc}>{String(item?.description || '')}</div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
