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
}
