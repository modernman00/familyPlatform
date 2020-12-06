<?php

namespace App\model;

use App\classes\AllFunctionalities;
class Post extends AllFunctionalities
{

    function commentLink2Post($postNo)
    {
         return $this->select_from('comment', 'post_no', $postNo);
       
    }

    function postLink2Id($id)
    {
        return $this->select_from('post', 'id', $id);
    }

    function getAllPost()
    {
        return $this->selectall('post');
    }

    function getAllComments()
    {
        return $this->selectall('comment');
    }
}
