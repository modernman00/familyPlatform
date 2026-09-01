<?php
declare(strict_types=1);

namespace App\controller\admin;

use App\classes\Insert;
use App\controller\BaseController;
use Src\CheckToken;
use Src\Utility;
use Src\FileUploader;
use Src\Exceptions\ValidationException;

final class AdminBlogController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $verifyJWT = \Src\functionality\SignIn::verify();
        if (empty($verifyJWT['id'])) {
            throw new \Src\Exceptions\UnauthorisedException("Unauthorized access to administrative dashboard.");
        }
        
        // Optionally, check if user is admin role
        // if ($_SESSION['type'] !== 'admin') { ... }
    }

    public function create(): void
    {
        try {
            parent::viewWithCsp('admin/blog/create');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    public function store(): void
    {
        try {
            // CSRF check
            CheckToken::tokenCheck();

            $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $summary = filter_input(INPUT_POST, 'summary', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $content = $_POST['content'] ?? ''; // rich text, we rely on Quill.js + strict frontend/backend config.
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? 'draft';
            
            if (empty($title) || empty($content)) {
                throw new ValidationException("Title and content are required.");
            }

            // Slug generation
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            $slug .= '-' . substr(uniqid(), -4); // Ensure uniqueness

            $coverImage = null;
            if (!empty($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Ensure directory exists
                $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/public/images/blogs/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                
                $uploadResult = FileUploader::fileUploadSingle($targetDir, 'cover_image');
                $coverImage = '/public/images/blogs/' . $uploadResult['fileName'];
            }

            $insertData = [
                'title' => $title,
                'slug' => $slug,
                'summary' => $summary,
                'content' => $content,
                'author_id' => $_SESSION['id'],
                'cover_image' => $coverImage,
                'status' => $status
            ];

            $result = Insert::submitFormDynamic('blogs', $insertData);

            if ($result) {
                // Check if we need to queue for social media
                if ($status === 'published') {
                    $platforms = [];
                    if (filter_input(INPUT_POST, 'post_facebook', FILTER_VALIDATE_BOOLEAN)) $platforms[] = 'facebook';
                    if (filter_input(INPUT_POST, 'post_instagram', FILTER_VALIDATE_BOOLEAN)) $platforms[] = 'instagram';
                    if (filter_input(INPUT_POST, 'post_linkedin', FILTER_VALIDATE_BOOLEAN)) $platforms[] = 'linkedin';

                    if (!empty($platforms)) {
                        $appUrl = getenv('APP_URL') ?: 'https://familyplatform.test';
                        $blogUrl = rtrim($appUrl, '/') . '/blog/' . $slug;
                        
                        // Calculate allowed words for the summary to keep the total message <= 90 words
                        $titleWords = count(preg_split('/\s+/', $title) ?: []);
                        $footerWords = count(preg_split('/\s+/', "\n\nRead more here: " . $blogUrl) ?: []);
                        
                        $allowedSummaryWords = 90 - $titleWords - $footerWords;
                        if ($allowedSummaryWords < 10) {
                            $allowedSummaryWords = 10; // Fallback just in case title is ridiculously long
                        }
                        
                        $summaryWords = preg_split('/\s+/', trim((string) $summary)) ?: [];
                        if (count($summaryWords) > $allowedSummaryWords) {
                            $summary = implode(' ', array_slice($summaryWords, 0, $allowedSummaryWords)) . '...';
                        }

                        // Construct the message
                        $message = $title . "\n\n" . $summary . "\n\nRead more here: " . $blogUrl;
                        $fullImageUrl = $coverImage ? rtrim($appUrl, '/') . $coverImage : null;
                        
                        $queueFile = BASE_PATH . '/storage/social_queue.json';
                        $queueData = [];
                        
                        if (file_exists($queueFile)) {
                            $queueData = json_decode(file_get_contents($queueFile) ?: '[]', true) ?? [];
                        }
                        
                        $queueData[] = [
                            'message' => $message,
                            'image_url' => $fullImageUrl,
                            'platforms' => $platforms,
                            'queued_at' => time()
                        ];
                        
                        if (!is_dir(dirname($queueFile))) {
                            mkdir(dirname($queueFile), 0777, true);
                        }
                        
                        file_put_contents($queueFile, json_encode($queueData));
                    }
                }

                // Send JSON response for JS handler
                Utility::msgSuccess(200, "Blog published successfully!", ["slug" => $slug]);
            } else {
                throw new \Exception("Failed to save blog to database.");
            }
        } catch (\Throwable $th) {
            Utility::msgException(400, $th->getMessage());
        }
    }
}
