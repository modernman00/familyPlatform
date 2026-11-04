<?php
namespace App\controller;

final class ServeImgController {
    /**
     * @return never
     */
    public function ProfileDir($imgName) {
        $filename = $imgName;

        if (empty($filename)) {
            // If no filename is provided, return a 400 Bad Request response
            http_response_code(400);
            echo 'Filename is required.';
            exit;
        }

        // Search candidate directories under resources/images for the profile image
        $searchPaths = [
            __DIR__ . '/../../resources/images/profile/',
            __DIR__ . '/../../resources/images/',
        ];

        $filePath = null;
        $safeName = basename(rawurldecode($filename));
        foreach ($searchPaths as $dir) {
            $candidate = $dir . $safeName;
            if (file_exists($candidate) && is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        // On-demand Next-Gen VSE variant generation if .avif or .webp is requested for an older post
        if (!$filePath) {
            $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
            if ($ext === 'avif' || $ext === 'webp') {
                $baseNameNoExt = pathinfo($safeName, PATHINFO_FILENAME);
                $candidateExts = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
                foreach ($searchPaths as $dir) {
                    foreach ($candidateExts as $cExt) {
                        $sourceCandidate = $dir . $baseNameNoExt . '.' . $cExt;
                        if (file_exists($sourceCandidate) && is_file($sourceCandidate)) {
                            \App\services\VisualSupremacyService::processInPlace($sourceCandidate);
                            $newVariantPath = $dir . $safeName;
                            if (file_exists($newVariantPath) && is_file($newVariantPath)) {
                                $filePath = $newVariantPath;
                                break 2;
                            }
                            // Defensive Fallback (David Structural Safety Gate):
                            // If VSE generation fails (e.g. missing avifenc on production host),
                            // NEVER return 404 to an HTML5 <picture> element! Fall back to source.
                            $filePath = $sourceCandidate;
                        }
                    }
                }
            }
        }

        if (!$filePath) {
            // Check for default avatar in resources/images/profile as fallback
            $fallbackPath = __DIR__ . '/../../resources/images/profile/avatarM.png';
            if (file_exists($fallbackPath)) {
                $filePath = $fallbackPath;
            } else {
                http_response_code(404);
                echo 'File not found.';
                exit;
            }
        }

        // Get the file's MIME type
        $mimeType = 'image/jpeg';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $filePath);
            if (is_string($detected)) {
                $mimeType = $detected;
            }
            finfo_close($finfo);
        }

        // Set the appropriate headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . (string)filesize($filePath));
        header('Cache-Control: public, max-age=86400');

        // Read the file and output its contents
        readfile($filePath);
        exit;
    }


    /**
     * @return never
     */
    public function PostDir($imgName) {
        $filename = $imgName;

        if (empty($filename)) {
            // If no filename is provided, return a 400 Bad Request response
            http_response_code(400);
            echo 'Filename is required.';
            exit;
        }

        // Search candidate directories under resources/images for post images
        $searchPaths = [
            __DIR__ . '/../../resources/images/post/',
            __DIR__ . '/../../resources/images/',
            __DIR__ . '/../../resources/images/profile/',
            __DIR__ . '/../../public/images/',
            __DIR__ . '/../../public/images/journals/',
        ];

        $filePath = null;
        $safeName = basename(rawurldecode($filename));
        foreach ($searchPaths as $dir) {
            $candidate = $dir . $safeName;
            if (file_exists($candidate) && is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        // On-demand Next-Gen VSE variant generation if .avif or .webp is requested for an older post
        if (!$filePath) {
            $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
            if ($ext === 'avif' || $ext === 'webp') {
                $baseNameNoExt = pathinfo($safeName, PATHINFO_FILENAME);
                $candidateExts = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
                foreach ($searchPaths as $dir) {
                    foreach ($candidateExts as $cExt) {
                        $sourceCandidate = $dir . $baseNameNoExt . '.' . $cExt;
                        if (file_exists($sourceCandidate) && is_file($sourceCandidate)) {
                            \App\services\VisualSupremacyService::processInPlace($sourceCandidate);
                            $newVariantPath = $dir . $safeName;
                            if (file_exists($newVariantPath) && is_file($newVariantPath)) {
                                $filePath = $newVariantPath;
                                break 2;
                            }
                            // Defensive Fallback (David Structural Safety Gate):
                            // If VSE generation fails (e.g. missing avifenc on production host),
                            // NEVER return 404 to an HTML5 <picture> element! Fall back to source.
                            $filePath = $sourceCandidate;
                        }
                    }
                }
            }
        }

        if (!$filePath) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'File not found.';
            exit;
        }

        // Get the file's MIME type
        $mimeType = 'image/jpeg';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $filePath);
            if (is_string($detected)) {
                $mimeType = $detected;
            }
            finfo_close($finfo);
        }

