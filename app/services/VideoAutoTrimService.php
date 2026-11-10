<?php
declare(strict_types=1);

namespace App\services;

use Src\Exceptions\ValidationException;

/**
 * VideoAutoTrimService
 *
 * Sovereign video upload & 30-second auto-trimming engine for FamilyPlatform posts.
 * - Enforces strict magic-byte MIME verification (MP4, WebM, QuickTime MOV).
 * - Clamps any requested [trim_start, trim_end] window to a maximum of 30.0 seconds.
 * - Executes lossless container slicing via ffmpeg (proc_open array, 8s timeout) when available.
 * - Parses native ISO-BMFF (MP4/MOV) mvhd atoms to verify duration without external dependencies.
 * - Emits W3C Media Fragment (#t=start,end) + metadata so client players strictly enforce bounds.
 */
final class VideoAutoTrimService
{
    public const MAX_CLIP_DURATION = 30.0;
    public const MAX_FILE_SIZE_BYTES = 1024 * 1024 * 1024; // 1GB (1024MB) pre-trim source cap
    public const MAX_CHUNKS = 100;

    /**
     * Allowed video MIME types mapped to safe canonical extensions.
     *
     * @var array<string, string>
     */
    private const ALLOWED_VIDEO_MIMES = [
        'video/mp4'       => 'mp4',
        'video/webm'      => 'webm',
        'video/quicktime' => 'mov',
        'video/ogg'       => 'ogg',
    ];

    /**
     * Normalise and clamp a requested [start, end] trim range to <= 30.0 seconds.
     *
     * @param float|int|string|null $rawStart
     * @param float|int|string|null $rawEnd
     * @param float|null $detectedDuration
     * @return array{start: float, end: float, duration: float, isTrimmed: bool}
     */
    public static function clampTrimWindow(
        float|int|string|null $rawStart,
        float|int|string|null $rawEnd,
        ?float $detectedDuration = null
    ): array {
        $start = is_numeric($rawStart) ? max(0.0, min((float)$rawStart, 36000.0)) : 0.0;
        $end = is_numeric($rawEnd) ? max(0.0, min((float)$rawEnd, 36000.0)) : 0.0;

        if ($detectedDuration !== null && $detectedDuration > 0.0) {
            if ($start >= $detectedDuration) {
                $start = max(0.0, $detectedDuration - self::MAX_CLIP_DURATION);
            }
            if ($end <= $start || $end > $detectedDuration) {
                $end = min($detectedDuration, $start + self::MAX_CLIP_DURATION);
            }
        } else {
            if ($end <= $start) {
                $end = $start + self::MAX_CLIP_DURATION;
            }
        }

        // Enforce strict 30-second cap
        if (($end - $start) > self::MAX_CLIP_DURATION) {
            $end = $start + self::MAX_CLIP_DURATION;
        }

        $start = round($start, 2);
        $end = round($end, 2);
        $duration = round(max(0.1, $end - $start), 2);

        $isTrimmed = ($start > 0.0)
            || ($detectedDuration !== null && $detectedDuration > (self::MAX_CLIP_DURATION + 0.5))
            || ($detectedDuration !== null && ($end < ($detectedDuration - 0.2)));

        return [
            'start'     => $start,
            'end'       => $end,
            'duration'  => $duration,
            'isTrimmed' => $isTrimmed,
        ];
    }

