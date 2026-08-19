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

        // Search candidate directories for the image
        $searchPaths = [
            __DIR__ . '/../../public/img/profile/',
            __DIR__ . '/../../resources/images/profile/',
            __DIR__ . '/../../public/avatar/',
            __DIR__ . '/../../resources/images/',
            __DIR__ . '/../../public/img/post/',
            __DIR__ . '/../../resources/images/post/',
            __DIR__ . '/../../public/img/photos/',
        ];

        $filePath = null;
        $safeName = basename($filename);
        foreach ($searchPaths as $dir) {
            $candidate = $dir . $safeName;
            if (file_exists($candidate) && is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if (!$filePath) {
            // Check for default avatars in public/avatar as fallback
            $fallbackPath = __DIR__ . '/../../public/avatar/avatarM.png';
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

        // Search candidate directories for post images
        $searchPaths = [
            __DIR__ . '/../../public/img/post/',
            __DIR__ . '/../../resources/images/post/',
            __DIR__ . '/../../resources/images/',
            __DIR__ . '/../../public/img/photos/',
            __DIR__ . '/../../public/img/profile/',
        ];

        $filePath = null;
        $safeName = basename($filename);
        foreach ($searchPaths as $dir) {
            $candidate = $dir . $safeName;
            if (file_exists($candidate) && is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if (!$filePath) {
            // If the file does not exist, return a 404 Not Found response
            http_response_code(404);
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
}
