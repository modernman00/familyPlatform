<?php

declare(strict_types=1);

namespace App\model;

use App\classes\Select;
use App\classes\InnerJoin;
use App\classes\Update;


class Post extends Select
{

    static function commentLink2Post($postNo): array|int|string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'comment', identifier1: "post_no");
        return parent::selectFn2(query: $query, bind: [$postNo]);
    }

    public static function commentLink2Img($imgPath): array|int|string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'comment', identifier1: "picture");
        return parent::selectFn2(query: $query, bind: [$imgPath]);
    }

    public static function commentByNo(int|string $commentNo): array|int|string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'comment', identifier1: "comment_no");
        $result = parent::selectFn2(query: $query, bind: [$commentNo]);
        return $result[0];
    }


    static function postByNo(int|string $postNo): array|int|string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'post', identifier1: "post_no");
        $result = parent::selectFn2(query: $query, bind: [$postNo]);
        return $result[0];
    }

    static function postLink2Id(string|int $id): array|int|string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'post', identifier1: "id");
        return parent::selectFn2(query: $query, bind: [$id]);
    }

    static function getAllPost(): array|int|string
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
        return InnerJoin::joinAll2(firstTable: 'profilePics', para: 'id', table: ['post'], orderBy: 'post.post_no');
    }

    public function getAllPostProfilePics2(): array
    {
        $inner = new InnerJoin;
        return $inner->joinAll(firstTable: 'profilePics', para: 'id', table: ['post'], orderBy: 'post.date_created');
    }


    public static function getAllCommentProfilePics(): array
    {
        return InnerJoin::joinAll2(firstTable: 'profilePics', para: 'id', table: ['comment'], orderBy: 'comment.date_created');
    }

    public static function getProfilePicsById($id): string
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
    public static function getAllPostPics(string|int $custNo): array|int|string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'post', identifier1: "id");
        return parent::selectFn2(query: $query, bind: [$custNo]);
    }

    public static function getProfilePics($id): array|int|string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'profilePics', identifier1: "id");
        return parent::selectFn2(query: $query, bind: [$id]);
    }

    static function getAllComments(): array|int|string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ALL", table: 'comment');
        return parent::selectFn2(query: $query);
    }

    static function getUnpublishedPost(): array|int|string
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

    

    static function getUnpublishedComment(): array|int|string
    {
        $query = parent::formAndMatchQuery(
            selection: "SELECT_ONE", 
            table: 'comment', 
            identifier1: 'comment_status', 
            orderBy: "ORDER BY comment_no DESC"
        );
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

    static function updateCommentByStatusAsPublished($commentNo): bool
    {
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


}
