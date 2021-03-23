<?php

namespace App\model;

use App\classes\Select;
class Post extends Select
{

    function commentLink2Post($postNo)
    {
        $query = $this->formAndMatchQuery(selection: "SELECT_ONE", table: 'comment', identifier1: "post_no");
        return $this->selectFn(query: $query, bind: [$postNo]);
       
    }

    function postLink2Id($id)
    {
          $query = $this->formAndMatchQuery(selection: "SELECT_ONE", table: 'post', identifier1: "id");
        return $this->selectFn(query: $query, bind:[ $id]);

    }

    function getAllPost()
    {
         $query = $this->formAndMatchQuery(selection: "SELECT_ALL", table: 'post');
        return $this->selectFn(query: $query);
    }

    function getAllPostPics($custNo)
    {
             $query = $this->formAndMatchQuery(selection: "SELECT_ONE", table: 'post', identifier1: "id");
        return $this->selectFn(query: $query, bind: [$custNo]);
		
    }

    function getAllComments()
    {
              $query = $this->formAndMatchQuery(selection: "SELECT_ALL", table: 'comment');
        return $this->selectFn(query: $query);
    }
}
