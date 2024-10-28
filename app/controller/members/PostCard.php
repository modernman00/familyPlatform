<?php 


declare(strict_types=1);

namespace App\controller\members;


use App\classes\{Select, Db, AllFunctionalities};


class PostCard extends Db {

    /**
     * Increments the post likes count and returns the new count.
     * 
     * The function is called by the AJAX request in the like button.
     * 
     * @return void
     */
    public static function likeFunction()
    {
        try {
               $id = checkInput($_GET['postId']);
        $count = checkInput($_GET['count']);
        $count+=1;
        AllFunctionalities::update2('post', 'post_likes', $count, 'post_no', $id);

        $query = Select::formAndMatchQuery(selection: "SELECT_COL_ID", table:'post', column:"post_likes", identifier1: "post_no");
        $result = Select::selectFn2($query, [$id]);
        foreach($result as $result){
            if($id){
            $result2= (int) $result['post_likes'] + 1;
        
            }
        }
        echo json_encode($result2);
        } catch (\Throwable $th) {
           returnErrorCode(505, $th);
        }
     
    }


    /**
     * Sets an HTTP cookie for JWT token.
     * 
     * Retrieves the token from the GET request and sets a secure, HTTP-only cookie with a one-hour expiry.
     * Responds with a success message upon completion.
     * 
     * @return void
     */
    public function setHeader()
    {
        $token = $_GET['token'];
         setCookie(name:'tokenJWT', value:$token, expires_or_options:time() + 3600, path: "/", domain:'', secure: true, httponly: true );
         msgSuccess(200, "message set");
    }

}