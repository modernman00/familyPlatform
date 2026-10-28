<?php
declare(strict_types=1);

namespace App\model;

use Src\InnerJoin;
use Src\Select;
use Src\SelectFn;
use Src\Update;


final class Post extends Select
{

    static function commentLink2Post($postNo): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'comment', identifier1: "post_no");
        return parent::selectFn2(query: $query, bind: [$postNo]);
    }

    /**
     * @param array|null|string $imgPath
     */
    public static function commentLink2Img(array|string|null $imgPath): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'comment', identifier1: "picture");
        return parent::selectFn2(query: $query, bind: [$imgPath]);
    }

    public static function commentByNo(int|string $commentNo): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'comment', identifier1: "comment_no");
        $result = parent::selectFn2(query: $query, bind: [$commentNo]);
        return $result[0] ?? [];
    }


    public static function postByNo(int|string $postNo): array
    {
        return SelectFn::selectOneRow('post', 'post_no', (string) $postNo) ?? [];
    }

    static function postLink2Id(string $id): array
    {
        return SelectFn::selectOneRow('post', 'id', (string) $id) ?? [];
    }

    static function getAllPost(): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ALL", table: 'post', orderBy: "ORDER BY post_no DESC");
        return parent::selectFn2(query: $query);
    }

    //  static function getAllEvents() : array
    // {
    //     $query = parent::formAndMatchQuery(selection: "SELECT_ALL", table: 'events');
    //     return parent::selectFn2(query: $query);
    // }

    /**
     * get all posts and the profile pics
     * @return array
     */

    public static function getAllPostProfilePics(): array
    {
        $query = "SELECT post.*, profilePics.img 
                  FROM post 
                  LEFT JOIN profilePics ON post.id = profilePics.id 
                  ORDER BY post.post_no DESC";
        return parent::selectFn2($query);
    }

    public function getAllPostProfilePics2(): array
    {
        $query = "SELECT post.*, profilePics.img 
                  FROM post 
                  LEFT JOIN profilePics ON post.id = profilePics.id 
                  ORDER BY post.date_created ASC";
        return parent::selectFn2($query);
    }


    public static function getAllCommentProfilePics(): array
    {
        $query = "SELECT comment.*, profilePics.img 
                  FROM comment 
                  LEFT JOIN profilePics ON comment.id = profilePics.id 
                  ORDER BY comment.date_created ASC";
        return parent::selectFn2($query);
    }

    /**
     * @param array|null|string $id
     */
    public static function getProfilePicsById(array|string|null $id): string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_COL_ID", table: 'profilePics', column: 'img', identifier1: "id");
        $result =  parent::selectFn2(query: $query, bind: [$id]);
        return (string)($result[0]['img'] ?? '');
    }

    /**
     * Undocumented function
     *
     * @param [type] $custNo this is the id
     *  get the
     */
    public static function getAllPostPics(string $custNo): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'post', identifier1: "id");
        return parent::selectFn2(query: $query, bind: [$custNo]);
    }

    public static function getProfilePics($id): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'profilePics', identifier1: "id");
        return parent::selectFn2(query: $query, bind: [$id]);
    }

    static function getAllComments(): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ALL", table: 'comment');
        return parent::selectFn2(query: $query);
    }

    static function getUnpublishedPost(): array
    {
        $query = parent::formAndMatchQuery(
            selection: "SELECT_ONE", 
            table: 'post', 
            identifier1: 'post_status', 
            orderBy: "ORDER BY post_no DESC"
        );
        $result = parent::selectFn2(query: $query, bind: ["new"]);
        return $result;
    }

    

    static function getUnpublishedComment(): array
    {
        $query = "SELECT * FROM comment WHERE comment_status = ? ORDER BY comment_no DESC";
        return parent::selectFn2(query: $query, bind: ["new"]);
    }

    static function updatePostByStatusAsPublished($postNo): bool
    {
        $newUpdate = new Update('post');
        $result =  $newUpdate->updateTable(column: 'post_status', columnAnswer: "published", identifier: 'post_no', identifierAnswer: $postNo);
        if (!$result) {
            msgException(500, "Database update failed");
            return false;
        }
        return $result;
    }

    /**
     * @param int|null|string $commentNo
     */
    static function updateCommentByStatusAsPublished(int|string|null $commentNo): bool
    {
        if ($commentNo === null) {
            msgException(406, 'comment number not well formed');
            return false;
        }

        $newUpdate = new Update('comment');
        $result =  $newUpdate->updateTable(column: 'comment_status', columnAnswer: "published", identifier: 'comment_no', identifierAnswer: $commentNo);
        if (!$result) {
            msgException(500, "Database update failed");
            return false;
        }
        return $result;
    }


    static function fetchUpdatedLikes()
    {
        // Define a recent time window in seconds, e.g., 10 seconds
        $recentTimeWindow = 2;

        $stmt = parent::connect2()->prepare("SELECT post_no, postFamCode, post_likes 
                FROM post
                WHERE TIMESTAMPDIFF(SECOND, likes_updated_at, NOW()) <= :recentTimeWindow
            ");

        $stmt->bindParam(':recentTimeWindow', $recentTimeWindow);
        $stmt->execute();

        // Fetch and return all matching rows
        return $stmt->fetchAll();
    }

    /**
     * Retrieves all images posted by a specific author across both post and images tables.
     *
     * @param string $authorId
     * @return array<int, array<string, mixed>>
     * @param string $authorId Member ID
     * @param bool $publicOnly When true, returns only images marked as public
     * @return array<int, array{id: string, img: string, caption: string, likes: int, where_from: string, created_at: string, post_no: int|null, is_public: int}>
     */
    public static function getAllImagesByAuthor(string $authorId, bool $publicOnly = false): array
    {
        $allImages = [];
        $baseDir = dirname(__DIR__, 2);
        $searchDirs = [
            $baseDir . '/resources/images/post/',
            $baseDir . '/resources/images/',
            $baseDir . '/resources/images/profile/',
        ];

        $checkFileExists = static function (string $filename) use ($searchDirs): bool {
            if (empty($filename)) return false;
            $safeName = basename(rawurldecode($filename));
            foreach ($searchDirs as $dir) {
                if (file_exists($dir . $safeName) && is_file($dir . $safeName)) {
                    return true;
                }
            }
            return false;
        };

        // Preload visibility map from images table
        $visibilityMap = [];
        $hasIsPublicColumn = true;
        try {
            $queryVis = "SELECT img, is_public FROM images WHERE id = ?";
            $stmtVis = parent::connect2()->prepare($queryVis);
            $stmtVis->execute([$authorId]);
            foreach ($stmtVis->fetchAll(\PDO::FETCH_ASSOC) as $vRow) {
                $vImg = trim((string)$vRow['img']);
                if (!empty($vImg)) {
                    $visibilityMap[$vImg] = (int)($vRow['is_public'] ?? 0);
                }
            }
        } catch (\Throwable $e) {
            $hasIsPublicColumn = false;
        }

        // 1. Fetch images from published posts
        $query = "SELECT post_no, id, fullName, postMessage, post_img0, post_img1, post_img2, post_img3, post_img4, post_img5, date_created, post_likes 
                  FROM post 
                  WHERE id = ? AND post_status = 'published' AND date_deleted IS NULL 
                  ORDER BY date_created DESC";
        $stmt = parent::connect2()->prepare($query);
        $stmt->execute([$authorId]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($posts as $post) {
            for ($i = 0; $i <= 5; $i++) {
                $col = 'post_img' . $i;
                if (!empty($post[$col])) {
                    $imgName = trim((string)$post[$col]);
                    if (!empty($imgName) && $checkFileExists($imgName)) {
                        $isPublic = (int)($visibilityMap[$imgName] ?? 0);
                        if ($publicOnly && $isPublic !== 1) {
                            continue;
                        }
                        $allImages[] = [
                            'id' => (string)$post['id'],
                            'img' => $imgName,
                            'caption' => (string)($post['postMessage'] ?? ''),
                            'likes' => (int)($post['post_likes'] ?? 0),
                            'where_from' => 'post',
                            'created_at' => (string)($post['date_created'] ?? ''),
                            'post_no' => (int)$post['post_no'],
                            'is_public' => $isPublic,
                        ];
                    }
                }
            }
        }

        // 2. Fetch images from images table
        $images = [];
        try {
            if ($hasIsPublicColumn) {
                $query2 = "SELECT no, id, img, where_from, caption, likes, is_public, created_at 
                           FROM images 
                           WHERE id = ? AND img IS NOT NULL AND img != '' AND deleted_at IS NULL 
                           ORDER BY created_at DESC";
            } else {
                $query2 = "SELECT no, id, img, where_from, caption, likes, created_at 
                           FROM images 
                           WHERE id = ? AND img IS NOT NULL AND img != '' AND deleted_at IS NULL 
                           ORDER BY created_at DESC";
            }
            $stmt2 = parent::connect2()->prepare($query2);
            $stmt2->execute([$authorId]);
            $images = $stmt2->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            $images = [];
        }

        $existingImgNames = array_column($allImages, 'img');
        foreach ($images as $imgRow) {
            $imgName = trim((string)$imgRow['img']);
            if (!empty($imgName) && !in_array($imgName, $existingImgNames, true) && $checkFileExists($imgName)) {
                $isPublic = (int)($imgRow['is_public'] ?? ($visibilityMap[$imgName] ?? 0));
                if ($publicOnly && $isPublic !== 1) {
                    continue;
                }
                $allImages[] = [
                    'id' => (string)$imgRow['id'],
                    'img' => $imgName,
                    'caption' => (string)($imgRow['caption'] ?? ''),
                    'likes' => (int)($imgRow['likes'] ?? 0),
                    'where_from' => (string)($imgRow['where_from'] ?? 'gallery'),
                    'created_at' => (string)($imgRow['created_at'] ?? ''),
                    'post_no' => null,
                    'is_public' => $isPublic,
                ];
                $existingImgNames[] = $imgName;
            }
        }

        return $allImages;
    }

    /**
     * Toggle or set an image's public/private visibility status.
     *
     * @param string $authorId
     * @param string $imageName
     * @param int $isPublic (1 for public, 0 for private)
     * @return bool
     */
    public static function setImageVisibility(string $authorId, string $imageName, int $isPublic): bool
    {
        try {
            $db = parent::connect2();
            $safeImg = trim($imageName);
            $isPublic = $isPublic ? 1 : 0;

            // Check if image already exists in images table for this user
            $checkStmt = $db->prepare("SELECT no FROM images WHERE id = ? AND img = ?");
            $checkStmt->execute([$authorId, $safeImg]);
            $existing = $checkStmt->fetch(\PDO::FETCH_ASSOC);

            if ($existing && !empty($existing['no'])) {
                $upd = $db->prepare("UPDATE images SET is_public = ? WHERE id = ? AND img = ?");
                return (bool)$upd->execute([$isPublic, $authorId, $safeImg]);
            }

            // Insert new record in images table
            $ins = $db->prepare("INSERT INTO images (id, img, where_from, is_public, created_at) VALUES (?, ?, 'gallery', ?, CURRENT_TIMESTAMP)");
            return (bool)$ins->execute([$authorId, $safeImg, $isPublic]);
        } catch (\Throwable $e) {
            error_log('[Post::setImageVisibility] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete an image owned by the author from images and post tables.
     *
     * @param string $authorId
     * @param string $imageName
     * @return array{status: int, success: bool, message?: string, error?: string}
     */
    public static function deleteGalleryImage(string $authorId, string $imageName): array
    {
        try {
            $db = parent::connect2();
            $safeImg = basename(trim($imageName));

            if ($safeImg === '') {
                return [
                    'status' => 400,
                    'success' => false,
                    'error' => 'Invalid picture name provided'
                ];
            }

            // 1. Verify ownership: check if the image belongs to the author in images table
            $chkOwnImg = $db->prepare("SELECT no FROM images WHERE id = ? AND img = ? AND deleted_at IS NULL LIMIT 1");
            $chkOwnImg->execute([$authorId, $safeImg]);
            $ownImgRow = $chkOwnImg->fetch(\PDO::FETCH_ASSOC);

            // 2. Check if the image belongs to the author in post table
            $chkOwnPost = $db->prepare("SELECT post_no FROM post WHERE id = ? AND date_deleted IS NULL AND (? IN (post_img0, post_img1, post_img2, post_img3, post_img4, post_img5)) LIMIT 1");
            $chkOwnPost->execute([$authorId, $safeImg]);
            $ownPostRow = $chkOwnPost->fetch(\PDO::FETCH_ASSOC);

            $isOwner = (!empty($ownImgRow) || !empty($ownPostRow));

            if (!$isOwner) {
                // Check if the picture exists for any other user (IDOR detection)
                $chkOtherImg = $db->prepare("SELECT id FROM images WHERE img = ? AND deleted_at IS NULL LIMIT 1");
                $chkOtherImg->execute([$safeImg]);
                $otherImg = $chkOtherImg->fetch(\PDO::FETCH_ASSOC);

                $chkOtherPost = $db->prepare("SELECT id FROM post WHERE date_deleted IS NULL AND (? IN (post_img0, post_img1, post_img2, post_img3, post_img4, post_img5)) LIMIT 1");
                $chkOtherPost->execute([$safeImg]);
                $otherPost = $chkOtherPost->fetch(\PDO::FETCH_ASSOC);

                if (!empty($otherImg) || !empty($otherPost)) {
                    return [
                        'status' => 403,
                        'success' => false,
                        'error' => 'You can only delete your own pictures'
                    ];
                }

                return [
                    'status' => 404,
                    'success' => false,
                    'error' => 'Picture not found in your gallery'
                ];
            }

            // 3. Mark as soft-deleted in images table for this author
            $delImages = $db->prepare("UPDATE images SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND img = ?");
            $delImages->execute([$authorId, $safeImg]);

            // 4. Atomically clear this image from all post image slots owned by this author
            $clearPostImgs = $db->prepare("UPDATE post SET 
                post_img0 = CASE WHEN post_img0 = ? THEN NULL ELSE post_img0 END,
                post_img1 = CASE WHEN post_img1 = ? THEN NULL ELSE post_img1 END,
                post_img2 = CASE WHEN post_img2 = ? THEN NULL ELSE post_img2 END,
                post_img3 = CASE WHEN post_img3 = ? THEN NULL ELSE post_img3 END,
                post_img4 = CASE WHEN post_img4 = ? THEN NULL ELSE post_img4 END,
                post_img5 = CASE WHEN post_img5 = ? THEN NULL ELSE post_img5 END
                WHERE id = ? AND (? IN (post_img0, post_img1, post_img2, post_img3, post_img4, post_img5))");
            $clearPostImgs->execute([$safeImg, $safeImg, $safeImg, $safeImg, $safeImg, $safeImg, $authorId, $safeImg]);

            // 5. Soft-delete posts owned by this author that now have empty text and zero pictures
            $softDeleteEmptyPosts = $db->prepare("UPDATE post SET date_deleted = CURRENT_TIMESTAMP 
                WHERE id = ? 
                AND date_deleted IS NULL 
                AND (postMessage IS NULL OR TRIM(postMessage) = '') 
                AND post_img0 IS NULL 
                AND post_img1 IS NULL 
                AND post_img2 IS NULL 
                AND post_img3 IS NULL 
                AND post_img4 IS NULL 
                AND post_img5 IS NULL");
            $softDeleteEmptyPosts->execute([$authorId]);

            // 6. Check if physical image file is referenced anywhere else before unlinking
            $chkPostRef = $db->prepare("SELECT COUNT(*) FROM post WHERE date_deleted IS NULL AND (? IN (post_img0, post_img1, post_img2, post_img3, post_img4, post_img5))");
            $chkPostRef->execute([$safeImg]);
            $postRefCount = (int)$chkPostRef->fetchColumn();

            $chkImgRef = $db->prepare("SELECT COUNT(*) FROM images WHERE deleted_at IS NULL AND img = ?");
            $chkImgRef->execute([$safeImg]);
            $imgRefCount = (int)$chkImgRef->fetchColumn();

            $chkProfRef = $db->prepare("SELECT COUNT(*) FROM profilePics WHERE img = ?");
            $chkProfRef->execute([$safeImg]);
            $profRefCount = (int)$chkProfRef->fetchColumn();

            if ($postRefCount === 0 && $imgRefCount === 0 && $profRefCount === 0) {
                $primaryPath = 'resources/images/post/' . $safeImg;
                $publicPath = 'public/img/post/' . $safeImg;
                if (is_file($primaryPath)) {
                    @unlink($primaryPath);
                }
                if (is_file($publicPath)) {
                    @unlink($publicPath);
                }
            }

            return [
                'status' => 200,
                'success' => true,
                'message' => 'Picture removed from your gallery successfully'
            ];
        } catch (\Throwable $e) {
            error_log('[Post::deleteGalleryImage] Error: ' . $e->getMessage());
            return [
                'status' => 500,
                'success' => false,
                'error' => 'An unexpected server error occurred while deleting the picture'
            ];
        }
    }

    /**
     * Atomically delete multiple images owned by the author from images and post tables.
     * Enforces an all-or-nothing PDO transaction so mixed ownership (IDOR) rolls back completely.
     *
     * @param string $authorId
     * @param array<int, mixed> $imageNames
     * @return array{status: int, success: bool, deleted_count?: int, deleted_images?: array<int, string>, message?: string, error?: string}
     */
    public static function deleteMultipleGalleryImages(string $authorId, array $imageNames): array
    {
        $cleanNames = [];
        foreach ($imageNames as $raw) {
            if (!is_string($raw) && !is_numeric($raw)) {
                continue;
            }
            $base = basename(trim((string)$raw));
            $safe = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $base) ?? '';
            if ($safe !== '' && !in_array($safe, $cleanNames, true)) {
                $cleanNames[] = $safe;
            }
        }

        if (empty($cleanNames)) {
            return [
                'status' => 400,
                'success' => false,
                'error' => 'No valid picture names provided'
            ];
        }

        if (count($cleanNames) > 50) {
            return [
                'status' => 400,
                'success' => false,
                'error' => 'You can delete a maximum of 50 pictures at a time'
            ];
        }

        $db = null;
        try {
            $db = parent::connect2();
            $inTransaction = $db->inTransaction();
            if (!$inTransaction) {
                $db->beginTransaction();
            }

            $chkOwnImg = $db->prepare("SELECT no FROM images WHERE id = ? AND img = ? AND deleted_at IS NULL LIMIT 1");
            $chkOwnPost = $db->prepare("SELECT post_no FROM post WHERE id = ? AND date_deleted IS NULL AND (? IN (post_img0, post_img1, post_img2, post_img3, post_img4, post_img5)) LIMIT 1");
            $chkOtherImg = $db->prepare("SELECT id FROM images WHERE img = ? AND deleted_at IS NULL LIMIT 1");
            $chkOtherPost = $db->prepare("SELECT id FROM post WHERE date_deleted IS NULL AND (? IN (post_img0, post_img1, post_img2, post_img3, post_img4, post_img5)) LIMIT 1");

            // 1. Pre-verify ownership of every picture in the batch before mutating any state
            foreach ($cleanNames as $safeImg) {
                $chkOwnImg->execute([$authorId, $safeImg]);
                $ownImgRow = $chkOwnImg->fetch(\PDO::FETCH_ASSOC);

                $chkOwnPost->execute([$authorId, $safeImg]);
                $ownPostRow = $chkOwnPost->fetch(\PDO::FETCH_ASSOC);

                if (empty($ownImgRow) && empty($ownPostRow)) {
                    $chkOtherImg->execute([$safeImg]);
                    $otherImg = $chkOtherImg->fetch(\PDO::FETCH_ASSOC);

                    $chkOtherPost->execute([$safeImg]);
                    $otherPost = $chkOtherPost->fetch(\PDO::FETCH_ASSOC);

                    if (!$inTransaction && $db->inTransaction()) {
                        $db->rollBack();
                    }

                    if (!empty($otherImg) || !empty($otherPost)) {
                        return [
                            'status' => 403,
                            'success' => false,
                            'error' => 'You can only delete your own pictures'
                        ];
                    }

                    return [
                        'status' => 404,
                        'success' => false,
                        'error' => 'One or more selected pictures were not found in your gallery'
                    ];
                }
            }

            // 2. Execute soft-delete and slot clearing for each verified image
            $delImages = $db->prepare("UPDATE images SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND img = ?");
            $clearPostImgs = $db->prepare("UPDATE post SET 
                post_img0 = CASE WHEN post_img0 = ? THEN NULL ELSE post_img0 END,
                post_img1 = CASE WHEN post_img1 = ? THEN NULL ELSE post_img1 END,
                post_img2 = CASE WHEN post_img2 = ? THEN NULL ELSE post_img2 END,
                post_img3 = CASE WHEN post_img3 = ? THEN NULL ELSE post_img3 END,
                post_img4 = CASE WHEN post_img4 = ? THEN NULL ELSE post_img4 END,
                post_img5 = CASE WHEN post_img5 = ? THEN NULL ELSE post_img5 END
                WHERE id = ? AND (? IN (post_img0, post_img1, post_img2, post_img3, post_img4, post_img5))");

            foreach ($cleanNames as $safeImg) {
                $delImages->execute([$authorId, $safeImg]);
                $clearPostImgs->execute([$safeImg, $safeImg, $safeImg, $safeImg, $safeImg, $safeImg, $authorId, $safeImg]);
            }

            // 3. Soft-delete posts owned by this author that now have empty text and zero pictures
            $softDeleteEmptyPosts = $db->prepare("UPDATE post SET date_deleted = CURRENT_TIMESTAMP 
                WHERE id = ? 
                AND date_deleted IS NULL 
                AND (postMessage IS NULL OR TRIM(postMessage) = '') 
                AND post_img0 IS NULL 
                AND post_img1 IS NULL 
                AND post_img2 IS NULL 
                AND post_img3 IS NULL 
                AND post_img4 IS NULL 
                AND post_img5 IS NULL");
            $softDeleteEmptyPosts->execute([$authorId]);

            if (!$inTransaction && $db->inTransaction()) {
                $db->commit();
            }

            // 4. After commit, check physical file references and unlink unreferenced files
            $chkPostRef = $db->prepare("SELECT COUNT(*) FROM post WHERE date_deleted IS NULL AND (? IN (post_img0, post_img1, post_img2, post_img3, post_img4, post_img5))");
            $chkImgRef = $db->prepare("SELECT COUNT(*) FROM images WHERE deleted_at IS NULL AND img = ?");
            $chkProfRef = $db->prepare("SELECT COUNT(*) FROM profilePics WHERE img = ?");

            foreach ($cleanNames as $safeImg) {
                $chkPostRef->execute([$safeImg]);
                $postRefCount = (int)$chkPostRef->fetchColumn();

                $chkImgRef->execute([$safeImg]);
                $imgRefCount = (int)$chkImgRef->fetchColumn();

                $chkProfRef->execute([$safeImg]);
                $profRefCount = (int)$chkProfRef->fetchColumn();

                if ($postRefCount === 0 && $imgRefCount === 0 && $profRefCount === 0) {
                    $primaryPath = 'resources/images/post/' . $safeImg;
                    $publicPath = 'public/img/post/' . $safeImg;
                    if (is_file($primaryPath)) {
                        @unlink($primaryPath);
                    }
                    if (is_file($publicPath)) {
                        @unlink($publicPath);
                    }
                }
            }

            $count = count($cleanNames);
            return [
                'status' => 200,
                'success' => true,
                'deleted_count' => $count,
                'deleted_images' => $cleanNames,
                'message' => $count === 1
                    ? 'Picture removed from your gallery successfully'
                    : "{$count} pictures removed from your gallery successfully"
            ];
        } catch (\Throwable $e) {
            if ($db instanceof \PDO && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[Post::deleteMultipleGalleryImages] Error: ' . $e->getMessage());
            return [
                'status' => 500,
                'success' => false,
                'error' => 'An unexpected server error occurred while deleting the selected pictures'
            ];
        }
    }

    /**
     * Atomically increment post view count and log viewer audit record.
     *
     * @param int $postNo
     * @param string $userId
     * @return void
     */
    public static function incrementViews(int $postNo, string $userId = ''): void
    {
        try {
            $pdo = parent::connect2();
            $stmt = $pdo->prepare("UPDATE post SET views_count = views_count + 1 WHERE post_no = ?");
            $stmt->execute([$postNo]);

            if ($userId !== '') {
                $stmtAudit = $pdo->prepare("
                    INSERT INTO post_views (post_no, user_id, view_count, created_at, updated_at)
                    VALUES (?, ?, 1, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE view_count = view_count + 1, updated_at = NOW()
                ");
                $stmtAudit->execute([$postNo, $userId]);
            }
        } catch (\Throwable $e) {
            error_log('[Post::incrementViews] ' . $e->getMessage());
        }
    }

    /**
     * Get the current view count for a post.
     *
     * @param int $postNo
     * @return int
     */
    public static function getViewsCount(int $postNo): int
    {
        try {
            $pdo = parent::connect2();
            $stmt = $pdo->prepare("SELECT views_count FROM post WHERE post_no = ?");
            $stmt->execute([$postNo]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[Post::getViewsCount] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verify whether a given user is the author of the post.
     *
     * @param int $postNo
     * @param string $userId
     * @return bool
     */
    public static function isPostAuthor(int $postNo, string $userId): bool
    {
        if ($postNo <= 0 || $userId === '') {
            return false;
        }
        try {
            $pdo = parent::connect2();
            $stmt = $pdo->prepare("SELECT id FROM post WHERE post_no = ? AND date_deleted IS NULL");
            $stmt->execute([$postNo]);
            $authorId = $stmt->fetchColumn();
            return $authorId !== false && (string)$authorId === (string)$userId;
        } catch (\Throwable $e) {
            error_log('[Post::isPostAuthor] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve the list of users who have viewed a post.
     *
     * @param int $postNo
     * @return array<int, array<string, mixed>>
     */
    public static function getPostViewers(int $postNo): array
    {
        if ($postNo <= 0) {
            return [];
        }
        try {
            $pdo = parent::connect2();
            $stmt = $pdo->prepare("
                SELECT pv.user_id, pv.view_count, pv.created_at, pv.updated_at,
                       COALESCE(p.firstName, '') AS firstName,
                       COALESCE(p.lastName, '') AS lastName,
                       COALESCE(pp.img, '') AS profile_img
                FROM post_views pv
                LEFT JOIN personal p ON pv.user_id = p.id
                LEFT JOIN profilePics pp ON pv.user_id = pp.id
                WHERE pv.post_no = ?
                ORDER BY pv.updated_at DESC
            ");
            $stmt->execute([$postNo]);
            /** @var array<int, array<string, mixed>> $rows */
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            return $rows;
        } catch (\Throwable $e) {
            error_log('[Post::getPostViewers] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Atomically increment comment view count and log viewer audit record.
     *
     * @param int $commentNo
     * @param string $userId
     * @return void
     */
    public static function incrementCommentViews(int $commentNo, string $userId = ''): void
    {
        if ($commentNo <= 0) {
            return;
        }
        try {
            $pdo = parent::connect2();
            $stmt = $pdo->prepare("UPDATE comment SET views_count = views_count + 1 WHERE comment_no = ?");
            $stmt->execute([$commentNo]);

            if ($userId !== '') {
                $stmtAudit = $pdo->prepare("
                    INSERT INTO comment_views (comment_no, user_id, view_count, created_at, updated_at)
                    VALUES (?, ?, 1, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE view_count = view_count + 1, updated_at = NOW()
                ");
                $stmtAudit->execute([$commentNo, $userId]);
            }
        } catch (\Throwable $e) {
            error_log('[Post::incrementCommentViews] ' . $e->getMessage());
        }
    }

    /**
     * Get the current view count for a comment.
     *
     * @param int $commentNo
     * @return int
     */
    public static function getCommentViewsCount(int $commentNo): int
    {
        if ($commentNo <= 0) {
            return 0;
        }
        try {
            $pdo = parent::connect2();
            $stmt = $pdo->prepare("SELECT views_count FROM comment WHERE comment_no = ?");
            $stmt->execute([$commentNo]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[Post::getCommentViewsCount] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verify whether a given user is the author of the comment.
     *
     * @param int $commentNo
     * @param string $userId
     * @return bool
     */
    public static function isCommentAuthor(int $commentNo, string $userId): bool
    {
        if ($commentNo <= 0 || $userId === '') {
            return false;
        }
        try {
            $pdo = parent::connect2();
            $stmt = $pdo->prepare("SELECT id FROM comment WHERE comment_no = ? AND date_deleted IS NULL");
            $stmt->execute([$commentNo]);
            $authorId = $stmt->fetchColumn();
            return $authorId !== false && (string)$authorId === (string)$userId;
        } catch (\Throwable $e) {
            error_log('[Post::isCommentAuthor] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve the list of users who have viewed a comment.
     *
     * @param int $commentNo
     * @return array<int, array<string, mixed>>
     */
    public static function getCommentViewers(int $commentNo): array
    {
        if ($commentNo <= 0) {
            return [];
        }
        try {
            $pdo = parent::connect2();
            $stmt = $pdo->prepare("
                SELECT cv.user_id, cv.view_count, cv.created_at, cv.updated_at,
                       COALESCE(p.firstName, '') AS firstName,
                       COALESCE(p.lastName, '') AS lastName,
                       COALESCE(pp.img, '') AS profile_img
                FROM comment_views cv
                LEFT JOIN personal p ON cv.user_id = p.id
                LEFT JOIN profilePics pp ON cv.user_id = pp.id
                WHERE cv.comment_no = ?
                ORDER BY cv.updated_at DESC
            ");
            $stmt->execute([$commentNo]);
            /** @var array<int, array<string, mixed>> $rows */
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            return $rows;
        } catch (\Throwable $e) {
            error_log('[Post::getCommentViewers] ' . $e->getMessage());
            return [];
        }
    }

}
