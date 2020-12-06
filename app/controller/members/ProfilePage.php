<?php

namespace App\controller\members;

use App\model\{
    SingleCustomerData,
    Post
};

use App\classes\{
    Sanitise,
    ProcessImg
};

class ProfilePage extends ProcessImg
{
    private $getCommentData;
    private $setCommentData;
    private $data;
    private $allPostData;
    private $allCommentData;
    private $id;

    function __construct()
    {
        unset($_SESSION['loginType'], $_SESSION['identifyCust'], $_SESSION['token']);

        // GET MEMBER'S DATA
        if(!$_SESSION['memberId']) {
            throw new \Exception("Error Processing ID request", 1);
        }
        $this->id = checkInput($_SESSION['memberId']);

        $setData = new SingleCustomerData;
        $this->memberData = $setData->getCustomerData($this->id)
        ;
        //GET POST DATA 
        $instanceAllData = new Post;

        $this->allPostData = $instanceAllData->getAllPost();

        //GET COMMENT DATA

        $this->allCommentData = $instanceAllData->getAllComments();

        // POST AND ID

        $this->post2Id = $instanceAllData->postLink2Id($this->id);

        // COMMENT AND POST NO 
       // echo $this->allPostData['post_no'];
        $this->comment2Post = $instanceAllData->commentLink2Post(21);

    }

    function index()
    {
        try {

            //printArr($this->comment2Post);

            $_SESSION['id'] = $this->id;
            $_SESSION['fName'] = $this->memberData['firstName'];
            $_SESSION['lName'] = $this->memberData['lastName'];
            $_SESSION['currentTime'] = time();
         

            view('member/profile3', [
                'data' =>$this->memberData, 'allData'=>$this->allPostData,
                'comment'=> $this->allCommentData,
                'post2Id' => $this->post2Id,
                'comment2Post' => $this->comment2Post
                ]);

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function processPostData()
    {
        try {
            if (!$_POST) {
                throw new \Exception("There was no data", 1);
            }
            // SANITISE THE POST 

            unset($_POST['post_img']);
            $sanitise = new Sanitise($_POST, null);
            $getSanitisePost = $sanitise->getData();

            // check if there are images in the post
            if ($_FILES) {
                if ($_FILES['post_img']['error'][0] !== 4 || $_FILES['post_img']['size'][0] !== 0) {
                    fileUploadMultiple("img/post/", 'post_img');

                    // create a file path name for the database
                    $image = $_FILES['post_img']['name'];
                    // create the post array for the post image
                    for ($i = 0; $i < count($image); $i++) {
                        $getSanitisePost["post_img$i"] = $image[$i];
                    }
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

            // printArr($getPost);

            $this->insertData_NoRedirect($getPost, 'post');

          header('Location: /member/ProfilePage');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function selectProfileImg()
    {
        $result = $this->select_from_withLimit(
            'profile_pics',
            'id',
            $_SESSION['id'],
            'no',
            "DESC",
            1
        );
        foreach ($result as $result);
        return $result['img'];
    }

    function postComment()
    {
        try {
            $getPost = $this->processPostData();
           // printArr($getPost);
            $this->insertData_NoRedirect($getPost, 'comment');

            header('Location: /member/ProfilePage');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    function profileImage()
    {
        try {

            // process the image 

            $this->processProfileImage();


            header('Location: /member/ProfilePage');
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