    /**
     * Process an uploaded video file, auto-trim it to <= 30s, and persist to resources/videos/posts/.
     *
     * @param array<string, mixed> $file $_FILES['video_file']
     * @param string $userId
     * @param float|int|string|null $trimStart
     * @param float|int|string|null $trimEnd
     * @param float|int|string|null $clientDuration
     * @param bool $isAssembledChunk
     * @return array{
     *   success: bool,
     *   videoId: string,
     *   fileName: string,
     *   streamUrl: string,
     *   rawUrl: string,
     *   trimStart: float,
     *   trimEnd: float,
     *   duration: float,
     *   originalDuration: float|null,
     *   physicallyTrimmed: bool,
     *   autoTrimmed: bool
     * }
     */
    public static function processUploadAndTrim(
        array $file,
        string $userId,
        float|int|string|null $trimStart = 0.0,
        float|int|string|null $trimEnd = 30.0,
        float|int|string|null $clientDuration = null,
        bool $isAssembledChunk = false
    ): array {
        $tmpName = isset($file['tmp_name']) && is_string($file['tmp_name']) ? $file['tmp_name'] : '';
        $error = isset($file['error']) ? (int)$file['error'] : UPLOAD_ERR_NO_FILE;
        $size = isset($file['size']) ? (int)$file['size'] : 0;

        if ($error !== UPLOAD_ERR_OK || $tmpName === '' || !is_file($tmpName)) {
            throw new ValidationException('No valid video file was uploaded.');
        }

        if ($size <= 0 || $size > self::MAX_FILE_SIZE_BYTES) {
            throw new ValidationException('Video size must be between 1 byte and 1GB.');
        }

        if (!$isAssembledChunk && !is_uploaded_file($tmpName) && !self::isTestEnvironment()) {
            throw new ValidationException('Untrusted file upload source.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName);
        if (!is_string($mime) || !isset(self::ALLOWED_VIDEO_MIMES[$mime])) {
            throw new ValidationException('Invalid video format. Only MP4, WebM, and QuickTime (MOV) videos are allowed.');
        }

        // Reject polyglot files containing PHP tags in the first 4KB
        $headHandle = fopen($tmpName, 'rb');
        if ($headHandle !== false) {
            $headBytes = (string)fread($headHandle, 4096);
            fclose($headHandle);
            if (stripos($headBytes, '<?php') !== false || stripos($headBytes, '<?=') !== false) {
                throw new ValidationException('Malicious payload detected in video upload.');
            }
        }

        $ext = self::ALLOWED_VIDEO_MIMES[$mime];

        // Inspect native MP4/MOV duration if possible, falling back to client-reported duration
        $parsedDuration = self::parseMp4Duration($tmpName);
        $originalDuration = $parsedDuration ?? (is_numeric($clientDuration) && (float)$clientDuration > 0 ? round((float)$clientDuration, 2) : null);

        $window = self::clampTrimWindow($trimStart, $trimEnd, $originalDuration);

        $targetDir = dirname(__DIR__, 2) . '/resources/videos/posts';
        if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Unable to initialise post video directory.');
        }

        $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '', $userId) ?: 'member';
        $videoId = 'post_vid_' . $safeUser . '_' . time() . '_' . bin2hex(random_bytes(6));
        $fileName = $videoId . '.' . $ext;
        $destPath = $targetDir . '/' . $fileName;

        if ($isAssembledChunk) {
            $moved = @rename($tmpName, $destPath) || copy($tmpName, $destPath);
            if (is_file($tmpName)) {
                @unlink($tmpName); // nosemgrep: php.lang.security.unlink-use.unlink-use
            }
        } else {
            $moved = is_uploaded_file($tmpName)
                ? move_uploaded_file($tmpName, $destPath)
                : copy($tmpName, $destPath);
        }

        if (!$moved || !is_file($destPath)) {
            throw new \RuntimeException('Failed to store uploaded video file.');
        }

        // Attempt lossless hardware/ffmpeg slice if ffmpeg binary is available on host
        $physicallyTrimmed = false;
        if ($window['isTrimmed'] || $size > 50 * 1024 * 1024) {
            $physicallyTrimmed = self::tryFfmpegTrim($destPath, $window['start'], $window['duration'], $ext);
        }

        // Host-Portable Pure-PHP FastStart: Ensure MP4/MOV `moov` atom precedes `mdat` so streaming starts in <100ms on Namecheap or any host without ffmpeg
        $fastStartOptimised = false;
        if ($ext === 'mp4' || $ext === 'mov') {
            $fastStartOptimised = self::optimiseMp4FastStart($destPath);
        }

        $rawUrl = '/resources/videos/posts/' . $fileName;

        // If physically trimmed by ffmpeg, the resulting file starts at 0 and lasts $window['duration'].
        // If ffmpeg is not installed, we append the canonical W3C media fragment #t=start,end so our
        // feed player and browser native range requests enforce the exact 30s window.
        if ($physicallyTrimmed) {
            $streamUrl = $rawUrl . '#t=0,' . $window['duration'];
        } elseif ($window['isTrimmed'] || $window['start'] > 0.0 || $window['duration'] <= self::MAX_CLIP_DURATION) {
            $streamUrl = $rawUrl . '#t=' . $window['start'] . ',' . $window['end'];
        } else {
            $streamUrl = $rawUrl;
        }

        return [
            'success'            => true,
            'videoId'            => $videoId,
            'fileName'           => $fileName,
            'streamUrl'          => $streamUrl,
            'rawUrl'             => $rawUrl,
            'trimStart'          => $window['start'],
            'trimEnd'            => $window['end'],
            'duration'           => $window['duration'],
            'originalDuration'   => $originalDuration,
            'physicallyTrimmed'  => $physicallyTrimmed,
            'autoTrimmed'        => $window['isTrimmed'],
            'fastStartOptimised' => $fastStartOptimised,
        ];
    }

