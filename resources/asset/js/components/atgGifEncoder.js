/**
 * AtgGifEncoder — Zero-dependency pure-JS GIF89a Looping Animated GIF Encoder.
 * Supports Netscape 2.0 infinite-loop extension, 6-6-6 + grayscale 256-colour
 * perceptual palette quantisation, and standard LZW variable-code compression.
 */

function buildWebSafePalette() {
  const palette = new Uint8Array(256 * 3);
  let idx = 0;
  // 216-colour 6x6x6 RGB cube
  for (let r = 0; r < 6; r++) {
    for (let g = 0; g < 6; g++) {
      for (let b = 0; b < 6; b++) {
        palette[idx++] = Math.round((r * 255) / 5);
        palette[idx++] = Math.round((g * 255) / 5);
        palette[idx++] = Math.round((b * 255) / 5);
      }
    }
  }
  // 40-step grayscale ramp (216..255)
  for (let i = 0; i < 40; i++) {
    const v = Math.round((i * 255) / 39);
    palette[idx++] = v;
    palette[idx++] = v;
    palette[idx++] = v;
  }
  return palette;
}

function quantizeRgbaFrame(rgba) {
  const pixels = new Uint8Array(rgba.length >> 2);
  for (let i = 0, p = 0; i < rgba.length; i += 4, p++) {
    const r = rgba[i];
    const g = rgba[i + 1];
    const b = rgba[i + 2];
    const a = rgba[i + 3];
    if (a < 128) {
      pixels[p] = 216; // Near-black background for transparent pixels
      continue;
    }
    const ri = Math.round((r * 5) / 255);
    const gi = Math.round((g * 5) / 255);
    const bi = Math.round((b * 5) / 255);
    pixels[p] = ri * 36 + gi * 6 + bi;
  }
  return pixels;
}

function lzwCompress(minCodeSize, indices) {
  const clearCode = 1 << minCodeSize;
  const eoiCode = clearCode + 1;
  const out = [];
  let bitBuf = 0;
  let bitCount = 0;

  const writeBits = (code, size) => {
    bitBuf |= code << bitCount;
    bitCount += size;
    while (bitCount >= 8) {
      out.push(bitBuf & 0xff);
      bitBuf >>= 8;
      bitCount -= 8;
    }
  };

  let codeSize = minCodeSize + 1;
  let nextCode = eoiCode + 1;
  let table = new Map();

  const resetTable = () => {
    table = new Map();
    codeSize = minCodeSize + 1;
    nextCode = eoiCode + 1;
  };

  writeBits(clearCode, codeSize);
  let prefix = indices[0] ?? 0;

  for (let i = 1; i < indices.length; i++) {
    const k = indices[i];
    const key = (prefix << 8) | k;
    const hit = table.get(key);
    if (hit !== undefined) {
      prefix = hit;
    } else {
      writeBits(prefix, codeSize);
      if (nextCode < 4096) {
        table.set(key, nextCode++);
        if (nextCode > (1 << codeSize) && codeSize < 12) {
          codeSize++;
        }
      } else {
        writeBits(clearCode, codeSize);
        resetTable();
      }
      prefix = k;
    }
  }

  writeBits(prefix, codeSize);
  writeBits(eoiCode, codeSize);
  if (bitCount > 0) {
    out.push(bitBuf & 0xff);
  }
  return out;
}

function pushSubBlocks(bytes, data) {
  for (let i = 0; i < data.length; i += 255) {
    const chunkLen = Math.min(255, data.length - i);
    bytes.push(chunkLen);
    for (let j = 0; j < chunkLen; j++) {
      bytes.push(data[i + j]);
    }
  }
  bytes.push(0); // Block terminator
}

/**
 * Encode an array of RGBA Uint8ClampedArray frames into an infinitely looping GIF89a Blob.
 *
 * @param {Uint8ClampedArray[]} frames
 * @param {number} width
 * @param {number} height
 * @param {number} [delayCs=10] - Frame delay in centiseconds (10 = 100ms = 10fps)
 * @returns {Blob}
 */
export function encodeAnimatedGif(frames, width, height, delayCs = 10) {
  const palette = buildWebSafePalette();
  const bytes = [];

  const writeStr = (s) => {
    for (let i = 0; i < s.length; i++) bytes.push(s.charCodeAt(i));
  };
  const writeU16 = (v) => {
    bytes.push(v & 0xff, (v >> 8) & 0xff);
  };

  // Header + Logical Screen Descriptor (Global Color Table, 256 colours = 2^(7+1))
  writeStr('GIF89a');
  writeU16(width);
  writeU16(height);
  bytes.push(0xf7, 0x00, 0x00);

  for (let i = 0; i < palette.length; i++) bytes.push(palette[i]);

  // Netscape 2.0 Application Extension (infinite loop)
  bytes.push(0x21, 0xff, 0x0b);
  writeStr('NETSCAPE2.0');
  bytes.push(0x03, 0x01, 0x00, 0x00, 0x00);

  const minCodeSize = 8;
  for (const rgba of frames) {
    // Graphic Control Extension
    bytes.push(0x21, 0xf9, 0x04, 0x04);
    writeU16(delayCs);
    bytes.push(0x00, 0x00);

    // Image Descriptor
    bytes.push(0x2c);
    writeU16(0);
    writeU16(0);
    writeU16(width);
    writeU16(height);
    bytes.push(0x00);

    // LZW Image Data
    bytes.push(minCodeSize);
    const indices = quantizeRgbaFrame(rgba);
    const lzw = lzwCompress(minCodeSize, indices);
    pushSubBlocks(bytes, lzw);
  }

  bytes.push(0x3b); // GIF Trailer
  return new Blob([new Uint8Array(bytes)], { type: 'image/gif' });
}
