<?php

namespace App\controller\members;

use App\model\{
    SingleCustomerData,
    Post
};

use App\classes\AllFunctionalities;

class ProfilePage extends AllFunctionalities
{
    private $getCommentData;
    private $setCommentData;

    function index()
    {

        unset($_SESSION['loginType'], $_SESSION['identifyCust'], $_SESSION['token']);
        $custId = checkInput($_SESSION['memberId']);

        $setData = new SingleCustomerData;
        $data = $setData->getCustomerData($custId);

        $instanceAllData = new Post;
        $allData = $instanceAllData->getAllPost();
        $_SESSION['commentData'] = $instanceAllData->getAllComments();

        $_SESSION['id'] = $custId;
        $_SESSION['fName'] = $data['firstName'];
        $_SESSION['lName'] = $data['lastName'];
        $_SESSION['currentTime'] = time();

        view('member/profile3', compact('data', 'allData'));
    }

    private function processPostData()
    {
        $newPost = new Post;
        $getPost = $newPost->processPost();
        printArr($getPost);
        if (!$getPost) {
            throw new \Exception("There was no data", 1);
        }
        $getPost['id'] = $_SESSION['id'];
        $getPost['fullName'] = $_SESSION['fName'] . " " . $_SESSION['lName'];
        $getPost['post_time'] = $_SESSION['currentTime'];
        return $getPost;
    }

    function post()
    {
        try {
            

            $this->processImages();

            $getPost = $this->processPostData();
            $this->insertData_NoRedirect($getPost, 'post');
            // header('Location: /member/ProfilePage');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    function postComment()
    {
        try {
            $getPost = $this->processPostData();
            $this->insertData_NoRedirect($getPost, 'comment');
            $commentData = $this->select_from('comment', 'post_no', $getPost['post_no']);
            // $_SESSION['commentData'] = $commentData;
            $this->getAllComments = $commentData;
            $_SESSION['counter'] = 1;
            header('Location: /member/ProfilePage');
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    private function processImages()
    {

    }





    function profileImage()
    {
        try {

            printArr($_FILES);
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
