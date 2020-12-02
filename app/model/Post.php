<?php

namespace App\model;

use App\classes\AllFunctionalities;
use App\classes\Sanitise;

class Post extends AllFunctionalities
{

    function getPost()
    {
        printArr($_FILES);
        // printArr($_POST);
        $image = $_POST['post_img'];
        unset($_POST['post_img']);
        $sanitise = new Sanitise($_POST, null);
        $getSanitisePost = $sanitise->getData();
        if (!$getSanitisePost) {
            throw new \Exception("Error Processing Request - sanitised data", 1);
        }
        $_POST['post_img'] = $image;
        // $id = checkInput($_SESSION['post_id']);
        //upload picture function;
        fileUploadMultiple("img/post/", 'post_img');

        // create a file path name for the database
        $getSanitisePost['post_img'] = ($_FILES['post_img']['name']);
        return $getSanitisePost;
    }

    function processPost()
    {
        $processData = $this->getPost();
        return $processData;
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
