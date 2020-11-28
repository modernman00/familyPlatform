<?php

namespace App\model;

use App\classes\AllFunctionalities;
use App\classes\Sanitise;

class Post extends AllFunctionalities {

    function getPost () {

        $sanitise = new Sanitise($_POST, null);
        $getSanitisePost = $sanitise->getData();
        return $getSanitisePost;   
    }

    function processPost() {
        $processData = $this->getPost();
        return $processData;
    }

    function getAllPost() {
        return $this->selectall('post');
    }

}