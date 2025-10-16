<?php

declare(strict_types=1);

namespace App\Controller\members;

use App\model\{
    SingleCustomerData,
    Post
};
use Src\functionality\{
    SignIn
};
use Src\{SubmitForm, FileUploader, SelectFn, Utility, LoginUtility};
use App\controller\BaseController;
use Src\functionality\{UpdateExistingData, SubmitPostData};

use App\classes\{ProcessImg};

// use Pusher\Pusher;
use App\model\AllMembersData as DataAll;



use Exception;

class ProfilePage extends BaseController
{
    /**
     * @var array|null
     */
    public $allPostData;

    private const REDIRECT = "Location: /member/ProfilePage";


    public function index(): void
    {
        unset(
            $_SESSION['auth'],
            $_SESSION['imageUploadOutcome'],
            $_SESSION['token'],
            $_SESSION['EDIT_PROFILE_ID'],
            $_SESSION['CREATE_EVENT_ID'],
            $_SESSION['changePW'],
            $_SESSION['register'],

        );

        unsetSess(['auth', 'imageUploadOutcome', 'token', 'EDIT_PROFILE_ID', 'CREATE_EVENT_ID', 'changePW', 'register', 'LAST_INSERT_ID_COMMENT', 'LAST_INSERT_ID_CODE_MGT', 'LAST_INSERT_ID_POST', 'LAST_INSERT_ID_AUDIT_LOGS', 'LAST_INSERT_ID_NOTIFICATION', 'POST_COUNT', 'fName', 'lName', 'currentTime', 'LAST_INSERT_ID_EVENTS', 'LAST_INSERT_ID_events', 'email', 'id', 'famCode']);

        try {
            $VerifyJWT = SignIn::verify();

            if (!$VerifyJWT) {
                redirect('/login');
            }

            if (!isset($_SESSION['id'])) {
                $_SESSION['id'] = $VerifyJWT['id'];
            }



            $id = $VerifyJWT['id'] ?? throw new Exception("Error Processing ID request", 1);
            $setData = new SingleCustomerData;
            $table = ['personal', 'contact', 'otherFamily', 'post', 'profilePics'];
            $memberData = $setData->getCustomerData($id, $table);
            $getFamCode = $memberData['famCode'];
            sessSetMany([
                'famCode' => $getFamCode,
                'fullName' => $memberData['firstName'] . ' ' . $memberData['lastName']
            ]);
            $friendRequestData = DataAll::getFriendRequestData($id, "Request sent");
            $postLink2Id = Post::postLink2Id($id);
            $pic2Id = Post::getAllPostPics($id);
            $allData = Post::getAllPostProfilePics();
            $commentProfilePics = Post::getAllCommentProfilePics();
            $eventData = DataAll::getEventDataByFamCode($getFamCode);


            parent::viewWithCsp('member/profilePage', [
                'data' => $memberData,
                'allData' => $allData,
                'comment' => $commentProfilePics,
                'post2Id' => $postLink2Id,
                'pics2Id' => $pic2Id,
                'eventData' => $eventData,
                'requestData' => $friendRequestData
                // 'comment2Post' => $this->comment2Post
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
    private function processPostData(): array
    {
        $id = cleanSession($_SESSION['id']);
        $newInput = [
            'id' => $id,
            'fullName' => sessGet('fullName'),
            'post_time' => milliSeconds(),
            'profileImg' => Post::getProfilePicsById($id)
        ];

        return $newInput;
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
            $newData = $this->processPostData();

            SubmitPostData::submitToOneTablenImage(
                table: 'post',
                imgPath: 'resources/images/post/',
                fileName: 'post_img',
                fileTable: 'post',
                newInput: $newData,
                isCaptcha: false
            );

            // this send the last post id to the JS frontend
            // Notification of new post by email and service worker is done with PostMessage::getPostno function as it is called by the javascipt when the post is appended. 
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    // MESSAGE IS SENT THROUGH THE SUBMIT FOR DYNAMIC
    public function postComment(): void
    {
        try {

            $newData = $this->processPostData();
            SubmitPostData::submitToOneTablenImage(
                table: 'comment',
                newInput: $newData,
                isCaptcha: false
            );
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
            $processImage = new ProcessImg();
            $processImage->processProfileImage();
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
                FileUploader::fileUploadMultiple("public/img/photos/", 'photo');

                // create a file path name for the database
                $image = $_FILES['photo']['name'];
                // create the post array for the post image
                for ($i = 0; $i < count($image); $i++) {

                    $arrData = [
                        'img' => $image[$i],
                        'id' => checkInput($_SESSION['id']),
                        'where_from' => 'profile_upload_img'
                    ];
                    SubmitForm::submitForm(table: 'images', fields: $arrData);
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
        $token = checkInput($_GET['token']);
        setCookie(name: 'tokenJWT', value: $token, expires_or_options: time() + 3600, path: "/", domain: '', secure: true, httponly: true);
        msgSuccess(200, "message set");
    }

    // edit the profile 

    public function editProfile(): void
    {
        try {
            if (!$_POST) {
                throw new Exception("There was no post data", 1);
                exit;
            }

            UpdateExistingData::updateMultipleTables(
                fileTable: 'profilePics',
                imgPath: 'resources/images/profile/',
                fileName: 'img',
                removeKeys: ['submit', 'token', 'grecaptcharesponse', 'awnqylrds9sip'],
                allowedTables: ['personal', 'contact', 'profilePics'],
                identifier: 'id',
                identifierValue: $_SESSION['EDIT_PROFILE_ID']
            );
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function myPics(): void
    {

        $id = checkInput($_SESSION['id']);

            $images = SelectFn::selectDynamicColumnsById(
                table: 'images', 
                identifier: 'id', 
                value: $id, 
                implodeColArray: ['id', 'img', 'caption', 'likes', 'where_from', 'created_at']
            );
    
       parent::viewWithCsp('member/images', ['images' => $images]);

        // view('member.images', compact('images') );
    }
}
