<?php

namespace App\model;

use App\classes\AllFunctionalities;
use App\classes\Sanitise;

class Post extends AllFunctionalities {

    function getPost () {
        printArr($_POST);
        $sanitise = new Sanitise($_POST, null);
        $getSanitisePost = $sanitise->getData();
        if(!$getSanitisePost) {
            throw new \Exception("Error Processing Request - sanitised data", 1);   
        }
        return $getSanitisePost;   
    }

    function processPost() {
        $processData = $this->getPost();
        return $processData;
    }

    function getAllPost() {
        return $this->selectall('post');
    }

    function getAllComments() {
        return $this->selectall('comment');
    }

}