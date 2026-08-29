<?php
declare(strict_types=1);

namespace App\controller\members;
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

use App\services\ProfilepageService;

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
     * @var mixed
     */
    private $id;

    private const REDIRECT = "Location: /member/ProfilePage";

    private ProfilepageService $profileService;

    public function __construct()
    {
        $this->id = $this->authenticate();
        $this->profileService = new ProfilepageService();
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
        unset($_SESSION['loginType'], $_SESSION['identifyCust']);

        return $id;
    }
    public function index(): void
    {
        try {

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
                'requestData' => $data['friendRequests'],
                'totalFamilyMembers' => $data['totalFamilyMembers']
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

            $postMessage = trim($_POST['postMessage'] ?? '');
            $hasImage = isset($_FILES['post_img']['name'][0]) && !empty($_FILES['post_img']['name'][0]);
            $hasPoll = !empty($_POST['poll_question']);

            if (empty($postMessage) && !$hasImage && !$hasPoll) {
                throw new \Src\Exceptions\ValidationException("Please add some text, an image, or a poll to your post.");
            }

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
                imgPath: "resources/images/post/",
                sourceFileTable: 'post',
                newInput: $newInput,
                isCaptcha: false,
                removeKeys: ['poll_question', 'poll_options', 'token', 'post_no'],
                optionalFields: ['poll_question', 'poll_options', 'postMessage', 'post_no']
            );

    
            if ($result) {
                $_SESSION["LAST_INSERT_ID_POST"] = $result;

                // Handle Poll Data
                $pollQuestion = isset($_POST['poll_question']) ? htmlspecialchars(trim($_POST['poll_question']), ENT_QUOTES, 'UTF-8') : null;
                $pollOptionsRaw = $_POST['poll_options'] ?? [];
                
                $pollOptions = [];
                foreach ($pollOptionsRaw as $opt) {
                    $cleanOpt = htmlspecialchars(trim($opt), ENT_QUOTES, 'UTF-8');
                    if (!empty($cleanOpt)) {
                        $pollOptions[] = $cleanOpt;
                    }
                }
                
                if (!empty($pollQuestion) && count($pollOptions) >= 2) {
                    \App\model\PollData::createPoll((int)$result, $pollQuestion, $pollOptions);
                }

                // Explicitly update the status to published first.
                // Do not rely on background tasks or secondary queries to handle critical state transitions.
                Post::updatePostByStatusAsPublished($result);

                // Trigger background notifications server-side. The post is already
                // saved at this point, so a notification failure (e.g. email/SMTP)
                // must never turn a successful post into a failed response.
                try {
                    \App\controller\members\PostMessage::getNewPostAndEmail($result);
                    \App\controller\members\PostMessage::getNewPostPusher($result);
                } catch (\Throwable $th) {
                    error_log((string) $th);
                }

                // Return the full post row (not just its id) so the composer can prepend it
                // to the feed immediately instead of waiting on a full posts re-fetch.
                $newPost = Post::postByNo($result);

                // Filter out internal database timestamps to prevent data leakage
                $newPost = array_diff_key($newPost, array_flip([
                    'likes_updated_at',
                    // 'date_created', // Keep this so frontend doesn't break
                    'date_updated',
                    'date_deleted'
                ]));

                msgSuccess(200, $newPost ?: $result);
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

                // Trigger background notifications server-side
                \App\controller\members\PostMessage::getNewCommentPusher();

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


        FileUploadProcess::process([], 'profile_upload_img', 'photo', 'resources/images/post/', 'images');
        redirect(self::REDIRECT);
    }


    /**
     * show post pics when they are clicked on
     */
    public function showPics(): void
    {
        try {
            $path = isset($_GET['path']) ? checkInput(data: $_GET['path']) : 'photos';
            $path = is_string($path) ? $path : 'photos';
            $dir = isset($_GET['dir']) ? checkInput(data: $_GET['dir']) : 'public/img';
            $dir = is_string($dir) ? $dir : 'public/img';
            $imgName = $_GET['pics'] ?? '';
            $postId = isset($_GET['pID']) ? checkInput(data: $_GET['pID']) : '';

            $imagePath = "/$dir/$path/$imgName";
            $comment2Img = !empty($postId) ? Post::commentLink2Img(imgPath: $postId) : [];

            view('showImage', [
                'imagePath' => $imagePath,
                'comment' => $comment2Img
            ]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Photo Gallery of images posted by the member / author.
     */
    public function gallery(): void
    {
        try {
            $VerifyJWT = SignIn::verify('users');
            $id = (string)cleanSession((string)$VerifyJWT['id']);

            // Allow viewing a specific member's gallery if ?id=... is provided, default to current user
            $targetId = isset($_GET['id']) ? (string)cleanSession((string)$_GET['id']) : $id;

            // Get pagination params
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 24;
            $offset = ($page - 1) * $limit;

            // Fetch all images posted by the author from post table and images table
            $allImages = Post::getAllImagesByAuthor($targetId);
            $total = count($allImages);
            $totalPages = max(1, (int)ceil($total / $limit));
            $paginatedImages = array_slice($allImages, $offset, $limit);

            $singleCust = new SingleCustomerData();
            $memberInfo = $singleCust->getCustomerData($targetId, ['personal', 'profilePics']);

            \App\controller\BaseController::viewWithCsp('member/images', [
                'data' => $paginatedImages,
                'total' => $total,
                'page' => $page,
                'totalPages' => $totalPages,
                'member' => $memberInfo,
                'isOwner' => ($targetId === $id),
            ]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Update member profile picture from an existing gallery image.
     */
    public function setProfilePicFromImage(): void
    {
        try {
            $VerifyJWT = SignIn::verify('users');
            $id = (string)cleanSession((string)$VerifyJWT['id']);

            $rawInput = (string)file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['imageName'])) {
                throw new Exception("Image name not provided");
            }

            $imageName = checkInput((string)$input['imageName']);
            $imageName = is_string($imageName) ? $imageName : '';

            $updateProfilePic = new \Src\Update('profilePics');
            $result = $updateProfilePic->updateTable(
                column: 'img',
                columnAnswer: $imageName,
                identifier: 'id',
                identifierAnswer: $id
            );

            if ($result) {
                $_SESSION['img'] = $imageName;
                msgSuccess(200, "Profile picture updated successfully");
            } else {
                throw new Exception("Failed to update profile picture in database");
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Toggle public/private visibility of a gallery image.
     */
    public function toggleImageVisibility(): void
    {
        try {
            $VerifyJWT = SignIn::verify('users');
            $id = (string)cleanSession((string)$VerifyJWT['id']);

            $rawInput = (string)file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if (empty($input['imageName'])) {
                throw new \Exception("Image name not provided");
            }

            $imageName = checkInput((string)$input['imageName']);
            $imageName = is_string($imageName) ? $imageName : '';
            $isPublic = !empty($input['is_public']) ? 1 : 0;

            $success = Post::setImageVisibility($id, $imageName, $isPublic);

            if ($success) {
                http_response_code(200);
                echo json_encode([
                    'status' => 200,
                    'success' => true,
                    'message' => $isPublic ? 'Photo is now visible on your public profile' : 'Photo is now private',
                    'is_public' => $isPublic
                ]);
            } else {
                throw new \Exception("Failed to update photo visibility");
            }
        } catch (\Throwable $th) {
            http_response_code(400);
            echo json_encode([
                'status' => 400,
                'success' => false,
                'error' => $th->getMessage()
            ]);
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
        $token = checkInput($_GET['token']);
        $token = is_string($token) ? $token : '';
        setCookie(name: 'tokenJWT', value: $token, expires_or_options: time() + 3600, path: "/", domain: '', secure: true, httponly: true);
        msgSuccess(200, "message set");
    }
}
