/**
 * imageUrls.js
 * 
 * Shared defensive URL normalization utilities for post and profile images.
 * Prevents double-prefixing, avoids encoded slashes (%2F) that break web servers,
 * and ensures fast, resilient image asset resolution across feeds, galleries, and lightboxes.
 */

/**
 * Detects whether a given image URL or path represents an animated GIF.
 * 
 * @param {string|null|undefined} url - Image path or URL
 * @returns {boolean} True if GIF media
 */
export function isGifMedia(url) {
    if (!url || typeof url !== 'string') return false;
    const clean = url.trim().toLowerCase();
    return clean.endsWith('.gif') || 
           clean.includes('.gif?') || 
           clean.includes('/gifs/') || 
           clean.includes('/api/atg/gif/') ||
           clean.startsWith('data:image/gif');
}

/**
 * Resolves a post image filename or path to a clean, absolute URL.
 * 
 * @param {string|null|undefined} img - Image filename or path
 * @returns {string} Fully qualified or root-relative URL
 */
export function getPostImageUrl(img) {
    if (!img || typeof img !== 'string') return '';
    
    let clean = img.trim();
    if (!clean || clean === 'null' || clean === 'undefined' || clean === 'none') {
        return '';
    }

    // Check safe external or blob URLs
    if (clean.startsWith('http://') || clean.startsWith('https://') || clean.startsWith('blob:')) {
        // Downsize oversized Google Photos / Googleusercontent CDN URLs (=s3840, =s2048, =s0) for feed cards
        if (/^https:\/\/(?:lh[3-6]\.googleusercontent\.com|[^/]+\.ggpht\.com)\//i.test(clean)) {
            clean = clean.replace(/=s(?:3840|2048|0)(-no)?$/i, (_, noSuffix) => `=w900${noSuffix || ''}`);
        }
        return clean;
    }

    // Strictly whitelist safe base64 image data URIs (disallow data:text/html, javascript:, etc.)
    if (/^data:image\/(jpe?g|png|webp|gif);base64,/i.test(clean)) {
        return clean;
    }

    // Reject any other data: or javascript: URI schemes
    if (clean.startsWith('data:') || clean.startsWith('javascript:') || clean.startsWith('vbscript:')) {
        return '';
    }

    // Absolute root path (e.g. /images/..., /public/..., /resources/..., /api/atg/gif/..., /storage/...)
    if (clean.startsWith('/')) {
        return clean;
    }

    // Known root folders passed without leading slash
    if (clean.startsWith('images/') || clean.startsWith('public/') || clean.startsWith('resources/') || clean.startsWith('api/atg/gif/')) {
        return `/${clean}`;
    }

    // Extract clean basename if path contains directories
    const parts = clean.split(/[/\\]/);
    const basename = parts[parts.length - 1];

    if (!basename) return '';

    return `/resources/images/post/${encodeURIComponent(basename)}`;
}

/**
 * Resolves a post image URL for a specific modern format variant ('avif', 'webp', or fallback).
 * 
 * Note: Animated GIFs MUST NEVER be rewritten as .avif or .webp variants, as doing so causes
 * 404 broken images in modern browsers using <picture><source>.
 * 
 * @param {string|null|undefined} img - Image filename or path
 * @param {'avif'|'webp'|'fallback'} variant - Target format variant
 * @param {boolean|string[]|Record<string, string[]>|null} [verifiedVariants=null] - Verified variant manifest (pass false or post.image_variants to prevent blind 404 <source> emissions)
 * @returns {string} Fully qualified or root-relative URL for the requested variant, or empty string if not applicable
 */
export function getPostImageVariantUrl(img, variant = 'fallback', verifiedVariants = null) {
    const fallbackUrl = getPostImageUrl(img);
    if (!fallbackUrl) {
        return '';
    }

    if (variant === 'fallback') {
        return fallbackUrl;
    }

    // GIFs MUST NOT be served as static AVIF or WebP variants
    if (isGifMedia(fallbackUrl) || isGifMedia(img)) {
        return '';
    }

    // Do not transform external URLs, blobs, or data URIs
    if (fallbackUrl.startsWith('http://') || fallbackUrl.startsWith('https://') || fallbackUrl.startsWith('blob:') || fallbackUrl.startsWith('data:')) {
        return '';
    }

    // When a verification manifest (or explicit false) is provided, never blindly emit
    // speculative .avif/.webp <source> paths unless the source already is that format or verified.
    if (verifiedVariants !== null) {
        const lower = fallbackUrl.toLowerCase();
        if (lower.endsWith('.' + variant) || lower.includes('.' + variant + '?')) {
            return fallbackUrl;
        }
        const imgVariants = (verifiedVariants && typeof verifiedVariants === 'object')
            ? (Array.isArray(verifiedVariants) ? verifiedVariants : verifiedVariants[img])
            : null;
        if (!Array.isArray(imgVariants) || !imgVariants.includes(variant)) {
            return '';
        }
    }

    const dotIndex = fallbackUrl.lastIndexOf('.');
    if (dotIndex <= 0) {
        return '';
    }

    const basePath = fallbackUrl.substring(0, dotIndex);
    return `${basePath}.${variant}`;
}

/**
 * Resolves a profile avatar image filename or path.
 * 
 * @param {string|null|undefined} img - Image filename or path
 * @returns {string} Fully qualified or root-relative URL
 */
export function getProfileImageUrl(img) {
    if (!img || typeof img !== 'string') return '/public/avatar/avatarM.png';
    
    let clean = img.trim();
    if (!clean || clean === 'null' || clean === 'undefined' || clean === 'none') {
        return '/public/avatar/avatarM.png';
    }

    if (clean.startsWith('http://') || clean.startsWith('https://') || clean.startsWith('blob:')) {
        return clean;
    }

    if (/^data:image\/(jpe?g|png|webp|gif);base64,/i.test(clean)) {
        return clean;
    }

    if (clean.startsWith('data:') || clean.startsWith('javascript:') || clean.startsWith('vbscript:')) {
        return '/public/avatar/avatarM.png';
    }

    if (clean.startsWith('/public/avatar/')) {
        return clean;
    }

    const parts = clean.split(/[/\\]/);
    const basename = parts[parts.length - 1];

    if (!basename) return '/public/avatar/avatarM.png';

    return `/resources/images/profile/${encodeURIComponent(basename)}`;
}
