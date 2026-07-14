<?php
declare(strict_types=1);

namespace App\Controller\members;
use Src\functionality\middleware\FileUploadProcess;

use App\model\{
    SingleCustomerData,
    Post
};

use Src\{
    Sanitise,
    //ProcessImg,
  
//  VerifyToken,
};

use Src\Exceptions\NotFoundException;

use Src\functionality\{
    SignIn,
    SubmitPostData
};

use App\Services\ProfilepageService;

use App\classes\{ProcessImg, Insert};
// use App\classes\VerifyToken;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// use Pusher\Pusher;
use App\model\AllMembersData as DataAll;



use Exception;

final class ProfilePage extends ProcessImg
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

    private ProfilepageService $profileService;

    public function __construct()
    {
        try {
            $this->id = $this->authenticate();
            $this->profileService = new ProfilepageService();

        } catch (Exception $e) {
            showError($e);
        }
    }

    private function authenticate(): mixed
    {
        $verifyJWT = SignIn::verify();

        if (!\is_array($verifyJWT) || empty($verifyJWT['id'])) {
            throw new NotFoundException('Invalid session');
        }

        $id = checkInput($verifyJWT['id']);

        // centralise session handling
        $_SESSION['id'] = $id;
        unset($_SESSION['loginType'], $_SESSION['identifyCust'], $_SESSION['token']);

        return $id;
    }
    public function index(): void
    {
        try {
            if (!isset($_SESSION['loggedIn'])) {
                redirect("/login");
            }

            $data = $this->profileService->getProfileData($this->id);

            // clean session assignment
            $_SESSION['fName'] = $data['memberData']['firstName'];
            $_SESSION['lName'] = $data['memberData']['lastName'];
            $_SESSION['famCode'] = $data['famCode'];
            $_SESSION['currentTime'] = time();

            view('member/profilePage', [
                'data' => $data['memberData'],
                'allData' => $data['posts'],
                'comment' => $data['comments'],
                'post2Id' => $data['post2Id'],
                'pics2Id' => $data['pics'],
                'eventData' => $data['events'],
                'requestData' => $data['friendRequests']
            ]);

        } catch (\Throwable $th) {
            showError($th);
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
    // private function processPostData(): array
    // {
    //     if (!$_POST) {
    //         throw new Exception("There was no post data", 1);
    //     }
    //     // SANITISE THE POST 
    //     unset($_POST['post_img']);
    //     $sanitise = new Sanitise(formData: $_POST);
    //     $getSanitisePost = $sanitise->getCleanData();

    //     // check if there are images in the post
    //     if ($_FILES) {

    //         if ($_FILES['post_img']['error'][0] !== 4 || $_FILES['post_img']['size'][0] !== 0) {

    //             fileUploadMultiple(fileLocation: "public/img/post/", formInputName: 'post_img');

    //             // create a file path name for the database
    //             $image = $_FILES['post_img']['name'];
    //             // create the post array for the post image
    //             for ($i = 0; $i < count($image); $i++) {
    //                 $name = str_replace(' ', '', $image[$i]);
    //                 $getSanitisePost["post_img$i"] = $name;
    //             }
    //         }
    //     }

    //     // get the other post variables id, fullname, time of post
    //     $getSanitisePost['id'] = checkInput(data: $_SESSION['id']);
    //     $getSanitisePost['fullName'] = $_SESSION['fName'] . " " . $_SESSION['lName'];
    //     $getSanitisePost['post_time'] = milliSeconds();
    //     return $getSanitisePost;
    // }


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
            // // header('Content-Type: text/event-stream');
            // // header('Cache-Control: no-cache');
            // $getPost = $this->processPostData();
            // $getPost['profileImg'] = Post::getProfilePicsById($getPost['id']);
            // Insert::submitFormDynamic(table: 'post', field: $getPost); // this send the last post id to the JS frontend

            // Notification of new post by email and service worker is done with PostMessage::getPostno function as it is called by the javascipt when the post is appended. 
            $id = checkInput(data: $_SESSION['id']);
            $newInput = [
                'id' => $id,
                'fullName' => $_SESSION['fName'] . " " . $_SESSION['lName'],
                'profileImg' => Post::getProfilePicsById($id),
                'post_time' => milliSeconds()
            ];

           $result =  SubmitPostData::submitToOneTablenImage(
                table: 'post',
                fileName: 'post_img',
                imgPath: "public/img/post/",
                sourceFileTable: 'post',
                newInput: $newInput,
                isCaptcha: false,
                generalFileTable: 'images'
            );

            if ($result) {
                $_SESSION["LAST_INSERT_ID_POST"] = $result;

                msgSuccess(200, $result);
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    // MESSAGE IS SENT THROUGH THE SUBMIT FOR DYNAMIC
    public function postComment(): void
    {
        try {
            // $getComment = $this->processPostData();

            // // get the profile pics of the commenter 

            // $getComment['profileImg'] = Post::getProfilePicsById($getComment['id']);

            // Insert::submitFormDynamic('comment', $getComment);

            $id = checkInput(data: $_SESSION['id']);
            $newInput = [
                'id' => $id,
                'fullName' => $_SESSION['fName'] . " " . $_SESSION['lName'],
                'profileImg' => Post::getProfilePicsById($id),
                'post_time' => milliSeconds()
            ];
            $result = SubmitPostData::submitToOneTablenImage(
                table: 'comment',
                newInput: $newInput,
                isCaptcha: false
            );
            if ($result) {
                $_SESSION["LAST_INSERT_ID_COMMENT"] = $result;

                msgSuccess(200, $result);
            }
        } catch (\Throwable $th) {
  
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
        // if ($_FILES) {
        //     if ($_FILES['photo']['error'][0] !== 4 || $_FILES['post_img']['size'][0] !== 0) {
        //         fileUploadMultiple("public/img/photos/", 'photo');

        //         // create a file path name for the database
        //         $image = $_FILES['photo']['name'];
        //         // create the post array for the post image
        //         for ($i = 0; $i < count($image); $i++) {

        //             $arrData = [
        //                 'img' => $image[$i],
        //                 'id' => checkInput($_SESSION['id']),
        //                 'where_from' => 'profile_upload_img'
        //             ];
        //             $insertFile = new Insert();
        //             $insertFile->submitForm(table: 'images', field: $arrData);
        //         }
        //     }

        //     header(self::REDIRECT);
        // }


        FileUploadProcess::process([], 'profile_upload_img', 'photo', 'public/img/photos/', 'images');
        redirect(self::REDIRECT);
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
        $token = checkInput($_GET['token']);
        setCookie(name: 'tokenJWT', value: $token, expires_or_options: time() + 3600, path: "/", domain: '', secure: true, httponly: true);
        msgSuccess(200, "message set");
    }
}
