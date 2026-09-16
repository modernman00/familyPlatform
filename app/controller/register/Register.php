<?php
declare(strict_types=1);

namespace App\controller\register;

use App\model\RegisterTableData;
use Exception;
use Src\{ SubmitForm, Select, Db, CheckToken, Recaptcha, LoginUtility, CorsHandler, Limiter };
use Src\functionality\middleware\GetRequestData;
use Src\functionality\SendEmailFunctionality;

// testing

final class Register extends Db
{
    /**
     * GDPR-2 — UK GDPR (DPA 2018 s.9) sets 13 as the age at which a child can
     * consent to an "information society service" on their own. Below this they
     * must be added to the tree by a parent/guardian, not self-register. The
     * DPIA may raise this; change it here if so.
     */
    private const MIN_SELF_REGISTER_AGE = 13;

    public function index(): void
    {
        try {

            $_SESSION['register'] = true;

            // to retain values in the input 
            if (isset($_POST['submit'])) {
                $registerPostData = $_POST;
                view('registration/register', ['registerPostData' => $registerPostData]);
            } elseif (isset($_SESSION['oauth_pending'])) {
                $oauth = $_SESSION['oauth_pending'];
                $randomPass = bin2hex(random_bytes(8)) . 'A1!'; // Meets complexity
                $registerPostData = [
                    'firstName' => $oauth['firstName'],
                    'lastName' => $oauth['lastName'],
                    'email' => $oauth['email'],
                    'password' => $randomPass,
                    'confirm_password' => $randomPass
                ];
                view('registration/register', ['registerPostData' => $registerPostData]);
            } else {
                $registerPostData = [];
                $inviteTokenError = null;

                // Handle new opaque invite token (InviteTokenService)
                if (!empty($_GET['invite'])) {
                    // Rate-limit invite token enumeration (use REMOTE_ADDR to prevent header spoofing)
                    $clientIp = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
                    try {
                        Limiter::limit('invite_token_' . $clientIp);
                    } catch (\Src\Exceptions\TooManyRequestsException $e) {
                        $inviteTokenError = 'Too many invite link attempts. Please try again later.';
                        view('registration/register', [
                            'registerPostData' => [],
                            'inviteTokenError' => $inviteTokenError
                        ]);
                        return;
                    }

                    try {
                        $tokenData = \App\services\InviteTokenService::peek((string)$_GET['invite']);
                        if ($tokenData) {
                            $registerPostData['famCode'] = $tokenData['family_code'];
                            $registerPostData['firstName'] = $tokenData['first_name'] ?? '';
                            $registerPostData['lastName'] = $tokenData['last_name'] ?? '';
                            $registerPostData['email'] = $tokenData['email'] ?? '';
                            $registerPostData['claim_node'] = $tokenData['node_id'] ?? null;
                        } else {
                            $inviteTokenError = 'This invite link has expired or is invalid. Please ask your family member to send a new one.';
                        }
                    } catch (\Throwable $e) {
                        error_log('[Register] Invite token error: ' . $e->getMessage());
                        $inviteTokenError = 'This invite link has expired or is invalid. Please ask your family member to send a new one.';
                    }
                }

                // Fallback: Handle legacy signed invite_token (FamilyClaimService)
                if (empty($registerPostData) && !empty($_GET['invite_token'])) {
                    $tokenData = \App\services\FamilyClaimService::verifySignedInviteToken((string)$_GET['invite_token']);
                    if ($tokenData) {
                        $registerPostData['famCode'] = $tokenData['family_code'];
                        $registerPostData['firstName'] = $tokenData['first_name'];
                        $registerPostData['lastName'] = $tokenData['last_name'];
                        $registerPostData['email'] = $tokenData['email'];
                        $registerPostData['claim_node'] = $tokenData['node_id'];
                    }
                }

                // Deprecated ?famCode= parameter removed (2026-09-16): Security risk.
                // Use opaque invite tokens (?invite=) or re-invite only.

                if (!empty($registerPostData['famCode'])) {
                    $famCodeRaw = $registerPostData['famCode'];
                    // GET params can arrive as a plain string or as an array (e.g. ?famCode[]=X).
                    // PHPStan tells us the union type here is non-empty-array|non-falsy-string.
                    if (is_string($famCodeRaw)) {
                        $famCodeStr = $famCodeRaw;
                    } else {
                        // Must be a non-empty-array at this point.
                        $first = reset($famCodeRaw);
                        $famCodeStr = is_string($first) ? $first : '';
                    }
                    if ($famCodeStr !== '') {
                        $familySurname = self::getFamilySurnameByCode($famCodeStr);
                        if ($familySurname) {
                            $registerPostData['familySurname'] = $familySurname;
                        }
                    }
                }
                if (empty($registerPostData['familySurname']) && !empty($registerPostData['lastName'])) {
                    $registerPostData['familySurname'] = $registerPostData['lastName'];
                }

                $env = getenv('APP_ENV');
                if (($env === 'development' || $env === 'local') && empty($registerPostData)) {
                    $registerPostData = [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'famCode' => 'DOE123',
                        'email' => 'john.doe@example.com',
                        'mobile' => '447809650814',
                        'password' => 'Password123!',
                        'confirm_password' => 'Password123!',
                        'day' => '12',
                        'month' => 'Mar',
                        'year' => '1990',
                    ];
                }
                view('registration/register', ['registerPostData' => $registerPostData, 'inviteTokenError' => $inviteTokenError]);
            }
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    // public function nextStep(): void
    // {
    //     try {
    //         $_SESSION['register'] = false;
    //         view('registration/nextStep');
    //     } catch (\Throwable $e) {

    //         showError($e);
    //     }
    // }

    /**
     * 
     * @param mixed $array this is the POST Data 
     * @param mixed $table database table
     * @param mixed|null $data check len (max and min)
     * @return void 
     */
    public function processForm()
    {
        CorsHandler::setHeaders();
        try {
            $input = GetRequestData::getRequestData();
            Recaptcha::verifyCaptchaEnterprise($input, 'SUBMIT');
            unset($input['action'], $input['siteKey']);

            // Auto-generate family code if creating a new family (familySurname provided, no famCode)
            if (empty($input['famCode']) && !empty($input['familySurname'])) {
                $rawSurname = (string)$input['familySurname'];
                $cleaned = preg_replace('/[^A-Za-z]/', '', $rawSurname);
                $prefix = mb_strtoupper((string)$cleaned);
                if (mb_strlen($prefix) < 3) {
                    $prefix = str_pad($prefix, 3, 'X', STR_PAD_RIGHT);
                } else {
                    $prefix = mb_substr($prefix, 0, 3);
                }
                $randomBytes = bin2hex(random_bytes(4));
                $randomDigits = (int)hexdec(mb_substr($randomBytes, 0, 4)) % 10000;
                $input['famCode'] = $prefix . str_pad((string)$randomDigits, 4, '0', STR_PAD_LEFT);
            }

            // set application id
            $generateId = $this->setId($input, "firstName", 'account');
            $data = $this->dataToCheck();

            // Sanitise the data and get the cleaned data
            $cleanData = LoginUtility::getSanitisedInputData($generateId, $data);

            // hash the password and confirm_password fields
            $cleanData = hashPasswordsInArray($cleanData);

            // GDPR-2 — age gate. A child under the Art. 8 age of consent cannot
            // self-register; a parent/guardian adds them to the tree instead.
            $this->assertOldEnoughToRegister(
                (string) ($cleanData['day'] ?? ''),
                (string) ($cleanData['month'] ?? ''),
                (string) ($cleanData['year'] ?? '')
            );

            // Determine initial family status.
            // Server-side gate: If joining existing family (not creating), MUST be pending.
            // Do NOT trust client-side joining_via_invitation flag—verify by checking
            // if the family code was PRE-POPULATED (from invite token or existing family).
            $existingFamCode = !empty($cleanData['famCode']);
            $wasInviteParameter = !empty($_GET['invite']) || !empty($_GET['invite_token']);
            $joiningViaInvitation = $existingFamCode && ($wasInviteParameter || !empty($input['temporary_code']));
            $cleanData['familyStatus'] = $joiningViaInvitation ? 'pending' : 'approved';

            // create sessions and some variables
           sessSet('id',$cleanData['id']);
           sessSet('firstName',$cleanData['firstName']);

            $firstName = $cleanData['firstName'];

            // check if the email already exist
            if (checkEmailExist($cleanData['email'])) {
                throw new Exception("Your email-{$cleanData['email']} is already registered");
            }

            Limiter::limit($cleanData['email']);  
            
            // Prevent brute-force abuse by clearing rate limits
            Limiter::$argLimiter->reset();
            Limiter::$ipLimiter->reset();
            // time to submit the input data to database

            $getTableData = RegisterTableData::createRegisterTable($cleanData);

            if (isset($_SESSION['oauth_pending'])) {
                $provider = $_SESSION['oauth_pending']['provider'];
                $providerId = $_SESSION['oauth_pending']['providerId'];
                if ($provider === 'google') {
                    $getTableData['account']['google_id'] = $providerId;
                } else if ($provider === 'facebook') {
                    $getTableData['account']['facebook_id'] = $providerId;
                }
                // Optional: make password null, but we prefilled a secure random one so it's fine.
            }

            $dbConnection = self::connect2();
            $dbConnection->beginTransaction();

            try {
                foreach ($getTableData as $tableName => $tableData) {
                    if (!SubmitForm::submitForm($tableName, $tableData)) {
                        throw new Exception("$tableName didn't submit");
                    }
                }

                $dbConnection->commit();

                // Immediate Auto-Claim & Tree Node initialization
                try {
                    $claimNodeId = !empty($input['claim_node']) ? (int)$input['claim_node'] : null;
                    \App\services\FamilyClaimService::claimOrInitializeNode(
                        (string)$cleanData['famCode'],
                        (string)$cleanData['id'],
                        $cleanData,
                        $claimNodeId
                    );
                } catch (\Throwable $e) {
                    error_log("FamilyClaimService registration error: " . $e->getMessage());
                }

                // If joining via invitation, create the approval request & notify the inviter
                if ($joiningViaInvitation && !empty($cleanData['famCode'])) {
                    try {
                        $approvalService = new \App\service\FamilyCodeApprovalService($dbConnection);
                        $notificationService = new \App\service\NotificationService($dbConnection);

                        $inviterFirstName = trim((string)($input['inviter_first_name'] ?? ''));
                        $inviterLastName  = trim((string)($input['inviter_last_name'] ?? ''));
                        $inviterContact   = trim((string)($input['inviter_email_or_mobile'] ?? ''));

                        $approvalData = $approvalService->createApprovalRequest(
                            (string)$cleanData['id'],
                            (string)$cleanData['famCode'],
                            $inviterFirstName,
                            $inviterLastName,
                            $inviterContact
                        );

                        $inviter = $approvalService->findMatchingInviter(
                            (string)$cleanData['famCode'],
                            $inviterFirstName,
                            $inviterLastName,
                            $inviterContact
                        );

                        if ($inviter) {
                            $newUserInfo = [
                                'id' => (string)$cleanData['id'],
                                'firstName' => (string)($cleanData['firstName'] ?? ''),
                                'lastName'  => (string)($cleanData['lastName'] ?? ''),
                                'email'     => (string)($cleanData['email'] ?? '')
                            ];

                            $notificationService->sendFamilyApprovalNotification(
                                (string)$inviter['id'],
                                $newUserInfo,
                                (int)$approvalData['request_id'],
                                (string)$cleanData['famCode'],
                                (string)$approvalData['approval_token']
                            );
                        }
                    } catch (\Throwable $approvalEx) {
                        error_log('[Register.php] Approval request creation warning: ' . $approvalEx->getMessage());
                    }
                }

                // Check if user came via a friend recommendation referral
                $pendingRef = $_SESSION['pending_referral'] ?? null;
                if (is_array($pendingRef) && !empty($pendingRef['inviter_id']) && !empty($pendingRef['token'])) {
                    try {
                        \App\services\FamilyRecommendationService::recordSuccessfulRecommendation(
                            (string) $pendingRef['inviter_id'],
                            (string) $cleanData['id'],
                            (string) $cleanData['famCode'],
                            (string) $pendingRef['token']
                        );
                        unset($_SESSION['pending_referral']);
                    } catch (\Throwable $refEx) {
                        error_log('[Register.php] Recommendation record warning: ' . $refEx->getMessage());
                    }
                }

                SendEmailFunctionality::email("msg/appSub","We have received your application", $cleanData, 'member');

                if (isset($_SESSION['oauth_pending'])) {
                    unset($_SESSION['oauth_pending']);

                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_regenerate_id(true);
                    }

                    sessSet('manager_id', $cleanData['id']);
                    sessSet('famCode', $cleanData['famCode']);

                    \Src\JwtHandler::issueLoginCookie(['id' => $cleanData['id'], 'role' => 'users']);

                    msgSuccess(200, "Registration complete. Redirecting to your profile...", "/profilePage");
                } else {
                    if ($joiningViaInvitation) {
                        $successMsg = "Hello $firstName - Your registration is complete! An approval request has been sent to your family member. Once they approve, you'll have access to the family network.";
                        msgSuccess(200, $successMsg, "/login");
                    } else {
                        // Creating a new family — show the code in a modal before redirecting
                        header('Content-Type: application/json');
                        http_response_code(200);
                        echo json_encode([
                            'status' => 'success',
                            'message' => "Hello $firstName - Your family has been created!",
                            'family_code' => (string)$cleanData['famCode'],
                            'show_code_modal' => true,
                            'redirect' => '/login'
                        ]);
                        return;
                    }
                }


            } catch (\Throwable $th) {
                if ($dbConnection->inTransaction()) {
                    $dbConnection->rollBack();
                }
                msgException(500, $th->getMessage());
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    /**
     * @return (int|string)[][]
     *
     * @psalm-return array{min: array{0: 2, 1: 2, 2: 2, 3: 7, 4: 7, 5: 7}, max: array{0: 35, 1: 35, 2: 30, 3: 16, 4: 50, 5: 50}, data: array{0: 'firstName', 1: 'lastName', 2: 'country', 3: 'mobile', 4: 'email', 5: 'password'}}
     */
    private function dataToCheck(): array
    {
        return [
            'min' => [2, 2, 2, 7, 7, 7, 4],
            'max' => [35, 35, 30, 16, 50, 50, 20],
            'data' => [
                'firstName', 'lastName', 'country', 'mobile', 'email', 'password', 'famCode'
            ]
        ];
    }



    /**
     * Reject self-registration by anyone under the Art. 8 age of consent.
     *
     * Accepts the three parts posted by the shared-lib `birthday` field
     * (`day` = 1–31, `month` = "Jan".."Dec" or 1–12, `year` = 4-digit).
     *
     * @throws \Src\Exceptions\BadRequestException when the date is missing,
     *         malformed, in the future, or the person is younger than
     *         self::MIN_SELF_REGISTER_AGE.
     */
    private function assertOldEnoughToRegister(string $day, string $month, string $year): void
    {
        $monthMap = [
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
            'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
        ];

        $d = (int) trim($day);
        $m = $monthMap[strtolower(substr(trim($month), 0, 3))] ?? (int) trim($month);
        $y = (int) trim($year);

        if ($d < 1 || $m < 1 || $m > 12 || $y < 1900 || !checkdate($m, $d, $y)) {
            throw new \Src\Exceptions\BadRequestException('Please select a valid date of birth.');
        }

        $dob   = new \DateTimeImmutable(\sprintf('%04d-%02d-%02d', $y, $m, $d));
        $today = new \DateTimeImmutable('today');

        if ($dob > $today) {
            throw new \Src\Exceptions\BadRequestException('Your date of birth cannot be in the future.');
        }

        if ($dob->diff($today)->y < self::MIN_SELF_REGISTER_AGE) {
            throw new \Src\Exceptions\BadRequestException(
                'You must be at least ' . self::MIN_SELF_REGISTER_AGE
                . ' to create your own account. Ask a parent or guardian to '
                . 'register and add you to the family tree.'
            );
        }
    }

    /**
     * Summary of setId
     * @param array $postData - array data from the input form
     * @param string|int $name - the input name 
     * @param string $table = the db table to save the id 
     * @throws Exception
     * @return array
     */
    private function setId(array $postData, string|int $name, string $table): array
    {

        $sanitiseName = ($postData["$name"]) ? checkInput($postData["$name"]) : throw new Exception("Provide Info");
        $sanitiseName = is_string($sanitiseName) ? $sanitiseName : '';

        $idName = preg_replace('/[^A-Za-z ]/', '', $sanitiseName) ?? '';

        do {
            $id = random_int(1000, 900000);
            $id .= strtoupper($idName);

            //check if the reference number exist
            $query = Select::formAndMatchQuery(selection: 'SELECT_COUNT_ONE', table: $table, identifier1: 'id');
            $idCheck = Select::selectFn2($query, [$id]);
        } while (count($idCheck) > 0);

        $postData['id'] = $id;
        return $postData;
    }

    /**
     * Check if a contact (mobile or email) exists in the database.
     * Used asynchronously on the registration form to avoid leaking PII.
     */
    public function checkContact(): void
    {
        CorsHandler::setHeaders();
        try {
            // SEC-5 — this endpoint is an unauthenticated "does this contact
            // exist?" oracle. Throttle per IP so it can't be swept to enumerate
            // the member base. 30 checks / 5 min is well above a real form fill.
            try {
                Limiter::limit(\Src\Utility::getUserIpAddr());
            } catch (\Src\Exceptions\TooManyRequestsException $e) {
                http_response_code(429);
                echo json_encode(['status' => 'error', 'message' => 'Too many requests. Please slow down.']);
                return;
            }

            // Read JSON payload
            $rawInput = file_get_contents('php://input');
            $input = $rawInput !== false ? json_decode($rawInput, true) : null;
            if (!is_array($input)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
                return;
            }

            $mobile = !empty($input['mobile']) ? checkInput($input['mobile']) : null;
            $email = !empty($input['email']) ? checkInput($input['email']) : null;
            
            if (!$mobile && !$email) {
                echo json_encode(['status' => 'error', 'message' => 'No mobile or email provided']);
                return;
            }

            $dbConnection = self::connect2();

            $exists = false;
            
            if ($mobile) {
                $stmt = $dbConnection->prepare("SELECT COUNT(*) FROM contact WHERE mobile = ?");
                $stmt->execute([$mobile]);
                if ($stmt->fetchColumn() > 0) {
                    $exists = true;
                }
            } 
            
            if ($email && !$exists) {
                $stmt = $dbConnection->prepare("SELECT COUNT(*) FROM contact WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $exists = true;
                }
            }

            echo json_encode(['status' => 'success', 'exists' => $exists]);
            return;
            
        } catch (\Throwable $th) {
            error_log((string)$th);
            echo json_encode(['status' => 'error', 'message' => 'An internal error occurred']);
            return;
        }
    }

    /**
     * Resolves the family surname associated with a given family code from database tables.
     */
    public static function getFamilySurnameByCode(string $famCode): ?string
    {
        $famCode = trim($famCode);
        if ($famCode === '') {
            return null;
        }

        try {
            $db = self::connect2();
            $stmt = $db->prepare("SELECT lastName FROM personal WHERE famCode = ? AND lastName IS NOT NULL AND TRIM(lastName) != '' ORDER BY id ASC LIMIT 1");
            $stmt->execute([$famCode]);
            $surname = $stmt->fetchColumn();
            if ($surname && is_string($surname) && trim($surname) !== '') {
                return trim($surname);
            }

            $stmtNode = $db->prepare("SELECT last_name FROM family_nodes WHERE family_code = ? AND last_name IS NOT NULL AND TRIM(last_name) != '' ORDER BY id ASC LIMIT 1");
            $stmtNode->execute([$famCode]);
            $nodeSurname = $stmtNode->fetchColumn();
            if ($nodeSurname && is_string($nodeSurname) && trim($nodeSurname) !== '') {
                return trim($nodeSurname);
            }
        } catch (\Throwable $e) {
            // Fall back silently
        }

        return null;
    }
}
