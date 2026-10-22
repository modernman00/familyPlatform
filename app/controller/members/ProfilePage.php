<?php
declare(strict_types=1);

namespace App\controller\members;
use Src\functionality\middleware\FileUploadProcess;

use App\model\{
    SingleCustomerData,
    Post
};


use Src\Exceptions\NotFoundException;

use Src\functionality\{
    SignIn,
    SubmitPostData
};

use App\services\ProfilepageService;

use App\classes\ProcessImg;


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
        if (!empty($_SESSION['id'])) {
            return $_SESSION['id'];
        }

        $verifyJWT = SignIn::verify();

        if (!\is_array($verifyJWT) || empty($verifyJWT['id'])) {
            throw new NotFoundException('Invalid session');
        }

        $id = checkInput($verifyJWT['id']);

        // centralise session handling
        $_SESSION['id'] = $id;

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

            $recentReels = \App\model\Reel::getReelsFeed($this->id, (string)($data['famCode'] ?? ''), 10, 0);
            $suggestedKin = \App\services\KinshipEngineService::getSuggestedKin($this->id, 5);

            view('member/profilePage', [
                'data' => $data['memberData'],
                'allData' => $data['posts'],
                'comment' => $data['comments'],
                'post2Id' => $data['post2Id'],
                'pics2Id' => $data['pics'],
                'eventData' => $data['events'],
                'requestData' => $data['friendRequests'],
                'totalFamilyMembers' => $data['totalFamilyMembers'],
                'unclaimedMatch' => $data['unclaimedMatch'] ?? null,
                'pendingFamilyRequests' => $data['pendingFamilyRequests'] ?? [],
                'pendingMigrations' => $data['pendingMigrations'] ?? [],
                'recentReels' => $recentReels,
                'suggestedKin' => $suggestedKin
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

            $rawPostMessage = trim((string)($_POST['postMessage'] ?? ''));
            $cleanMultiLineMessage = \App\services\LinkPreviewService::sanitizePostMessage($rawPostMessage);
            $hasDirectImage = isset($_FILES['post_img']['name'][0]) && !empty($_FILES['post_img']['name'][0]);
            
            // Collect and validate any staged media from PWA Web Share Target
            $sharedTempImages = [];
            if (!empty($_POST['shared_temp_images']) && is_array($_POST['shared_temp_images'])) {
                foreach ($_POST['shared_temp_images'] as $tempName) {
                    $cleanName = basename((string)$tempName);
                    if (preg_match('/^[a-zA-Z0-9_\-\.]+$/', $cleanName)) {
                        $sharedTempImages[] = $cleanName;
                    }
                }
            }
            // Attached family GIF support
            $attachedGif = !empty($_POST['attached_gif_url']) ? trim((string)$_POST['attached_gif_url']) : null;
            if ($attachedGif && filter_var($attachedGif, FILTER_VALIDATE_URL)) {
                $hasGif = true;
            } else {
                $hasGif = false;
                $attachedGif = null;
            }

            $hasImage = $hasDirectImage || !empty($sharedTempImages) || $hasGif;
            $hasPoll = !empty($_POST['poll_question']);

            if (empty($cleanMultiLineMessage) && !$hasImage && !$hasPoll) {
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

                // Promote attached GIF to primary post image if no local image uploaded
                if ($attachedGif && !$hasDirectImage && empty($sharedTempImages)) {
                    (new \Src\Update('post'))->updateTable(
                        column: 'post_img0',
                        columnAnswer: $attachedGif,
                        identifier: 'post_no',
                        identifierAnswer: $result
                    );
                }

                // Preserve multi-line captions and full URLs without being squashed by checkInput
                if ($cleanMultiLineMessage !== '') {
                    (new \Src\Update('post'))->updateTable(
                        column: 'postMessage',
                        columnAnswer: $cleanMultiLineMessage,
                        identifier: 'post_no',
                        identifierAnswer: $result
                    );
                }

                // Promote staged media from PWA Web Share Target into persistent storage
                if (!empty($sharedTempImages)) {
                    $tempBaseDir = realpath(__DIR__ . '/../../../resources/images/post') . '/temp';
                    $postBaseDir = realpath(__DIR__ . '/../../../resources/images/post');
                    $imgIndex = 0;
                    foreach ($sharedTempImages as $tempFile) {
                        if ($imgIndex >= 6) {
                            break;
                        }
                        $srcPath = rtrim($tempBaseDir, '/') . '/' . $tempFile;
                        $destPath = rtrim($postBaseDir, '/') . '/' . $tempFile;
                        if (file_exists($srcPath)) {
                            if (rename($srcPath, $destPath) || copy($srcPath, $destPath)) {
                                @unlink($srcPath); // nosemgrep: php.lang.security.unlink-use.unlink-use
                                (new \Src\Update('post'))->updateTable(
                                    column: 'post_img' . $imgIndex,
                                    columnAnswer: $tempFile,
                                    identifier: 'post_no',
                                    identifierAnswer: $result
                                );
                                $imgIndex++;
                            }
                        }
                    }
                    unset($_SESSION['pwa_shared_target']);
                }

                // Warm up link preview cache if a URL is present
                \App\services\LinkPreviewService::getPreviewForPostText($cleanMultiLineMessage);

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

                // Synchronise family hashtags & memory pills
                try {
                    $famCode = (string)($_SESSION['famCode'] ?? '');
                    $authorIdStr = is_string($id) ? $id : (string)($_SESSION['id'] ?? '');
                    \App\services\HashtagService::syncHashtags(
                        $famCode,
                        'post',
                        (string)$result,
                        $authorIdStr,
                        (string)$cleanMultiLineMessage
                    );
                } catch (\Throwable $th) {
                    error_log('[ProfilePage::post hashtags] ' . $th->getMessage());
                }

                // Synchronise tagged family members
                $postTags = [];
                try {
                    $famCode = (string)($_SESSION['famCode'] ?? '');
                    $authorIdStr = is_string($id) ? $id : (string)($_SESSION['id'] ?? '');
                    $rawTagged = $_POST['tagged_user_ids'] ?? [];
                    if (is_string($rawTagged)) {
                        $rawTagged = json_decode($rawTagged, true) ?: [$rawTagged];
                    }
                    if (is_array($rawTagged) && !empty($rawTagged)) {
                        $cleanTaggedIds = array_values(array_unique(array_filter(array_map('strval', $rawTagged))));
                        if (!empty($cleanTaggedIds)) {
                            $postTags = \App\controller\members\TagController::syncPostTags(
                                (int)$result,
                                $authorIdStr,
                                $cleanTaggedIds,
                                $famCode
                            );
                        }
                    }
                } catch (\Throwable $tagEx) {
                    error_log('[ProfilePage::post tags] ' . $tagEx->getMessage());
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

                // Attach hydrated link preview for immediate feed presentation
                if (is_array($newPost)) {
                    $newPost['link_preview'] = \App\services\LinkPreviewService::getPreviewForPostText($cleanMultiLineMessage);
                    $newPost['post_tags'] = !empty($postTags) ? $postTags : \App\controller\members\TagController::getPostTags((int)$result);
                }

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
            $idClean = checkInput(data: $_SESSION['id'] ?? '');
            $id = is_string($idClean) ? $idClean : '';
            if ($id === '') {
                msgException(401, 'Unauthorized');
                return;
            }

            // SEC-5 — cap comment spam per user (30 / 5 min via the shared limiter's
            // 'post' profile).
            \Src\Limiter::limit($id . ':comment', 'post');
            $newInput = [
                'id' => $id,
                'fullName' => $_SESSION['fName'] . " " . $_SESSION['lName'],
                'profileImg' => Post::getProfilePicsById($id) ?: 'avatarM.png',
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

                $commentData = [
                    'comment_no' => (int) $result,
                    'post_no' => (string) ($_POST['post_no'] ?? ''),
                    'id' => $id,
                    'fullName' => (string) $newInput['fullName'],
                    'profileImg' => (string) $newInput['profileImg'],
                    'comment' => \checkInput((string) ($_POST['comment'] ?? '')),
                    'post_time' => (string) $newInput['post_time'],
                    'comment_time' => (string) $newInput['post_time'],
                    'date_created' => date('Y-m-d H:i:s'),
                ];

                msgSuccess(200, $commentData);
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
            $id = !empty($_SESSION['id']) ? (string) $_SESSION['id'] : (string) cleanSession((string) SignIn::verify('users')['id']);

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
            $id = !empty($_SESSION['id']) ? (string) $_SESSION['id'] : (string) cleanSession((string) SignIn::verify('users')['id']);

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
            $id = !empty($_SESSION['id']) ? (string) $_SESSION['id'] : (string) cleanSession((string) SignIn::verify('users')['id']);

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
