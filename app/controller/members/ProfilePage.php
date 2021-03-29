<?php
declare(strict_types =1);

namespace App\controller\members;

use App\model\{
    SingleCustomerData,
    Post
};

use App\classes\{
    Sanitise,
    ProcessImg, Insert, Select
};

use Exception;

class ProfilePage extends ProcessImg
{
    private $allPostData;
    private $allCommentData;
    private $id;
    private const REDIRECT = "Location: /member/ProfilePage";

    function __construct()
    {
        unset($_SESSION['loginType'], $_SESSION['identifyCust'], $_SESSION['token']);

        // GET MEMBER'S DATA
        $_SESSION['memberId'] ??= throw new Exception("Error Processing ID request", 1);
        $this->id = checkInput($_SESSION['memberId']);
        $setData = new SingleCustomerData;
        $this->memberData = $setData->getCustomerData($this->id);

        //GET POST DATA 
        $instanceAllData = new Post;
        $this->allPostData = $instanceAllData->getAllPost();

        //GET COMMENT DATA
        $this->allCommentData = $instanceAllData->getAllComments();
       
        // POST AND ID
        $this->post2Id = $instanceAllData->postLink2Id($this->id);

        // COMMENT AND POST NO 
        //printArr($this->memberData);
        //$postId = $this->allPostData['post_no'];
        //  $this->comment2Post = $instanceAllData->commentLink2Post($postId);

        $this->getAllPics = $instanceAllData->getAllPostPics($this->id);
    }

    function index()
    {
        try {;
            $_SESSION['id'] = $this->id;
            $_SESSION['fName'] = $this->memberData['firstName'];
            $_SESSION['lName'] = $this->memberData['lastName'];
            $_SESSION['currentTime'] = time();

            view('member/profilePage', [
                'data' => $this->memberData, 
                'allData' => $this->allPostData,
                'comment' => $this->allCommentData,
                'post2Id' => $this->post2Id,
                'pics2Id' => $this->getAllPics
                // 'comment2Post' => $this->comment2Post
            ]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function processPostData()
    {
        try {
            if (!$_POST) {
                throw new \Exception("There was no post data", 1);
            }
            // SANITISE THE POST 
            unset($_POST['post_img']);
            $sanitise = new Sanitise($_POST, null);
            $getSanitisePost = $sanitise->getData();

            // check if there are images in the post
                if ($_FILES && $_FILES['post_img']['error'][0] !== 4 || $_FILES['post_img']['size'][0] !== 0) {

                    fileUploadMultiple("img/post/", 'post_img');

                    // create a file path name for the database
                    $image = $_FILES['post_img']['name'];
                    // create the post array for the post image
                    for ($i = 0; $i < count($image); $i++) {
                        $getSanitisePost["post_img$i"] = $image[$i];
                    }
                }


            // get the other post variables id, fullname, time of post
            $getSanitisePost['id'] = $_SESSION['id'];
            $getSanitisePost['fullName'] = $_SESSION['fName'] . " " . $_SESSION['lName'];
            $getSanitisePost['post_time'] = $_SESSION['currentTime'];

            // link the post profile image to the post 
            $getSanitisePost['profileImg'] = $this->selectProfileImg();

            return $getSanitisePost;
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    function post()
    {
        try {
            $getPost = $this->processPostData();
            $insertFile = new Insert();
            $insertFile->submitForm('post', $getPost);
            header(self::REDIRECT);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function selectProfileImg()
    {

        $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: "profile_pics", identifier1:"id", orderBy:"ORDER BY no DESC", limit: "LIMIT ". 1);

        $result = Select::selectFn2($query, [$_SESSION['id']]);

        foreach ($result as $result);
        return $result['img'];
    }

    function postComment()
    {
        try {
            $getPost = $this->processPostData();
             $insertFile = new Insert();
            $insertFile->submitForm('comment', $getPost);
            header(self::REDIRECT);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    function profileImage()
    {
        try {
            // process the image 
            $this->processProfileImage();
            header(self::REDIRECT);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    function postPics()
    {
        if ($_FILES) {
            if ($_FILES['photo']['error'][0] !== 4 || $_FILES['post_img']['size'][0] !== 0) {
                fileUploadMultiple("img/photos/", 'photo');

                // create a file path name for the database
                $image = $_FILES['photo']['name'];
                $images = [];
                // create the post array for the post image
                for ($i = 0; $i < count($image); $i++) {
                    $images["photo"] = $image[$i];
                    $arrData = [
                        'photo' => $image[$i],
                        'id' => checkInput($_SESSION['id'])
                    ];
                     $insertFile = new Insert();
                    $insertFile->submitForm('images', $arrData);
                }
            }

            header(self::REDIRECT);
        }
    }
}
