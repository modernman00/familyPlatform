<?php

declare(strict_types=1);

namespace App\controller\members;

use React\EventLoop\Factory;



use App\model\{
    SingleCustomerData,
    Post
};

use App\classes\{
    Sanitise,
    ProcessImg,
    Insert,
    VerifyToken,
    Pusher
};

use App\controller\members\PostMessage;

use App\classes\websocket\ReactServer;
use App\model\AllMembersData as DataAll;



use Exception;

class ProfilePage extends ProcessImg
{
    public $allPostData;
    private $allCommentData;
    private $id;
    private $eventData;
    private const REDIRECT = "Location: /member/ProfilePage";

    function __construct()
    {
        unset($_SESSION['loginType'], $_SESSION['identifyCust'], $_SESSION['token']);

       //TODO something is destroying the session after a few hours: find out

        // GET MEMBER'S DATA
        $_SESSION['memberId'] ??= throw new Exception("Error Processing ID request", 1);
        $this->id = checkInput($_SESSION['memberId']);

        $setData = new SingleCustomerData;

        $table = ['personal', 'interest', 'profile_pics', 'contact', 'otherFamily', 'post'];

        $this->memberData = $setData->getCustomerData($this->id, $table);

        //GET POST DATA 

        $this->allPostData = Post::getAllPostProfilePics();

        //GET ALL EVENTS DATA
        $this->eventData = DataAll::getEventData();

        //GET COMMENT DATA
        $this->allCommentData = Post::getAllCommentProfilePics();

        // POST AND ID
        $this->post2Id = Post::postLink2Id($this->id);

        // COMMENT AND POST NO 
        //printArr($this->memberData);
        //     $postId = $this->allPostData['post_no'];
        //    $this->comment2Post = Post::commentLink2Post($postId);

        $this->getAllPics = Post::getAllPostPics($this->id);
    }

    function index()
    {
        if (!$_SESSION['loggedIn']) {
            msgException(404, "How did you get here?");
            header("location: /login");
        }

        try {
            $_SESSION['id'] = $this->id;
            $_SESSION['fName'] = $this->memberData['firstName'];
            $_SESSION['lName'] = $this->memberData['lastName'];
            $_SESSION['currentTime'] = time();

            //  verify token

            $tokenVerify = new verifyToken();
            $result = $tokenVerify->profilePage();

            // if token is verified

            if ($result) {
                view('member/profilePage', [
                    'data' => $this->memberData,
                    'allData' => $this->allPostData,
                    'comment' => $this->allCommentData,
                    'post2Id' => $this->post2Id,
                    'pics2Id' => $this->getAllPics,
                    'eventData' => $this->eventData
                    // 'comment2Post' => $this->comment2Post
                ]);
            }
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    private function processPostData()
    {
     

        if (!$_POST) {
            throw new \Exception("There was no post data", 1);
        }
        // SANITISE THE POST 
        unset($_POST['post_img']);
        $sanitise = new Sanitise($_POST);
        $getSanitisePost = $sanitise->getData();

        // var_dump($getSanitisePost);

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
        // $getSanitisePost['post_time'] = $_SESSION['currentTime'];
        $getSanitisePost['post_time'] = milliSeconds();

        return $getSanitisePost;
    }


    function post()
    {
        try {
            $getPost = $this->processPostData();
            Insert::submitForm2('post', $getPost);
            // $_SESSION['NEW_POST'] = true; // use it for server sent event update
            // $getPost['LAST_INSERT_ID'] = $_SESSION['LAST_INSERT_ID'];

            // GET ALL THE INFORMATION ABOUT THE NEW POST: PROFILE PICS OF THE 

            msgSuccess(200, "Success");


            // ReactServer::startChat();

            // $loop = Factory::create();
            // $pusher = new Pusher;


            // This is our new stuff
            // $context = new ZMQContext();
            // $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');

            // printArr($socket);
            // $socket->connect("tcp://localhost:80");

            // $socket->send(json_encode($getPost));

           // header(self::REDIRECT);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Use this function to select the profile page
     *
     * @return void
     */
    // private function selectProfileImg()
    // {

    //     $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: "profile_pics", identifier1: "id", orderBy: "ORDER BY date_created DESC", limit: "LIMIT " . 1);

    //     $result = Select::selectFn2($query, [checkInput($_SESSION['id'])]);

    //     foreach ($result as $result);
    //     return $result['img'];
    // }

    function postComment()
    {
        try {
            $getComment = $this->processPostData();
            Insert::submitForm2('comment', $getComment);
            // header(self::REDIRECT);
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

    /**
     * show post pics when they are clicked on
     */

    public function showPics()
    {
        $path = checkInput($_GET['path']);
        $imagePath = ProcessImg::showPostImg($path);

        $postId = checkInput($_GET['pID']);


        $comment2Post = Post::commentLink2Post($postId);

        // printArr($comment2Post);

        view('showImage', [
            'imagePath' => $imagePath,
            //'allData' => $this->allPostData,
            'comment' => $comment2Post,
        ]);
    }
}