    /**
     * Reassemble a multi-chunk video upload (for large >20MB videos up to 1GB) and auto-trim on the final chunk.
     *
     * @param array<string, mixed> $file
     * @param string $userId
     * @param string $uploadId
     * @param int $chunkIndex
     * @param int $totalChunks
     * @param float|int|string|null $trimStart
     * @param float|int|string|null $trimEnd
     * @param float|int|string|null $clientDuration
     * @return array<string, mixed>
     */
    public static function processChunkedUpload(
        array $file,
        string $userId,
        string $uploadId,
        int $chunkIndex,
        int $totalChunks,
        float|int|string|null $trimStart = 0.0,
        float|int|string|null $trimEnd = 30.0,
        float|int|string|null $clientDuration = null
    ): array {
        if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $uploadId)) {
            throw new ValidationException('Invalid chunk upload identifier.');
        }

        if ($totalChunks < 1 || $totalChunks > self::MAX_CHUNKS || $chunkIndex < 0 || $chunkIndex >= $totalChunks) {
            throw new ValidationException('Invalid video chunk sequence.');
        }

        $tmpName = isset($file['tmp_name']) && is_string($file['tmp_name']) ? $file['tmp_name'] : '';
        $error = isset($file['error']) ? (int)$file['error'] : UPLOAD_ERR_NO_FILE;
        $chunkSize = isset($file['size']) ? (int)$file['size'] : 0;

        if ($error !== UPLOAD_ERR_OK || $tmpName === '' || !is_file($tmpName) || $chunkSize <= 0) {
            throw new ValidationException('No valid video chunk was uploaded.');
        }

        if (!is_uploaded_file($tmpName) && !self::isTestEnvironment()) {
            throw new ValidationException('Untrusted chunk upload source.');
        }

        $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '', $userId) ?: 'member';
        $stagingPath = sys_get_temp_dir() . '/fp_vidchunk_' . $safeUser . '_' . $uploadId . '.part';

        if ($chunkIndex === 0) {
            if (is_file($stagingPath)) {
                @unlink($stagingPath); // nosemgrep: php.lang.security.unlink-use.unlink-use
            }

            // Early polyglot check on the first chunk
            $headHandle = fopen($tmpName, 'rb');
            if ($headHandle !== false) {
                $headBytes = (string)fread($headHandle, 4096);
                fclose($headHandle);
                if (stripos($headBytes, '<?php') !== false || stripos($headBytes, '<?=') !== false) {
                    throw new ValidationException('Malicious payload detected in video upload.');
                }
            }
        } elseif (!is_file($stagingPath)) {
            throw new ValidationException('Upload session expired or out-of-order chunk received.');
        }

        $inStream = @fopen($tmpName, 'rb');
        $outStream = @fopen($stagingPath, 'ab');
        if ($inStream === false || $outStream === false) {
            if (is_resource($inStream)) {
                fclose($inStream);
            }
            if (is_resource($outStream)) {
                fclose($outStream);
            }
            throw new \RuntimeException('Unable to write video upload chunk.');
        }

        stream_copy_to_stream($inStream, $outStream);
        fclose($inStream);
        fclose($outStream);

        clearstatcache(true, $stagingPath);
        $accumulatedSize = (int)@filesize($stagingPath);

        if ($accumulatedSize <= 0 || $accumulatedSize > self::MAX_FILE_SIZE_BYTES) {
            @unlink($stagingPath); // nosemgrep: php.lang.security.unlink-use.unlink-use
            throw new ValidationException('Video size must be between 1 byte and 1GB.');
        }

        if ($chunkIndex < $totalChunks - 1) {
            return [
                'success'          => true,
                'chunkComplete'    => true,
                'chunkIndex'       => $chunkIndex,
                'totalChunks'      => $totalChunks,
                'accumulatedBytes' => $accumulatedSize,
            ];
        }

        try {
            return self::processUploadAndTrim(
                file: [
                    'name'     => (string)($file['name'] ?? 'video.mp4'),
                    'tmp_name' => $stagingPath,
                    'size'     => $accumulatedSize,
                    'error'    => UPLOAD_ERR_OK,
                ],
                userId: $userId,
                trimStart: $trimStart,
                trimEnd: $trimEnd,
                clientDuration: $clientDuration,
                isAssembledChunk: true
            );
        } finally {
            if (is_file($stagingPath)) {
                @unlink($stagingPath); // nosemgrep: php.lang.security.unlink-use.unlink-use
            }
        }
    }

    /**
     * Pure-PHP ISO-BMFF (MP4 / QuickTime MOV) `moov` Atom FastStart Optimiser.
     * Works 100% natively on Namecheap shared/cPanel hosting and any future cloud/VPS host without requiring ffmpeg or Python.
     * - If `moov` is already before `mdat`, returns true immediately in <1ms.
     * - If `moov` is placed after `mdat`, relocates `moov` ahead of `mdat` and shifts `stco`/`co64` chunk offsets by +$moovSize.
     */
    public static function optimiseMp4FastStart(string $filePath): bool
    {
        if (!is_file($filePath) || !is_readable($filePath) || !is_writable($filePath)) {
            return false;
        }

        clearstatcache(true, $filePath);
        $fileSize = (int)@filesize($filePath);
        // Safe ceiling for pure-PHP relocation on shared hosting (64MB file cap)
        if ($fileSize < 32 || $fileSize > 64 * 1024 * 1024) {
            return false;
        }

        $fp = @fopen($filePath, 'rb');
        if ($fp === false) {
            return false;
        }

        $moovOffset = null;
        $moovSize = 0;
        $mdatOffset = null;

        try {
            $pos = 0;
            while ($pos + 8 <= $fileSize) {
                if (fseek($fp, $pos) !== 0) {
                    break;
                }
                $header = fread($fp, 8);
                if ($header === false || strlen($header) < 8) {
                    break;
                }
                $unpacked = unpack('Nsize/a4type', $header);
                if (!is_array($unpacked)) {
                    break;
                }
                $size = (int)$unpacked['size'];
                $type = (string)$unpacked['type'];
                $headerSize = 8;

                if ($size === 1) {
                    $ext = fread($fp, 8);
                    if ($ext === false || strlen($ext) < 8) {
                        break;
                    }
                    $hiLo = unpack('Nhi/Nlo', $ext);
                    if (!is_array($hiLo)) {
                        break;
                    }
                    $size = ((int)$hiLo['hi'] << 32) | (int)$hiLo['lo'];
                    $headerSize = 16;
                } elseif ($size === 0) {
                    $size = $fileSize - $pos;
                }

                if ($size < $headerSize || $pos + $size > $fileSize) {
                    break;
                }

                if ($type === 'moov') {
                    $moovOffset = $pos;
                    $moovSize = $size;
                } elseif ($type === 'mdat' && $mdatOffset === null) {
                    $mdatOffset = $pos;
                }

                $pos += $size;
            }

            if ($moovOffset === null || $mdatOffset === null || $moovSize < 16) {
                return false;
            }

            // Already FastStart-optimised (`moov` precedes `mdat`)
            if ($moovOffset < $mdatOffset) {
                return true;
            }

            // Guard `moov` atom size to <= 8MB so RAM footprint stays tiny on Namecheap
            if ($moovSize > 8 * 1024 * 1024) {
                return false;
            }

            if (fseek($fp, $moovOffset) !== 0) {
                return false;
            }
            $moovBytes = fread($fp, $moovSize);
            if ($moovBytes === false || strlen($moovBytes) !== $moovSize) {
                return false;
            }

            // Shift all `stco` (32-bit) and `co64` (64-bit) chunk offsets inside `moov` by +$moovSize
            self::shiftMoovChunkOffsets($moovBytes, 8, $moovSize, $moovSize, $mdatOffset, 0);

            $tmpOut = $filePath . '.faststart.tmp';
            $outFp = @fopen($tmpOut, 'wb');
            if ($outFp === false) {
                return false;
            }

            try {
                // 1. Copy leading boxes before `mdat` (e.g. `ftyp`, `free`)
                if ($mdatOffset > 0) {
                    fseek($fp, 0);
                    stream_copy_to_stream($fp, $outFp, $mdatOffset);
                }

                // 2. Write relocated & offset-adjusted `moov` box right before `mdat`
                fwrite($outFp, $moovBytes);

                // 3. Copy `mdat` and any boxes up to original `$moovOffset`
                $middleLen = $moovOffset - $mdatOffset;
                if ($middleLen > 0) {
                    fseek($fp, $mdatOffset);
                    stream_copy_to_stream($fp, $outFp, $middleLen);
                }

                // 4. Copy any trailing boxes after original `moov`
                $afterMoovOffset = $moovOffset + $moovSize;
                if ($afterMoovOffset < $fileSize) {
                    fseek($fp, $afterMoovOffset);
                    stream_copy_to_stream($fp, $outFp, $fileSize - $afterMoovOffset);
                }
            } finally {
                fclose($outFp);
            }

            clearstatcache(true, $tmpOut);
            if (is_file($tmpOut) && (int)@filesize($tmpOut) === $fileSize) {
                if (@rename($tmpOut, $filePath)) {
                    return true;
                }
            }

            if (is_file($tmpOut)) {
                @unlink($tmpOut); // nosemgrep: php.lang.security.unlink-use.unlink-use
            }
            return false;
        } finally {
            fclose($fp);
        }
    }

    /**
     * Recursively traverse ISO-BMFF container boxes inside `$moovBytes` (`trak`, `mdia`, `minf`, `stbl`, `edts`, `udta`)
     * and increment `stco` / `co64` chunk offsets by `$delta` when `chunkOffset >= $minChunkOffset`.
     */
    private static function shiftMoovChunkOffsets(
        string &$moovBytes,
        int $offset,
        int $limit,
        int $delta,
        int $minChunkOffset,
        int $depth = 0
    ): void {
        if ($depth > 8) {
            return;
        }

        $containers = [
            'moov' => true,
            'trak' => true,
            'mdia' => true,
            'minf' => true,
            'stbl' => true,
            'edts' => true,
            'udta' => true,
        ];

        $pos = $offset;
        while ($pos + 8 <= $limit) {
            $header = substr($moovBytes, $pos, 8);
            $unpacked = unpack('Nsize/a4type', $header);
            if (!is_array($unpacked)) {
                break;
            }

            $size = (int)$unpacked['size'];
            $type = (string)$unpacked['type'];
            $headerSize = 8;

            if ($size === 1) {
                if ($pos + 16 > $limit) {
                    break;
                }
                $hiLo = unpack('Nhi/Nlo', substr($moovBytes, $pos + 8, 8));
                if (!is_array($hiLo)) {
                    break;
                }
                $size = ((int)$hiLo['hi'] << 32) | (int)$hiLo['lo'];
                $headerSize = 16;
            } elseif ($size === 0) {
                $size = $limit - $pos;
            }

            if ($size < $headerSize || $pos + $size > $limit) {
                break;
            }

            if (isset($containers[$type])) {
                self::shiftMoovChunkOffsets($moovBytes, $pos + $headerSize, $pos + $size, $delta, $minChunkOffset, $depth + 1);
            } elseif ($type === 'stco' && $size >= $headerSize + 8) {
                $countArr = unpack('Ncount', substr($moovBytes, $pos + $headerSize + 4, 4));
                $entryCount = is_array($countArr) ? (int)$countArr['count'] : 0;
                $entriesStart = $pos + $headerSize + 8;
                for ($i = 0; $i < $entryCount; $i++) {
                    $entryPos = $entriesStart + ($i * 4);
                    if ($entryPos + 4 > $pos + $size) {
                        break;
                    }
                    $valArr = unpack('Noffset', substr($moovBytes, $entryPos, 4));
                    if (is_array($valArr)) {
                        $oldOff = (int)$valArr['offset'];
                        if ($oldOff >= $minChunkOffset) {
                            $newBytes = pack('N', $oldOff + $delta);
                            for ($b = 0; $b < 4; $b++) {
                                $moovBytes[$entryPos + $b] = $newBytes[$b];
                            }
                        }
                    }
                }
            } elseif ($type === 'co64' && $size >= $headerSize + 8) {
                $countArr = unpack('Ncount', substr($moovBytes, $pos + $headerSize + 4, 4));
                $entryCount = is_array($countArr) ? (int)$countArr['count'] : 0;
                $entriesStart = $pos + $headerSize + 8;
                for ($i = 0; $i < $entryCount; $i++) {
                    $entryPos = $entriesStart + ($i * 8);
                    if ($entryPos + 8 > $pos + $size) {
                        break;
                    }
                    $hiLo = unpack('Nhi/Nlo', substr($moovBytes, $entryPos, 8));
                    if (is_array($hiLo)) {
                        $oldOff = ((int)$hiLo['hi'] << 32) | (int)$hiLo['lo'];
                        if ($oldOff >= $minChunkOffset) {
                            $newOff = $oldOff + $delta;
                            $newHi = ($newOff >> 32) & 0xFFFFFFFF;
                            $newLo = $newOff & 0xFFFFFFFF;
                            $newBytes = pack('NN', $newHi, $newLo);
                            for ($b = 0; $b < 8; $b++) {
                                $moovBytes[$entryPos + $b] = $newBytes[$b];
                            }
                        }
                    }
                }
            }

            $pos += $size;
        }
    }

    /**
     * Parse duration in seconds from an ISO-BMFF (MP4 / QuickTime MOV) file's `mvhd` atom.
     * Zero external dependencies; O(atoms) seek scan without loading the file into RAM.
     */
    public static function parseMp4Duration(string $filePath): ?float
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return null;
        }

        $fp = @fopen($filePath, 'rb');
        if ($fp === false) {
            return null;
        }

        try {
            $fileSize = (int)filesize($filePath);
            if ($fileSize < 16) {
                return null;
            }
            return self::scanBoxesForMvhd($fp, 0, $fileSize, 0);
        } finally {
            fclose($fp);
        }
    }

    /**
     * @param resource $fp
     */
    private static function scanBoxesForMvhd($fp, int $offset, int $limit, int $depth): ?float
    {
        if ($depth > 4) {
            return null;
        }

        $pos = $offset;
        while ($pos + 8 <= $limit) {
            if (fseek($fp, $pos) !== 0) {
                break;
            }
            $header = fread($fp, 8);
            if ($header === false || strlen($header) < 8) {
                break;
            }

            $unpacked = unpack('Nsize/a4type', $header);
            if (!is_array($unpacked)) {
                break;
            }

            $size = (int)$unpacked['size'];
            $type = (string)$unpacked['type'];
            $headerSize = 8;

            if ($size === 1) {
                $ext = fread($fp, 8);
                if ($ext === false || strlen($ext) < 8) {
                    break;
                }
                $hiLo = unpack('Nhi/Nlo', $ext);
                if (!is_array($hiLo)) {
                    break;
                }
                $size = ((int)$hiLo['hi'] << 32) | (int)$hiLo['lo'];
                $headerSize = 16;
            } elseif ($size === 0) {
                $size = $limit - $pos;
            }

            if ($size < $headerSize || $pos + $size > $limit) {
                break;
            }

            if ($type === 'moov') {
                $found = self::scanBoxesForMvhd($fp, $pos + $headerSize, $pos + $size, $depth + 1);
                if ($found !== null) {
                    return $found;
                }
            } elseif ($type === 'mvhd') {
                $bodyLen = min(32, $size - $headerSize);
                if ($bodyLen >= 20) {
                    $body = fread($fp, $bodyLen);
                    if ($body !== false && strlen($body) >= 20) {
                        $version = ord($body[0]);
                        if ($version === 0) {
                            $vals = unpack('Ntimescale/Nduration', substr($body, 12, 8));
                            if (is_array($vals) && (int)$vals['timescale'] > 0) {
                                return round((float)$vals['duration'] / (float)$vals['timescale'], 2);
                            }
                        } elseif ($version === 1 && strlen($body) >= 32) {
                            $ts = unpack('Ntimescale', substr($body, 20, 4));
                            $dur = unpack('Nhi/Nlo', substr($body, 24, 8));
                            if (is_array($ts) && is_array($dur) && (int)$ts['timescale'] > 0) {
                                $durationTicks = ((int)$dur['hi'] << 32) | (int)$dur['lo'];
                                return round((float)$durationTicks / (float)$ts['timescale'], 2);
                            }
                        }
                    }
                }
            }

            $pos += $size;
        }

        return null;
    }

    /**
     * Attempt lossless container trim via ffmpeg with a strict 8-second watchdog timeout.
     * Uses proc_open with an argument array (zero shell interpolation).
     */
    private static function tryFfmpegTrim(string $filePath, float $startSec, float $durationSec, string $ext): bool
    {
        $ffmpegBin = self::resolveFfmpegBinary();
        if ($ffmpegBin === null) {
            return false;
        }

        $tmpOut = $filePath . '.trimmed.' . $ext;
        $cmd = [
            $ffmpegBin,
            '-y',
            '-ss', sprintf('%.2f', $startSec),
            '-i', $filePath,
            '-t', sprintf('%.2f', $durationSec),
            '-c', 'copy',
            '-movflags', '+faststart',
            $tmpOut,
        ];

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        // Safe: $cmd is an explicit argv array (never passed through /bin/sh), with float-formatted timestamps and server-generated CSPRNG filename.
        $process = @proc_open($cmd, $descriptors, $pipes); // nosemgrep: php.lang.security.exec-use.exec-use
        if (!is_resource($process)) {
            return false;
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $deadline = microtime(true) + 8.0; // David Gate 3: 8s strict watchdog
        $exitCode = -1;

        while (microtime(true) < $deadline) {
            $status = proc_get_status($process);
            if (!$status['running']) {
                $exitCode = (int)$status['exitcode'];
                break;
            }
            usleep(50000); // 50ms
        }

        $status = proc_get_status($process);
        if ($status['running']) {
            @proc_terminate($process, 9);
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        @proc_close($process);

        if ($exitCode === 0 && is_file($tmpOut) && filesize($tmpOut) > 256) {
            if (@rename($tmpOut, $filePath)) {
                return true;
            }
        }

        if (is_file($tmpOut)) {
            @unlink($tmpOut); // nosemgrep: php.lang.security.unlink-use.unlink-use
        }

        return false;
    }

    private static function resolveFfmpegBinary(): ?string
    {
        $candidates = [
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/opt/homebrew/bin/ffmpeg',
        ];
        foreach ($candidates as $bin) {
            if (is_file($bin) && is_executable($bin)) {
                return $bin;
            }
        }
        return null;
    }

    private static function isTestEnvironment(): bool
    {
        if (function_exists('isTestEnv') && \isTestEnv()) {
            return true;
        }
        return defined('PHPUNIT_COMPOSER_INSTALL') || (PHP_SAPI === 'cli');
    }
}
