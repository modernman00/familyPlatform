<?php

declare(strict_types=1);

namespace App\controller\members;

use App\model\{
    SingleCustomerData,
    Post
};


class PostMessage
{

    function index()
    {
        try {

            $newPost = new Post();
            $message = $newPost->getAllPostProfilePics();
            $count = count($message);
         
            $_SESSION['COUNT'] = $count;
            msgSuccess(200, $message);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

     function update()
    {
        try {
            // session_destroy();
            // make session read-only
            \session_write_close();
            // disable default disconnect checks
            \ignore_user_abort(true);

            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Access-Control-Allow-Origin: *');

            $newPost = new Post();
            $message = $newPost->getAllPostProfilePics();
            $count = count($message);
            // $count = count($message);
            // if($_SESSION['COUNT']!= $count) {
          
            
                msgServerSent($message);
      
                
            // }
            
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }
}