        // Set the appropriate headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . (string)filesize($filePath));
        header('Cache-Control: public, max-age=86400');

        // Read the file and output its contents
        readfile($filePath);
        exit;
    }

    /**
     * Stream a reel video with HTTP 206 Byte-Range support for iOS/Android/Desktop media players.
     *
     * @param string $videoName
     * @return never
     */
    public function StreamReel(string $videoName): never
    {
        if (empty($videoName)) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Video filename is required.';
            exit;
        }

        $safeName = basename(rawurldecode($videoName));
        $candidatePaths = [
            __DIR__ . '/../../resources/videos/reels/' . $safeName,
            __DIR__ . '/../../public/resources/videos/reels/' . $safeName, // Legacy fallback
        ];

        $filePath = null;
        foreach ($candidatePaths as $candidate) {
            if (is_file($candidate) && file_exists($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if ($filePath === null) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'Video file not found.';
            exit;
        }

        // Determine MIME type
        $mimeType = 'video/mp4';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $filePath);
            if (is_string($detected) && str_starts_with($detected, 'video/')) {
                $mimeType = $detected;
            }
            finfo_close($finfo);
        }

        $fileSize = (int)filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;

        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', 'Off');

        header('Accept-Ranges: bytes');
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: private, max-age=3600');

        // Handle HTTP Range Header (RFC 7233)
        $httpRange = $_SERVER['HTTP_RANGE'] ?? null;
        if (!empty($httpRange) && preg_match('/^bytes=\s*(\d*)-(\d*)$/i', $httpRange, $matches)) {
            $startVal = filter_var($matches[1], FILTER_VALIDATE_INT);
            $endVal = filter_var($matches[2], FILTER_VALIDATE_INT);

            if ($startVal === false && $endVal !== false) {
                // Suffix range: last N bytes (bytes=-500)
                $start = max(0, $fileSize - $endVal);
            } elseif ($startVal !== false && $endVal === false) {
                // Open-ended range: from byte N to EOF (bytes=500-)
                $start = $startVal;
            } elseif ($startVal !== false && $endVal !== false) {
                // Bounded range: bytes=start-end
                $start = $startVal;
                $end = min($fileSize - 1, $endVal);
            }

            if ($start > $end || $start >= $fileSize) {
                http_response_code(416);
                header("Content-Range: bytes */{$fileSize}");
                exit;
            }

            http_response_code(206);
            $length = $end - $start + 1;
            header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
            header("Content-Length: {$length}");
        } else {
            http_response_code(200);
            header("Content-Length: {$fileSize}");
        }

        // Stream file chunks safely without exhausting memory limit
        $fp = fopen($filePath, 'rb');
        if ($fp === false) {
            http_response_code(500);
            header('Content-Type: text/plain');
            echo 'Unable to open video stream.';
            exit;
        }

        fseek($fp, $start);
        $remaining = $end - $start + 1;
        $chunkSize = 64 * 1024; // 64KB per chunk

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        while (!feof($fp) && $remaining > 0 && !connection_aborted()) {
            $readSize = min($chunkSize, $remaining);
            $buffer = fread($fp, $readSize);
            if ($buffer === false) {
                break;
            }
            echo $buffer;
            flush();
            $remaining -= strlen($buffer);
        }

        fclose($fp);
        exit;
    }

    /**
     * Serve reel video thumbnail images.
     *
     * @param string $thumbName
     * @return never
     */
    public function ReelThumb(string $thumbName): never
    {
        if (empty($thumbName)) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Thumbnail filename is required.';
            exit;
        }

        $safeName = basename(rawurldecode($thumbName));
        $candidatePaths = [
            __DIR__ . '/../../resources/images/reels/thumbs/' . $safeName,
            __DIR__ . '/../../public/resources/images/reels/thumbs/' . $safeName, // Legacy fallback
        ];

        $filePath = null;
        foreach ($candidatePaths as $candidate) {
            if (is_file($candidate) && file_exists($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if ($filePath === null) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'Thumbnail not found.';
            exit;
        }

        $mimeType = 'image/jpeg';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $filePath);
            if (is_string($detected) && str_starts_with($detected, 'image/')) {
                $mimeType = $detected;
            }
            finfo_close($finfo);
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . (string)filesize($filePath));
        header('Cache-Control: public, max-age=86400');

        readfile($filePath);
        exit;
    }

    /**
     * Stream a post video with HTTP 206 Byte-Range support for iOS/Android/Desktop media players.
     *
     * @param string $videoName
     * @return never
     */
    public function StreamPostVideo(string $videoName): never
    {
        if (empty($videoName)) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Video filename is required.';
            exit;
        }

        $safeName = basename(rawurldecode($videoName));
        $candidatePaths = [
            __DIR__ . '/../../resources/videos/posts/' . $safeName,
            __DIR__ . '/../../resources/images/post/' . $safeName,
            __DIR__ . '/../../public/resources/videos/posts/' . $safeName,
        ];

        $filePath = null;
        foreach ($candidatePaths as $candidate) {
            if (is_file($candidate) && file_exists($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if ($filePath === null) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'Video file not found.';
            exit;
        }

        $mimeType = 'video/mp4';
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $filePath);
            if (is_string($detected) && str_starts_with($detected, 'video/')) {
                $mimeType = $detected;
            }
            finfo_close($finfo);
        }

        $fileSize = (int)filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;

        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', 'Off');

        header('Accept-Ranges: bytes');
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: private, max-age=3600');

        $httpRange = $_SERVER['HTTP_RANGE'] ?? null;
        if (!empty($httpRange) && preg_match('/^bytes=\s*(\d*)-(\d*)$/i', $httpRange, $matches)) {
            $startVal = filter_var($matches[1], FILTER_VALIDATE_INT);
            $endVal = filter_var($matches[2], FILTER_VALIDATE_INT);

            if ($startVal === false && $endVal !== false) {
                $start = max(0, $fileSize - $endVal);
            } elseif ($startVal !== false && $endVal === false) {
                $start = $startVal;
            } elseif ($startVal !== false && $endVal !== false) {
                $start = $startVal;
                $end = min($fileSize - 1, $endVal);
            }

            if ($start > $end || $start >= $fileSize) {
                http_response_code(416);
                header("Content-Range: bytes */{$fileSize}");
                exit;
            }

            http_response_code(206);
            $length = $end - $start + 1;
            header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
            header("Content-Length: {$length}");
        } else {
            http_response_code(200);
            header("Content-Length: {$fileSize}");
        }

        $fp = fopen($filePath, 'rb');
        if ($fp === false) {
            http_response_code(500);
            header('Content-Type: text/plain');
            echo 'Unable to open video stream.';
            exit;
        }

        fseek($fp, $start);
        $remaining = $end - $start + 1;
        $chunkSize = 64 * 1024;

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        while (!feof($fp) && $remaining > 0 && !connection_aborted()) {
            $readSize = min($chunkSize, $remaining);
            $buffer = fread($fp, $readSize);
            if ($buffer === false) {
                break;
            }
            echo $buffer;
            flush();
            $remaining -= strlen($buffer);
        }

        fclose($fp);
        exit;
    }

    /**
     * Stream an external linked video (e.g. Google Photos MP4 stream) behind a sovereign
     * same-origin HTTP 206 Byte-Range streaming proxy with HMAC cryptographic validation.
     *
     * @return never
     */
    public function StreamExternalVideo(): never
    {
        $targetUrl = (string)($_GET['url'] ?? '');
        $signature = (string)($_GET['sig'] ?? '');

        if ($targetUrl === '' || $signature === '') {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Target video URL and cryptographic signature are required.';
            exit;
        }

        // 1. HMAC Verification (Constant-Time)
        if (!\App\services\LinkPreviewService::verifyStreamSignature($targetUrl, $signature)) {
            http_response_code(403);
            header('Content-Type: text/plain');
            echo 'Access denied: invalid or tampered video stream signature.';
            exit;
        }

        // 2. Strict SSRF Hardening (Dr. Viktor Kovács & Marcus Gate)
        $parsed = parse_url($targetUrl);
        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');

        if ($scheme !== 'https' || empty($host)) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Invalid stream protocol.';
            exit;
        }

        $isGoogleHost = preg_match('#^(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|googlevideo\.com)$#i', $host) === 1;
        if (!$isGoogleHost) {
            http_response_code(403);
            header('Content-Type: text/plain');
            echo 'Access denied: untrusted remote media host.';
            exit;
        }

        // 3. Release Session Lock to prevent blocking concurrent browser requests
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // 3b. Sovereign Local SSD Cache Fast-Path (ATG Zero-Latency & Token-Expiry Shield)
        $cacheFileName = 'ext_' . md5($targetUrl) . '.mp4';
        $cacheFilePath = __DIR__ . '/../../resources/videos/posts/' . $cacheFileName;
        if (is_file($cacheFilePath) && filesize($cacheFilePath) > 4096) {
            $this->StreamPostVideo($cacheFileName);
        }

        // 4. Clean all output buffers and disable compression before binary streaming
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', 'Off');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // 5. Initialize cURL pipeline
        $ch = curl_init();
        if ($ch === false) {
            http_response_code(500);
            header('Content-Type: text/plain');
            echo 'Internal streaming initialization error.';
            exit;
        }

        $headers = [
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept: video/webm,video/ogg,video/*;q=0.9,application/ogg;q=0.7,audio/*;q=0.6,*/*;q=0.5',
        ];

        $isHeadRequest = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'HEAD';

        // Forward Client Range Header
        $clientRange = $_SERVER['HTTP_RANGE'] ?? null;
        if (!$isHeadRequest && !empty($clientRange) && preg_match('/^bytes=\s*(\d*-\d*)$/i', $clientRange, $rm)) {
            curl_setopt($ch, CURLOPT_RANGE, $rm[1]);
        }

        if ($isHeadRequest) {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }

        $currentUpstreamStatus = 200;

        curl_setopt_array($ch, [
            CURLOPT_URL => $targetUrl,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADERFUNCTION => function ($ch, $headerLine) use (&$currentUpstreamStatus) {
                $trimmed = trim($headerLine);
                if ($trimmed === '') {
                    return strlen($headerLine);
                }
                if (preg_match('#^HTTP/[0-9.]+\s+(\d+)\s*(.*)$#i', $trimmed, $sm)) {
                    $currentUpstreamStatus = (int)$sm[1];
                    // Only forward final non-redirect status codes to client
                    if ($currentUpstreamStatus < 300 || $currentUpstreamStatus >= 400) {
                        http_response_code($currentUpstreamStatus);
                    }
                    return strlen($headerLine);
                }
                // Never forward Content-Type or Content-Length from intermediate 3xx redirect hops
                if ($currentUpstreamStatus >= 300 && $currentUpstreamStatus < 400) {
                    return strlen($headerLine);
                }
                $lower = strtolower($trimmed);
                if (str_starts_with($lower, 'content-type:')
                    || str_starts_with($lower, 'content-range:')
                    || str_starts_with($lower, 'content-length:')
                    || str_starts_with($lower, 'accept-ranges:')
                ) {
                    header($trimmed, true);
                }
                return strlen($headerLine);
            },
            CURLOPT_WRITEFUNCTION => function ($ch, $chunk) {
                if (connection_aborted()) {
                    return 0; // Abort cURL transfer immediately if client disconnected
                }
                echo $chunk;
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                return strlen($chunk);
            },
        ]);

        header('Cache-Control: private, max-age=3600');
        header('Accept-Ranges: bytes');

        curl_exec($ch);
        curl_close($ch);
        exit;
    }
}
