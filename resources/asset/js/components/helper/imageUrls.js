/**
 * imageUrls.js
 * 
 * Shared defensive URL normalization utilities for post and profile images.
 * Prevents double-prefixing, avoids encoded slashes (%2F) that break web servers,
 * and ensures fast, resilient image asset resolution across feeds, galleries, and lightboxes.
 */

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

    // Extract clean basename if path contains directories
    const parts = clean.split(/[/\\]/);
    const basename = parts[parts.length - 1];

    if (!basename) return '';

    return `/resources/images/post/${encodeURIComponent(basename)}`;
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
