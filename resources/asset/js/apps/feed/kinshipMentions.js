/**
 * Viewer-Relative Cultural Kinship Honorific Resolver for @Mentions & Post Tags
 *
 * Projects respectful British/Commonwealth kinship titles (Dad, Mum, Grandpa, Grandma,
 * Great-Grandpa, Great-Grandma, Uncle, Aunty) onto canonical "@First Last" mentions
 * and "is with …" post tags at display time based on the viewing relative's relationship.
 */

const VOCATIVE_PREFIX_RE = /(^|[\s(>])(grandpa|grandad|granddad|grandfather|grandma|gran|nanny|nan|grandmother|great-grandpa|great-grandma|great-grandfather|great-grandmother|great-uncle|great-aunt|great-aunty|dad|daddy|father|pa|papa|baba|mum|mummy|mom|mommy|mother|ma|mama|uncle|aunt|aunty|auntie)[ \t]+$/i;

export function normaliseNameKey(name) {
    return String(name || '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();
}

export function getMemberProfileHref(targetId, currentUserId = '') {
    const cleanTarget = String(targetId || '').trim();
    if (!cleanTarget) return null;
    const cleanViewer = String(currentUserId || '').trim();
    if (cleanViewer && cleanTarget === cleanViewer) {
        return '/profilePage';
    }
    return `/allMembers/seeProfile/${encodeURIComponent(cleanTarget)}`;
}

export function mapRelationToHonorificClient(egoRelation, viewerId = '', targetId = '') {
    const cleanViewer = String(viewerId || '').trim();
    const cleanTarget = String(targetId || '').trim();
    if (cleanViewer && cleanTarget && cleanViewer === cleanTarget) {
        return null;
    }

    const rel = String(egoRelation || '').trim();
    if (!rel) return null;

    const stripped = rel.replace(/^(?:maternal|paternal)\s+/i, '').trim();
    const lower = stripped.toLowerCase();

    if (!lower || lower.startsWith('former ') || lower.startsWith('late ')) {
        return null;
    }

    if (/^.+['\u2019]s\s+father$/i.test(stripped)) return 'Dad';
    if (/^.+['\u2019]s\s+mother$/i.test(stripped)) return 'Mum';

    if (['father', 'stepfather', 'father-in-law'].includes(lower)) return 'Dad';
    if (['mother', 'stepmother', 'mother-in-law'].includes(lower)) return 'Mum';

    if (['grandfather', 'grandfather-in-law'].includes(lower)) return 'Grandpa';
    if (['grandmother', 'grandmother-in-law'].includes(lower)) return 'Grandma';

    if (['great-grandfather', 'great-grandfather-in-law'].includes(lower)) return 'Great-Grandpa';
    if (['great-grandmother', 'great-grandmother-in-law'].includes(lower)) return 'Great-Grandma';

    if (['great-great-grandfather', '2nd great-grandfather'].includes(lower)) return 'Great-Great-Grandpa';
    if (['great-great-grandmother', '2nd great-grandmother'].includes(lower)) return 'Great-Great-Grandma';

    const nthGf = stripped.match(/^(\d+(?:st|nd|rd|th))\s+great-grandfather$/i);
    if (nthGf) return `${nthGf[1]} Great-Grandpa`;

    const nthGm = stripped.match(/^(\d+(?:st|nd|rd|th))\s+great-grandmother$/i);
    if (nthGm) return `${nthGm[1]} Great-Grandma`;

    if (['uncle', 'uncle-in-law'].includes(lower)) return 'Uncle';
    if (['aunt', 'aunty', 'auntie', 'aunt-in-law'].includes(lower)) return 'Aunty';
    if (['great-uncle', 'great-uncle-in-law'].includes(lower)) return 'Great-Uncle';
    if (['great-aunt', 'great-aunty', 'great-aunt-in-law'].includes(lower)) return 'Great-Aunty';

    return null;
}

export function formatDisplayNameClient(fullName, honorific) {
    const cleanName = String(fullName || '').replace(/\s+/g, ' ').trim();
    const cleanHon = String(honorific || '').trim();
    if (!cleanName || !cleanHon) return cleanName;

    const lowerName = cleanName.toLowerCase();
    const lowerHon = cleanHon.toLowerCase();
    if (lowerName === lowerHon || lowerName.startsWith(`${lowerHon} `)) {
        return cleanName;
    }
    return `${cleanHon} ${cleanName}`;
}

export function buildMentionContext(bootstrap = null, postTags = null) {
    const safeBoot = bootstrap && typeof bootstrap === 'object' ? bootstrap : {};
    const enabled = String(safeBoot.kinshipHonorifics || 'on').toLowerCase() !== 'off';
    const currentUserId = String(safeBoot.userId || '').trim();
    const byName = new Map();
    const byId = new Map();

    const registerEntry = (raw, isPostSpecific = false) => {
        if (!raw || typeof raw !== 'object') return;
        const id = String(raw.id || raw.user_id || '').trim();
        const fallbackName = `${raw.firstName || raw.first_name || ''} ${raw.lastName || raw.last_name || ''}`.replace(/\s+/g, ' ').trim();
        const fullName = String(raw.fullName || raw.full_name || fallbackName).replace(/\s+/g, ' ').trim();
        if (!id || !fullName) return;

        const kinship = String(raw.kinship || '').trim() || 'Family Member';
        const honorific = raw.honorific !== undefined
            ? (raw.honorific ? String(raw.honorific).trim() : null)
            : mapRelationToHonorificClient(kinship, currentUserId, id);
        const effectiveHonorific = enabled ? honorific : null;
        const displayFullName = effectiveHonorific
            ? formatDisplayNameClient(fullName, effectiveHonorific)
            : fullName;

        const entry = {
            id,
            fullName,
            kinship,
            honorific: effectiveHonorific,
            displayFullName,
            ambiguous: Boolean(raw.ambiguous) && !isPostSpecific,
        };

        byId.set(id, entry);

        const key = normaliseNameKey(fullName);
        if (!key) return;

        if (isPostSpecific) {
            byName.set(key, { ...entry, ambiguous: false });
            return;
        }

        const existing = byName.get(key);
        if (existing && existing.id !== id) {
            byName.set(key, {
                ...existing,
                ambiguous: true,
                honorific: null,
                displayFullName: fullName,
            });
            return;
        }
        byName.set(key, entry);
    };

    if (safeBoot.mentionDirectory && typeof safeBoot.mentionDirectory === 'object') {
        Object.values(safeBoot.mentionDirectory).forEach((item) => registerEntry(item, false));
    }

    if (Array.isArray(safeBoot.familyRoster)) {
        safeBoot.familyRoster.forEach((item) => registerEntry(item, false));
    }

    if (currentUserId) {
        const selfName = `${safeBoot.firstName || ''} ${safeBoot.lastName || ''}`.replace(/\s+/g, ' ').trim();
        if (selfName && !byId.has(currentUserId)) {
            registerEntry({
                id: currentUserId,
                fullName: selfName,
                kinship: 'You',
                honorific: null,
            }, false);
        }
    }

    if (Array.isArray(postTags)) {
        postTags.forEach((tag) => registerEntry(tag, true));
    }

    return { enabled, currentUserId, byName, byId };
}

export function applyKinshipToTokens(tokens, context) {
    if (!Array.isArray(tokens) || tokens.length === 0) return [];
    if (!context || !(context.byName instanceof Map) || context.byName.size === 0) {
        return tokens;
    }

    const out = [];
    for (let i = 0; i < tokens.length; i += 1) {
        const tok = tokens[i];
        if (!tok || tok.type !== 'mention') {
            out.push(tok ? { ...tok } : tok);
            continue;
        }

        let rawMentionName = String(tok.value || '').replace(/^@/, '');
        let matchedEntry = context.byName.get(normaliseNameKey(rawMentionName)) || null;

        // Support 3-word titled names (e.g. "@Chief Oluyomi Olaogun") by peeking at the next text token
        const nextTok = tokens[i + 1];
        if (!matchedEntry && nextTok && nextTok.type === 'text') {
            const extraMatch = String(nextTok.value || '').match(/^([ \t]+[A-Za-z\u00C0-\u024F0-9_.'-]+)(?![A-Za-z0-9_])/);
            if (extraMatch) {
                const candidate3Word = `${rawMentionName}${extraMatch[1]}`;
                const entry3 = context.byName.get(normaliseNameKey(candidate3Word));
                if (entry3) {
                    matchedEntry = entry3;
                    rawMentionName = candidate3Word;
                    tokens[i + 1] = {
                        ...nextTok,
                        value: String(nextTok.value || '').slice(extraMatch[1].length),
                    };
                }
            }
        }

        if (!matchedEntry || matchedEntry.ambiguous) {
            out.push({
                ...tok,
                displayValue: tok.value,
                href: null,
                tooltip: `Mentioned: ${tok.value}`,
            });
            continue;
        }

        const isSelf = Boolean(context.currentUserId && String(matchedEntry.id) === String(context.currentUserId));
        const honorific = (!isSelf && context.enabled) ? matchedEntry.honorific : null;
        const displayName = honorific
            ? formatDisplayNameClient(matchedEntry.fullName, honorific)
            : matchedEntry.fullName;

        // Absorb redundant vocative prefix typed right before @mention (e.g. "Grandpa @Oluyomi Olaogun")
        if (honorific && out.length > 0) {
            const prev = out[out.length - 1];
            if (prev && prev.type === 'text' && VOCATIVE_PREFIX_RE.test(prev.value)) {
                prev.value = prev.value.replace(VOCATIVE_PREFIX_RE, '$1');
            }
        }

        const tooltip = isSelf
            ? `Mentioned you (${matchedEntry.fullName})`
            : (matchedEntry.kinship && !['Family Member', 'Relative', 'Extended Relative'].includes(matchedEntry.kinship)
                ? `${matchedEntry.kinship} · ${matchedEntry.fullName}`
                : `Mentioned: ${matchedEntry.fullName}`);

        out.push({
            ...tok,
            memberId: matchedEntry.id,
            kinship: matchedEntry.kinship || null,
            honorific: honorific || null,
            displayValue: `@${displayName}`,
            href: getMemberProfileHref(matchedEntry.id, context.currentUserId),
            tooltip,
        });
    }

    return out.filter((t) => !(t && t.type === 'text' && t.value === ''));
}

export function formatPostTagForViewer(tag, bootstrap = null) {
    const safeTag = tag && typeof tag === 'object' ? tag : {};
    const ctx = buildMentionContext(bootstrap, [safeTag]);
    const id = String(safeTag.user_id || safeTag.id || '').trim();
    const rawName = String(safeTag.fullName || `${safeTag.firstName || ''} ${safeTag.lastName || ''}`).replace(/\s+/g, ' ').trim();
    const entry = (id && ctx.byId.get(id)) || ctx.byName.get(normaliseNameKey(rawName)) || null;

    const isSelf = Boolean(ctx.currentUserId && id && id === ctx.currentUserId);
    const fullName = entry?.fullName || rawName || 'Family Member';
    const kinship = entry?.kinship || String(safeTag.kinship || '').trim() || 'Family Member';
    const honorific = (!isSelf && ctx.enabled)
        ? (entry?.honorific || mapRelationToHonorificClient(kinship, ctx.currentUserId, id))
        : null;
    const label = honorific ? formatDisplayNameClient(fullName, honorific) : fullName;
    const href = getMemberProfileHref(id, ctx.currentUserId);
    const tooltip = isSelf
        ? `You (${fullName})`
        : (kinship && !['Family Member', 'Relative', 'Extended Relative'].includes(kinship)
            ? `${kinship} · ${fullName}`
            : fullName);

    return { id, label, fullName, kinship, honorific, href, tooltip };
}

function escapeHtmlAttr(str) {
    return String(str || '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[c]));
}

function decodeHtmlEntitiesForLookup(str) {
    return String(str || '')
        .replace(/&#0*39;|&apos;/gi, "'")
        .replace(/&quot;/gi, '"')
        .replace(/&amp;/gi, '&');
}

/**
 * Transform @First Last tokens inside already-HTML-escaped strings for Reels, Daily Spark & Alpine views.
 */
export function renderKinshipMentionsHtml(escapedText, {
    enabled = true,
    directory = null,
    currentUserId = '',
    isDark = false,
} = {}) {
    if (!escapedText) return '';
    const byName = new Map();
    if (directory && typeof directory === 'object') {
        Object.entries(directory).forEach(([k, v]) => {
            if (v && typeof v === 'object') {
                byName.set(normaliseNameKey(k), v);
                if (v.fullName) {
                    byName.set(normaliseNameKey(v.fullName), v);
                }
            }
        });
    }

    const chipVariantClass = isDark ? ' mention-chip--stage' : '';
    const mentionWithOptionalVocativeRe = /(?:(\b(?:grandpa|grandad|granddad|grandfather|grandma|gran|nanny|nan|grandmother|great-grandpa|great-grandma|great-grandfather|great-grandmother|great-uncle|great-aunt|great-aunty|dad|daddy|father|pa|papa|baba|mum|mummy|mom|mommy|mother|ma|mama|uncle|aunt|aunty|auntie)[ \t]+))?(?<![\w@])@([A-Za-z\u00C0-\u024F0-9_.'-]+(?:&#0*39;[A-Za-z\u00C0-\u024F0-9_.-]+)?(?:[ \t]+[A-Za-z\u00C0-\u024F0-9_.'-]+(?:&#0*39;[A-Za-z\u00C0-\u024F0-9_.-]+)?){0,2})(?![\w])/gi;

    return String(escapedText).replace(mentionWithOptionalVocativeRe, (fullMatch, vocativePrefix, rawCaptured) => {
        const words = String(rawCaptured || '').split(/[ \t]+/);
        let matched = null;
        let consumedWordCount = 0;

        for (let len = Math.min(words.length, 3); len >= 1; len -= 1) {
            const candidate = decodeHtmlEntitiesForLookup(words.slice(0, len).join(' '));
            const entry = byName.get(normaliseNameKey(candidate));
            if (entry) {
                matched = entry;
                consumedWordCount = len;
                break;
            }
        }

        // Fallback when directory does not contain this name: highlight up to 2 words
        if (!matched || matched.ambiguous) {
            const fallbackCount = Math.min(words.length, 2);
            const mentionPart = words.slice(0, fallbackCount).join(' ');
            const trailing = words.slice(fallbackCount).length > 0 ? ` ${words.slice(fallbackCount).join(' ')}` : '';
            const safeLabel = escapeHtmlAttr(decodeHtmlEntitiesForLookup(mentionPart));
            return `${vocativePrefix || ''}<span class="mention-chip${chipVariantClass}" title="Mentioned: @${safeLabel}">@${mentionPart}</span>${trailing}`;
        }

        const trailing = words.slice(consumedWordCount).length > 0
            ? ` ${words.slice(consumedWordCount).join(' ')}`
            : '';
        const id = String(matched.id || '').trim();
        const isSelf = Boolean(currentUserId && id && String(currentUserId) === id);
        const honorific = (!isSelf && enabled)
            ? (matched.honorific || mapRelationToHonorificClient(matched.kinship || '', currentUserId, id))
            : null;
        const displayFull = honorific
            ? formatDisplayNameClient(matched.fullName, honorific)
            : matched.fullName;
        const href = getMemberProfileHref(id, currentUserId);
        const tooltip = isSelf
            ? `Mentioned you (${matched.fullName})`
            : (matched.kinship && !['Family Member', 'Relative', 'Extended Relative'].includes(matched.kinship)
                ? `${matched.kinship} · ${matched.fullName}`
                : `Mentioned: ${matched.fullName}`);

        const keptPrefix = honorific ? '' : (vocativePrefix || '');
        const safeText = escapeHtmlAttr(`@${displayFull}`);
        const safeTitle = escapeHtmlAttr(tooltip);

        if (href) {
            return `${keptPrefix}<a href="${escapeHtmlAttr(href)}" class="mention-chip mention-chip--link${chipVariantClass}" title="${safeTitle}" onclick="event.stopPropagation();">${safeText}</a>${trailing}`;
        }
        return `${keptPrefix}<span class="mention-chip${chipVariantClass}" title="${safeTitle}">${safeText}</span>${trailing}`;
    });
}
