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
    /**
     * @var array|null
     */
    public $allPostData;

    /**
     * @var array|null
     */
    private $allCommentData;
    private $id;
    private $eventData;

    /**
     * @var array|null
     */
    private $memberData;

    /**
     * @var array|null
     */
    private $post2Id;
    private $friendRequestData;

    /**
     * @var array|null
     */
    private $getAllPics;
    private const REDIRECT = "Location: /member/ProfilePage";

    public function __construct()
    {
        try {
            unset($_SESSION['loginType'], $_SESSION['identifyCust'], $_SESSION['token']);

            // GET MEMBER'S DATA
            // GET MEMBER'S DATA
            $this->id = checkInput($_SESSION['memberId'] ?? throw new Exception("Error Processing ID request", 1));

            $setData = new SingleCustomerData;


            $table = ['personal', 'contact', 'otherFamily', 'post', 'profilePics'];

            $this->memberData = $setData->getCustomerData($this->id, $table);
            
            $getFamCode = checkInput($this->memberData['famCode']);
            $_SESSION['famCode'] = $getFamCode;

            // printArr(DataAll::getAllMembers2($this->id));


            $this->friendRequestData = DataAll::getFriendRequestData($this->id, "Request sent");

            //GET POST DATA 
            $this->allPostData = Post::getAllPostProfilePics();

            //GET ALL EVENTS DATA
            $this->eventData = DataAll::getEventDataByFamCode($getFamCode);

            //GET COMMENT DATA
            $this->allCommentData = Post::getAllCommentProfilePics();

            // POST AND ID
            $this->post2Id = Post::postLink2Id($this->id);

            $this->getAllPics = Post::getAllPostPics($this->id);
        } catch (Exception $e) {
            showError($e);
        }
    }

    public function index(): void
    {

        try {
            if (!isset($_SESSION['loggedIn'])) {

                header("location: /login");
            }

            $_SESSION['id'] = $this->id;
            $_SESSION['fName'] = $this->memberData['firstName'];
            $_SESSION['lName'] = $this->memberData['lastName'];
            $_SESSION['currentTime'] = time();

            //  verify token

            $tokenVerify = new VerifyToken();
            $result = $tokenVerify->profilePage();

            // if token is not verified

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
                'eventData' => $this->eventData,
                'requestData' => $this->friendRequestData
                // 'comment2Post' => $this->comment2Post
            ]);
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }

    /**
     * processPostData
     * 
     * @return array
     * 
     * Process post data
     * 
     * This function takes in post data, sanitise it, and 
     * checks if there are images in the post
     * 
     * If there are images, it uploads them to the public/img/post/ folder
     * and creates a file path name for the database
     * 
     * It also creates the post array for the post image
     * 
     * It then gets the other post variables id, fullname, time of post
     * and returns the sanitised post data
     */
    private function processPostData(): array
    {


        if (!$_POST) {
            throw new Exception("There was no post data", 1);
        }
        // SANITISE THE POST 
        unset($_POST['post_img']);
        $sanitise = new Sanitise(formData: $_POST);
        $getSanitisePost = $sanitise->getCleanData();

        // check if there are images in the post
        if ($_FILES) {

            if ($_FILES['post_img']['error'][0] !== 4 || $_FILES['post_img']['size'][0] !== 0) {

                fileUploadMultiple(fileLocation: "public/img/post/", formInputName: 'post_img');

                // create a file path name for the database
                $image = $_FILES['post_img']['name'];
                // create the post array for the post image
                for ($i = 0; $i < count($image); $i++) {
                    $getSanitisePost["post_img$i"] = $image[$i];
                }
            }
        }

        // get the other post variables id, fullname, time of post
        $getSanitisePost['id'] = checkInput(data: $_SESSION['id']);
        $getSanitisePost['fullName'] = $_SESSION['fName'] . " " . $_SESSION['lName'];
        $getSanitisePost['post_time'] = milliSeconds();
        return $getSanitisePost;
    }


    /**
     * Handles the post submission
     * 
     * It sets the content type to text/event-stream and cache control to no-cache
     * and then processes the post data using the processPostData method
     * 
     * It then sends the post data to the database using the Insert::submitFormDynamic method
     * and sends the last post id to the JS frontend
     * 
     * If there is an exception, it calls the showError function
     * 
     * @return void
     */
    public function post(): void
    {
        try {
            // header('Content-Type: text/event-stream');
            // header('Cache-Control: no-cache');
            $getPost = $this->processPostData();
            Insert::submitFormDynamic(table: 'post', field: $getPost); // this send the last post id to the JS frontend

            // Notification of new post by email and service worker is done with PostMessage::getPostno function as it is called by the javascipt when the post is appended. 


         
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    // MESSAGE IS SENT THROUGH THE SUBMIT FOR DYNAMIC
    public function postComment(): void
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
    function profileImage(): void
    {
        try {
            // process the image 
            $this->processProfileImage();
           
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * post pictures on the profile page
     */
    public function postPics(): void
    {
        if ($_FILES) {
            if ($_FILES['photo']['error'][0] !== 4 || $_FILES['post_img']['size'][0] !== 0) {
                fileUploadMultiple("img/photos/", 'photo');

                // create a file path name for the database
                $image = $_FILES['photo']['name'];
                // create the post array for the post image
                for ($i = 0; $i < count($image); $i++) {
                 
                    $arrData = [
                        'photo' => $image[$i],
                        'id' => checkInput($_SESSION['id'])
                    ];
                    $insertFile = new Insert();
                    $insertFile->submitForm(table: 'images', field: $arrData);
                }
            }

            header(self::REDIRECT);
        }
    }

    /**
     * show post pics when they are clicked on
     */
    public function showPics(): void
    {
        $path = checkInput(data: $_GET['path']);
     
        $imagePath = ProcessImg::showPostImg(picsSource: $path);

        $postId = checkInput(data: $_GET['pID']);

        $comment2Img = Post::commentLink2Img(imgPath: $postId);

        // $comment2Post = Post::commentLink2Post($postId);

        view('showImage', [
            'imagePath' => $imagePath,
            //'allData' => $this->allPostData,
            'comment' => $comment2Img
        ]);
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
