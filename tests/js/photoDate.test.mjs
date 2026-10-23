// Run: node tests/js/photoDate.test.mjs
import assert from 'node:assert/strict';
import { exifYearFromBuffer } from '../../resources/asset/js/components/helper/photoDate.js';

/** Build a minimal JPEG whose EXIF carries DateTimeOriginal = `stamp` (little-endian TIFF). */
const jpegWithExif = (stamp, { tag = 0x9003, little = true } = {}) => {
    const text = Buffer.from(stamp + '\0', 'ascii');
    const w16 = (n) => { const b = Buffer.alloc(2); little ? b.writeUInt16LE(n) : b.writeUInt16BE(n); return b; };
    const w32 = (n) => { const b = Buffer.alloc(4); little ? b.writeUInt32LE(n) : b.writeUInt32BE(n); return b; };
    const header = Buffer.concat([Buffer.from(little ? 'II' : 'MM'), w16(0x2a), w32(8)]);
    const ifd0 = Buffer.concat([w16(1), w16(0x8769), w16(4), w32(1), w32(26), w32(0)]);          // 8..26
    const textOffset = 26 + 2 + 12 + 4;
    const exifIfd = Buffer.concat([w16(1), w16(tag), w16(2), w32(text.length), w32(textOffset), w32(0)]);
    const tiff = Buffer.concat([header, ifd0, exifIfd, text]);
    const app1Body = Buffer.concat([Buffer.from('Exif\0\0'), tiff]);
    const size = Buffer.alloc(2); size.writeUInt16BE(app1Body.length + 2);
    return Buffer.concat([Buffer.from([0xff, 0xd8, 0xff, 0xe1]), size, app1Body, Buffer.from([0xff, 0xda, 0, 2])]);
};
const ab = (b) => b.buffer.slice(b.byteOffset, b.byteOffset + b.byteLength);

assert.equal(exifYearFromBuffer(ab(jpegWithExif('1998:07:04 12:00:00'))), 1998, 'little-endian DateTimeOriginal');
assert.equal(exifYearFromBuffer(ab(jpegWithExif('2003:01:01 00:00:00', { little: false }))), 2003, 'big-endian');
assert.equal(exifYearFromBuffer(ab(jpegWithExif('1850:01:01 00:00:00'))), null, 'implausible year rejected');
assert.equal(exifYearFromBuffer(ab(jpegWithExif('0000:00:00 00:00:00'))), null, 'zeroed camera clock rejected');
assert.equal(exifYearFromBuffer(ab(jpegWithExif('2010:05:05 05:05:05', { tag: 0x9004 }))), 2010, 'DateTimeDigitized fallback');
assert.equal(exifYearFromBuffer(new ArrayBuffer(0)), null, 'empty buffer');
assert.equal(exifYearFromBuffer(ab(Buffer.from('not a jpeg at all, just text'))), null, 'not a jpeg');
assert.equal(exifYearFromBuffer(ab(jpegWithExif('1998:07:04 12:00:00').subarray(0, 30))), null, 'truncated header never throws');

console.log('photoDate: 8 passed');
