<?php

declare(strict_types=1);

namespace App\controller\members;

use App\model\{
    SingleCustomerData,
    Post
};

use App\classes\{
    Sanitise,
    ProcessImg,
    Insert,
    VerifyToken,
};

// use Pusher\Pusher;
use App\model\AllMembersData as DataAll;



use Exception;

class ProfilePage extends ProcessImg
{
    public $allPostData;
    private $allCommentData;
    private $id;
    private $eventData;
    private $memberData;
    private $post2Id;
    private $getAllPics;
    private const REDIRECT = "Location: /member/ProfilePage";

    public function __construct()
    {
        try {
            unset($_SESSION['loginType'], $_SESSION['identifyCust'], $_SESSION['token']);

            //TODO something is destroying the session after a few hours: find out

            // GET MEMBER'S DATA
            $_SESSION['memberId'] ??= throw new Exception("Error Processing ID request", 1);

            $this->id = checkInput($_SESSION['memberId']);

            $setData = new SingleCustomerData;

            $table = ['personal', 'contact', 'otherFamily', 'post', 'profile_pics'];

            $this->memberData = $setData->getCustomerData($this->id, $table);

            //GET POST DATA 
            $this->allPostData = Post::getAllPostProfilePics();

            //GET ALL EVENTS DATA
            $this->eventData = DataAll::getEventData();

            //GET COMMENT DATA
            $this->allCommentData = Post::getAllCommentProfilePics();

            // POST AND ID
            $this->post2Id = Post::postLink2Id($this->id);

            $this->getAllPics = Post::getAllPostPics($this->id);
        } catch (Exception $e) {
            showError($e);
        }
    }

    public function index()
    {

        try {
            if (!$_SESSION['loggedIn']) {
                // $tokenErr = "Alien! How did you get here?";
                // view('error/genError', ['error' => $tokenErr]);
                // msgException(404, "How did you get here?");
                header("location: /login");
            }

            $_SESSION['id'] = $this->id;
            $_SESSION['fName'] = $this->memberData['firstName'];
            $_SESSION['lName'] = $this->memberData['lastName'];
            $_SESSION['currentTime'] = time();

            //  verify token

            $tokenVerify = new VerifyToken();
            $result = $tokenVerify->profilePage();

            // if token is verified

            if (!$result) {
                header("location: /login");
                // $tokenErr = "Authentication failed";
                // view('error/genError', ['error' => $tokenErr]);
            }

            view('member/profilePage', [
                'data' => $this->memberData,
                'allData' => $this->allPostData,
                'comment' => $this->allCommentData,
                'post2Id' => $this->post2Id,
                'pics2Id' => $this->getAllPics,
                'eventData' => $this->eventData
                // 'comment2Post' => $this->comment2Post
            ]);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    private function processPostData()
    {


        if (!$_POST) {
            throw new Exception("There was no post data", 1);
        }
        // SANITISE THE POST 
        unset($_POST['post_img']);
        $sanitise = new Sanitise($_POST);
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
        $getSanitisePost['post_time'] = milliSeconds();
        return $getSanitisePost;
    }


    public function post()
    {
        try {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            $getPost = $this->processPostData();
            Insert::submitFormDynamic('post', $getPost);

            $theId = (int) $_SESSION['LAST_INSERT_ID_POST'];


            $messages = Post::postByNo($theId);
            foreach ($messages as $message);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    // MESSAGE IS SENT THROUGH THE SUBMIT FOR DYNAMIC
    public function postComment()
    {
        try {
            $getComment = $this->processPostData();
            Insert::submitFormDynamic('comment', $getComment);
        } catch (\Throwable $th) {
            returnErrorCode(401, $th);
            showError($th);
        }
    }

    /**
     * Process profile image; the function was imported
     */

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

    /**
     * post pictures on the profile page
     */

    public function postPics()
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

    /**
     * show post pics when they are clicked on
     */

    public function showPics()
    {
        $path = checkInput($_GET['path']);
        $imagePath = ProcessImg::showPostImg($path);

        $postId = checkInput($_GET['pID']);

        $comment2Img = Post::commentLink2Img($postId);

        // $comment2Post = Post::commentLink2Post($postId);

        view('showImage', [
            'imagePath' => $imagePath,
            //'allData' => $this->allPostData,
            'comment' => $comment2Img
        ]);
    }
}
