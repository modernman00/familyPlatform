/**
 * Best-effort "which year was this photo taken?" for the Then & Now composer (RFC-0003).
 * Reads EXIF DateTimeOriginal / DateTimeDigitized from a JPEG header. Returns null when the
 * file carries no usable date. Must run on the ORIGINAL file: client-side compression
 * re-encodes through a canvas and drops EXIF.
 */

const MIN_YEAR = 1900;

const validYear = (y) => Number.isInteger(y) && y >= MIN_YEAR && y <= new Date().getFullYear() + 1;

const readAscii = (view, start, length) => {
    let out = '';
    for (let i = 0; i < length && start + i < view.byteLength; i++) {
        const c = view.getUint8(start + i);
        if (c === 0) break;
        out += String.fromCharCode(c);
    }
    return out;
};

/** @param {ArrayBuffer} buffer first bytes of a JPEG */
export const exifYearFromBuffer = (buffer) => {
    try {
        const view = new DataView(buffer);
        if (view.byteLength < 12 || view.getUint16(0) !== 0xffd8) return null;

        let offset = 2;
        while (offset + 4 < view.byteLength) {
            if (view.getUint8(offset) !== 0xff) return null;
            const marker = view.getUint8(offset + 1);
            const size = view.getUint16(offset + 2);
            if (marker === 0xe1 && readAscii(view, offset + 4, 4) === 'Exif') {
                return yearFromTiff(view, offset + 10);
            }
            if (marker === 0xda) return null; // start of image data, no EXIF before it
            offset += 2 + size;
        }
    } catch (_) {
        // truncated or malformed header: treat as "no date"
    }
    return null;
};

const yearFromTiff = (view, tiff) => {
    const little = view.getUint16(tiff) === 0x4949;
    const u16 = (o) => view.getUint16(o, little);
    const u32 = (o) => view.getUint32(o, little);
    if (u16(tiff + 2) !== 0x002a) return null;

    const readIfd = (ifdOffset, wanted) => {
        const base = tiff + ifdOffset;
        const count = u16(base);
        const found = {};
        for (let i = 0; i < count; i++) {
            const entry = base + 2 + i * 12;
            const tag = u16(entry);
            if (wanted.includes(tag)) {
                const length = u32(entry + 4);
                const valueOffset = length > 4 ? tiff + u32(entry + 8) : entry + 8;
                found[tag] = { type: u16(entry + 2), length, valueOffset, raw: u32(entry + 8) };
            }
        }
        return found;
    };

    const ifd0 = readIfd(u32(tiff + 4), [0x8769, 0x0132]);
    let text = null;
    if (ifd0[0x8769]) {
        const exif = readIfd(ifd0[0x8769].raw, [0x9003, 0x9004]);
        const tag = exif[0x9003] || exif[0x9004];
        if (tag) text = readAscii(view, tag.valueOffset, tag.length);
    }
    if (!text && ifd0[0x0132]) text = readAscii(view, ifd0[0x0132].valueOffset, ifd0[0x0132].length);

    const year = text ? parseInt(text.slice(0, 4), 10) : NaN;
    return validYear(year) ? year : null;
};

/**
 * @param {File} file
 * @returns {Promise<number|null>} four-digit year or null
 */
export const guessPhotoYear = async (file) => {
    if (!file || !/^image\/jpe?g$/i.test(file.type || '')) return null;
    try {
        return exifYearFromBuffer(await file.slice(0, 256 * 1024).arrayBuffer());
    } catch (_) {
        return null;
    }
};
