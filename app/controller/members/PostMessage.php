<?php

declare(strict_types=1);

namespace App\controller\members;

use App\model\{
    SingleCustomerData,
    Post
};


class PostMessage {

    function index()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        $newPost = new Post();
        $message = $newPost->getAllPostProfilePics();
        msgServerSent( $message);
    }

}