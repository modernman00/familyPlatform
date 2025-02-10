<?php 

namespace App\controller;

class ServeImgController {
    public function ProfileDir($imgName) {
        $filename = $imgName;

        if (empty($filename)) {
            // If no filename is provided, return a 400 Bad Request response
            http_response_code(400);
            echo 'Filename is required.';
            exit;
        }

        // Define the path to the image directory
        $imageDir = __DIR__ . '/../../resources/asset/profile/';

        // Create the full path to the image file
        $filePath = $imageDir . basename($filename);

        if (!file_exists($filePath)) {
            // If the file does not exist, return a 404 Not Found response
            http_response_code(404);
            echo 'File not found.';
            exit;
        }

        // Get the file's MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        // Set the appropriate headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));

        // Read the file and output its contents
        readfile($filePath);
        exit;
    }


    public function PostDir($imgName) {
        $filename = $imgName;

        if (empty($filename)) {
            // If no filename is provided, return a 400 Bad Request response
            http_response_code(400);
            echo 'Filename is required.';
            exit;
        }

        // Define the path to the image directory
        $imageDir = __DIR__ . '/../../resources/asset/post/';

        // Create the full path to the image file
        $filePath = $imageDir . basename($filename);

        if (!file_exists($filePath)) {
            // If the file does not exist, return a 404 Not Found response
            http_response_code(404);
            echo 'File not found.';
            exit;
        }

        // Get the file's MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        // Set the appropriate headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));

        // Read the file and output its contents
        readfile($filePath);
        exit;
    }
}
