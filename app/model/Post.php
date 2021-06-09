<?php

declare(strict_types=1);

namespace App\model;

use App\classes\Select;
use App\classes\InnerJoin;

class Post extends Select
{

    static function commentLink2Post($postNo)
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'comment', identifier1: "post_no");
        return parent::selectFn2(query: $query, bind: [$postNo]);
    }

    static function postLink2Id(string $id): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'post', identifier1: "id");
        return parent::selectFn2(query: $query, bind: [$id]);
    }

    static function getAllPost(): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ALL", table: 'post');
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
        return InnerJoin::joinAll2(firstTable: 'profile_pics', para: 'id', table: ['post'], orderBy: 'post.date_created');
    }


    public static function getAllCommentProfilePics(): array
    {
        return InnerJoin::joinAll2(firstTable: 'profile_pics', para: 'id', table: ['comment'], orderBy: 'comment.date_created');
    }

    /**
     * Undocumented function
     *
     * @param [type] $custNo this is the id
     *  get the 
     * @return array
     */
    public static function getAllPostPics($custNo): array
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ONE", table: 'post', identifier1: "id");
        return parent::selectFn2(query: $query, bind: [$custNo]);
    }

    static function getAllComments()
    {
        $query = parent::formAndMatchQuery(selection: "SELECT_ALL", table: 'comment');
        return parent::selectFn2(query: $query);
    }
}
