<?php

declare(strict_types=1);

namespace App\model;

use App\classes\Select;
use App\classes\InnerJoin;

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
        return parent::selectFn2(query: $query, bind: [$commentNo]);
    }


    static function postByNo(int|string $postNo): array|int|string
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'post', identifier1: "post_no");
        return parent::selectFn2(query: $query, bind: [$postNo]);
    }

    static function postLink2Id(string $id): array|int|string
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

    /**
     * Undocumented function
     *
     * @param [type] $custNo this is the id
     *  get the
     */
    public static function getAllPostPics(string $custNo): array|int|string
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

}
