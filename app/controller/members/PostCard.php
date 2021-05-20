<?php 


declare(strict_types=1);

namespace App\controller\members;

use App\model\{
    SingleCustomerData,
    Post
};

use App\classes\{
    Select,
    Db, AllFunctionalities
};


class PostCard extends Db {

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

}