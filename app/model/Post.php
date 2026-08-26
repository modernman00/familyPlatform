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
        return $result[0]['img'];
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
     */
    public static function getAllImagesByAuthor(string $authorId): array
    {
        $allImages = [];

        // 1. Fetch images from published posts
        $query = "SELECT post_no, id, fullName, postMessage, post_img0, post_img1, post_img2, post_img3, post_img4, post_img5, date_created, post_likes 
                  FROM post 
                  WHERE id = ? AND post_status = 'published' 
                  ORDER BY date_created DESC";
        $stmt = parent::connect2()->prepare($query);
        $stmt->execute([$authorId]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($posts as $post) {
            for ($i = 0; $i <= 5; $i++) {
                $col = 'post_img' . $i;
                if (!empty($post[$col])) {
                    $imgName = trim((string)$post[$col]);
                    if (!empty($imgName)) {
                        $allImages[] = [
                            'id' => $post['id'],
                            'img' => $imgName,
                            'caption' => (string)($post['postMessage'] ?? ''),
                            'likes' => (int)($post['post_likes'] ?? 0),
                            'where_from' => 'post',
                            'created_at' => (string)($post['date_created'] ?? ''),
                            'post_no' => $post['post_no'],
                        ];
                    }
                }
            }
        }

        // 2. Fetch images from images table
        $query2 = "SELECT no, id, img, where_from, caption, likes, created_at 
                   FROM images 
                   WHERE id = ? AND img IS NOT NULL AND img != '' 
                   ORDER BY created_at DESC";
        $stmt2 = parent::connect2()->prepare($query2);
        $stmt2->execute([$authorId]);
        $images = $stmt2->fetchAll(\PDO::FETCH_ASSOC);

        $existingImgNames = array_column($allImages, 'img');
        foreach ($images as $imgRow) {
            $imgName = trim((string)$imgRow['img']);
            if (!empty($imgName) && !in_array($imgName, $existingImgNames, true)) {
                $allImages[] = [
                    'id' => $imgRow['id'],
                    'img' => $imgName,
                    'caption' => (string)($imgRow['caption'] ?? ''),
                    'likes' => (int)($imgRow['likes'] ?? 0),
                    'where_from' => (string)($imgRow['where_from'] ?? 'gallery'),
                    'created_at' => (string)($imgRow['created_at'] ?? ''),
                    'post_no' => null,
                ];
                $existingImgNames[] = $imgName;
            }
        }

        return $allImages;
    }

}
