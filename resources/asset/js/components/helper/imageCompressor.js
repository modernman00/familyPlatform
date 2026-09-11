/**
 * imageCompressor.js
 * 
 * High-performance, zero-dependency client-side image compression utility.
 * Downscales images to max dimensions and compresses them in milliseconds,
 * reducing multi-megabyte camera photos by 90-95% before network transmission.
 */

/**
 * Compresses an image File or Blob using HTML5 Canvas.
 * 
 * @param {File|Blob} file - The original image file
 * @param {Object} options - Compression options
 * @param {number} options.maxWidth - Max width (default 1920)
 * @param {number} options.maxHeight - Max height (default 1920)
 * @param {number} options.quality - Compression quality 0.0 to 1.0 (default 0.82)
 * @param {string} options.mimeType - Output mime type (default 'image/jpeg')
 * @returns {Promise<File>} Compressed File object
 */
export async function compressImageFile(file, options = {}) {
    // If not an image or is a GIF / SVG, skip compression to preserve animations/vectors
    if (!file || !file.type || !file.type.startsWith('image/') || file.type === 'image/gif' || file.type === 'image/svg+xml') {
        return file;
    }

    // If file exceeds 35MB, avoid browser-side canvas allocation to protect against OOM
    if (file.size > 35 * 1024 * 1024) {
        return file;
    }

    const {
        maxWidth = 1920,
        maxHeight = 1920,
        quality = 0.82,
        mimeType = 'image/jpeg'
    } = options;

    // Fast path: modern browsers support createImageBitmap with automatic EXIF orientation
    if (typeof window !== 'undefined' && typeof window.createImageBitmap === 'function') {
        try {
            const bitmap = await window.createImageBitmap(file, { imageOrientation: 'from-image' });
            let width = bitmap.width;
            let height = bitmap.height;

            // If already smaller than max dimensions and under 300KB, skip resizing
            if (width <= maxWidth && height <= maxHeight && file.size < 300 * 1024) {
                bitmap.close();
                return file;
            }

            // Cap extreme decompression bomb dimensions
            if (width > 8192 || height > 8192) {
                const maxDim = 8192;
                const scale = Math.min(maxDim / width, maxDim / height);
                width = Math.round(width * scale);
                height = Math.round(height * scale);
            }

            if (width > maxWidth || height > maxHeight) {
                const ratio = Math.min(maxWidth / width, maxHeight / height);
                width = Math.round(width * ratio);
                height = Math.round(height * ratio);
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');

            if (!ctx) {
                bitmap.close();
                return file;
            }

            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';

            if (mimeType === 'image/jpeg') {
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, width, height);
            }

            ctx.drawImage(bitmap, 0, 0, width, height);
            bitmap.close();

            return await new Promise((resolve) => {
                canvas.toBlob(
                    (blob) => {
                        if (!blob || blob.size >= file.size) {
                            return resolve(file);
                        }

                        let outName = file.name || 'image.jpg';
                        if (mimeType === 'image/jpeg' && !outName.match(/\.(jpe?g)$/i)) {
                            outName = outName.replace(/\.[^/.]+$/, "") + ".jpg";
                        }

                        resolve(new File([blob], outName, {
                            type: mimeType,
                            lastModified: Date.now()
                        }));
                    },
                    mimeType,
                    quality
                );
            });
        } catch (e) {
            // Fallback to Image() pipeline on any bitmap error
        }
    }

    // Fallback path: FileReader + Image
    return new Promise((resolve) => {
        const reader = new FileReader();

        reader.onerror = () => resolve(file);

        reader.onload = (e) => {
            const img = new Image();

            img.onerror = () => resolve(file);

            img.onload = () => {
                try {
                    let width = img.naturalWidth || img.width;
                    let height = img.naturalHeight || img.height;

                    if (width <= maxWidth && height <= maxHeight && file.size < 300 * 1024) {
                        return resolve(file);
                    }

                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');

                    if (!ctx) {
                        return resolve(file);
                    }

                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';

                    if (mimeType === 'image/jpeg') {
                        ctx.fillStyle = '#FFFFFF';
                        ctx.fillRect(0, 0, width, height);
                    }

                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(
                        (blob) => {
                            if (!blob || blob.size >= file.size) {
                                return resolve(file);
                            }

                            let outName = file.name || 'image.jpg';
                            if (mimeType === 'image/jpeg' && !outName.match(/\.(jpe?g)$/i)) {
                                outName = outName.replace(/\.[^/.]+$/, "") + ".jpg";
                            }

                            resolve(new File([blob], outName, {
                                type: mimeType,
                                lastModified: Date.now()
                            }));
                        },
                        mimeType,
                        quality
                    );
                } catch (err) {
                    console.warn('Image compression exception, falling back to original file:', err);
                    resolve(file);
                }
            };

            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
}
