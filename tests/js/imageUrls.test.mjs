// Run: node tests/js/imageUrls.test.mjs
import assert from 'node:assert/strict';
import {
    isGifMedia,
    getPostImageUrl,
    getPostImageVariantUrl,
    getProfileImageUrl,
} from '../../resources/asset/js/components/helper/imageUrls.js';

// 1. GIF detection
assert.equal(isGifMedia('/images/brand/gifs/atg_heritage_tree_bloom.gif'), true);
assert.equal(isGifMedia('/api/atg/gif/123'), true);
assert.equal(isGifMedia('photo.jpg'), false);
assert.equal(isGifMedia(null), false);

// 2. Post image URL normalization & XSS scheme rejection
assert.equal(getPostImageUrl('photo123.jpg'), '/resources/images/post/photo123.jpg');
assert.equal(getPostImageUrl('/resources/images/post/photo123.jpg'), '/resources/images/post/photo123.jpg');
assert.equal(getPostImageUrl('https://cdn.example.com/a.jpg'), 'https://cdn.example.com/a.jpg');
assert.equal(getPostImageUrl('javascript:alert(1)'), '');
assert.equal(getPostImageUrl('data:text/html,<script>alert(1)</script>'), '');
assert.equal(getPostImageUrl('null'), '');

// 3. Variant URL safety: NEVER blindly emit .avif/.webp when verifiedVariants === false
assert.equal(getPostImageVariantUrl('photo123.jpg', 'avif', false), '');
assert.equal(getPostImageVariantUrl('photo123.jpg', 'webp', false), '');
assert.equal(getPostImageVariantUrl('photo123.jpg', 'fallback', false), '/resources/images/post/photo123.jpg');

// Already-AVIF / already-WebP source is safe even when verifiedVariants === false
assert.equal(getPostImageVariantUrl('photo123.avif', 'avif', false), '/resources/images/post/photo123.avif');
assert.equal(getPostImageVariantUrl('photo123.webp', 'webp', false), '/resources/images/post/photo123.webp');

// Explicit verified manifest allows matching variant only
assert.equal(
    getPostImageVariantUrl('photo123.jpg', 'avif', { 'photo123.jpg': ['avif'] }),
    '/resources/images/post/photo123.avif'
);
assert.equal(
    getPostImageVariantUrl('photo123.jpg', 'webp', { 'photo123.jpg': ['avif'] }),
    ''
);

// GIFs never emit AVIF/WebP even if manifest passed
assert.equal(getPostImageVariantUrl('anim.gif', 'avif', ['avif']), '');

// 4. Profile avatar fallback
assert.equal(getProfileImageUrl(''), '/public/avatar/avatarM.png');
assert.equal(getProfileImageUrl('javascript:alert(1)'), '/public/avatar/avatarM.png');
assert.equal(getProfileImageUrl('avatar.jpg'), '/resources/images/profile/avatar.jpg');

console.log('imageUrls: all assertions passed');
