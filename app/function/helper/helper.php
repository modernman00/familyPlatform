<?php

declare(strict_types=1);

use Philo\Blade\Blade;
use App\classes\Select;
use Spatie\ImageOptimizer\OptimizerChainFactory as ImgOptimizer;
use App\classes\VirusScan as ScanVirus;
use Intervention\Image\ImageManager as Image;
use App\exceptions\HttpExceptions;


function view($path, array $data = [])
{
    // 1st param = accept the path to the file we actually want to load
    //2nd param array = any data that we want to pass to the view
    $view = __DIR__ . "/../../../resources/view";
    $cache = __DIR__ . "/../../../bootstrap/cache";
    $blade = new Blade(cachePath: $cache, viewPaths: [$view]);
    // 2 parameters
    //requires a path to the view -> folder where views are located
    // 2nd is the path to a cache directory -> cache under bootstrap
    echo $blade->view()->make($path, $data)->render();
}

function toSendEmail(string $viewPath, array $data, string $subject, string $emailRoute)
{
    // generate the data to send the email
    $sendEmailArray = genEmailArray(
        viewPath: $viewPath,
        data: $data,
        subject: $subject
    );

    // send the email
    return sendEmailWrapper(var: $sendEmailArray, recipientType: $emailRoute);
}

/**
 * @return false|string
 */
function make($fileName, $data): string|false
{
    extract($data);
    ob_start();  // turn on output buffering so no output is sent but store inside the internal buffer
    include __DIR__ . "/../../resources/views/emails/$fileName.php"; // include template
    $content = ob_get_contents(); //get content out of the file
    ob_end_clean(); // erase output turn off output buffering although stored in $content variable
    return $content;
}

function printArr($data): void
{

    if ($data === array()) {
        echo "<pre>";
        var_export($data);
        echo "</pre>";
    } else {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}



function replace_whitespace($string): string|null
{
    //Lower case everything
    $string = strtolower($string);
    //Make alphanumeric (removes all other characters)
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean up multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return $string;
}


/**
 * @return null|string
 */
function number2word(int $number)
{

    try {
        $fool = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $fool->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");
        $output = $fool->format($number);
        return ucfirst($output);
    } catch (TypeError $e) {
        echo $e->getMessage() . "\n";
    }
}
/**
 * 
 * @param string $postName  'user_input'
 * @param array $policyScores [ 'policyA' => 5, 'policyB' => 3,'policyC' => 1,]
 * @return int 
 */



function Scoring(string $postName, array $policyScores): int
{
    // Default score
    $defaultScore = 2;

    if (!isset($_POST[$postName])) {
        return $defaultScore;
    }

    $userInput = $_POST[$postName]; // `$_POST

    // Match the user input with the policy scores
    return match (true) {
        array_key_exists($userInput, $policyScores) => $policyScores[$userInput],
        default => $defaultScore,
    };
}



function setMinMaxLimit(array $minPolicy, array $maxPolicy, array $post): array|string
{
    $error = [];
    for ($x = 0; $x < count($minPolicy); $x++) {

        if (strlen($post[$x]) < $minPolicy && strlen($post[$x]) > $maxPolicy) {
            $cleanNameKey = strtoupper(preg_replace('/[^0-9A-Za-z@.]/', ' ', $post[$x]));
            $error  = "The response {-$cleanNameKey-} response does not meet either the required minimal or maximum limit";
        };
    };

    return $error;
}

/**
 * compare two variable or use to verify
 */
function compare($var1, $var2): bool
{
    if ($var1 != $var2) {
        return false;
    }
    return true;
}

// GET IP ADDRESS

function getUserIpAddr(): string
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * $month is the terms
 * while the date is the month
 *
 * @return string[]
 *
 * @psalm-return array{fullDate: string, dateFormat: string}
 */
function addMonthsToDate($months, $date): array
{

    $dt = new DateTime($date, new DateTimeZone('Europe/London'));
    $oldDay = $dt->format("d");
    $dt->add(new DateInterval("P{$months}M"));
    $newDay = $dt->format("d");
    if ($oldDay != $newDay) {
        // Check if the day is changed, if so we skipped to the next month.
        // Substract days to go back to the last day of previous month.
        $dt->sub(new DateInterval("P" . $newDay . "D"));
    }
    $newDay3 = $dt->format("Y-m-d");
    $newDay2 = $dt->format(" jS \of F Y"); // 2016-02-29
    //  return $newDay2;
    $datetime = ['fullDate' => $newDay2, 'dateFormat' => $newDay3];
    return $datetime;
}


function cleanSession($x): string|null|int
{
    if ($x) {
        $z = preg_replace(
            pattern: '/[^0-9A-Za-z@.]/',
            replacement: '',
            subject: $x
        );
        return $z;
    } else {
        return null;
    }
}

// SHOW THE ERROR EXCEPTION MESSAGE

function showError($th): void
{
    if (getenv('APP_ENV') === 'local') {
        error_log("Error: " . $th->getMessage());
        $error = (int) $th->getCode() ?? 500;
        if ($th instanceof HttpExceptions) {
            http_response_code($th->getStatusCode());
            echo json_encode([
                'error' => $th->getMessage()
            ]);
        } else {
            http_response_code($error);
            echo json_encode([
                'error' =>  $error = "Error on line {$th->getLine()} in {$th->getFile()}\n\n: The error message is {$th->getMessage()}\n\n"
            ]);
        }
    } else {
        if ($th instanceof HttpExceptions) {
            http_response_code($th->getStatusCode());
            echo json_encode([
                'error' => $th->getMessage()
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'error' => 'An unexpected error occurred. ' . $th->getMessage()
            ]);
        }
    }
}

/**
 * Summary of showErrorExp
 * @param mixed $th
 * @return void
 */
function showErrorExp($th): void
{
    echo "Error msg - " . $th->getMessage();
    echo "<br>";
    echo "Error Line - " . $th->getLine();
    echo "<br>";
    echo "Error code - " . $th->getCode();
    echo "<br>";
    echo "Error File- " . $th->getFile();
}

function showSSEError($th): void
{
    error_log("SSE Error: " . $th->getMessage());
    echo "Error msg - " . $th->getMessage() . $th->getFile() . $th->getLine();
    echo "<br>";
    echo "data: Error occurred\n\n";
    ob_flush();
    flush();
}


function spinner(): string
{
    return  '<div class="loader"></div>';
}

// FUNCTION TO SEND TEXT TO PHONE


function sendText($message, $numbers): void
{

    $apiKey = urlencode('y9X1o/Ko6M4-MCz6zJfBeGMv9TMOLG54k0c53EfCfo');
    $numbers = array($numbers);
    $sender = urlencode('Loaneasy Finance');
    $message = rawurlencode($message);
    $numbers = implode(',', $numbers);
    // Prepare data for POST request
    $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
    $ch = curl_init('http://api.txtlocal.com/send/');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch); // This is the result from the API
    curl_close($ch);
    echo $result;
}

// function to use php-clamavlib
// if($_POST){
//   $error = '';
//   //print_r($_FILES);
//   if($_FILES['file']['size'] == 0 || !is_file($_FILES['file']['tmp_name'])){
//      $error .= 'Please select a file for upload!';
//   } else {
//     cl_setlimits(5, 1000, 200, 0, 10485760);
//     if($malware = cl_scanfile($_FILES['file']['tmp_name'])) $error .= 'We have Malware: '.$malware.'<br>ClamAV version: '.clam_get_version();
//   }
//   if($error == ''){
//     rename($_FILES['file']['tmp_name'], $upload_dir.$_FILES['file']['name']);
//   }
// }

//NOTE: Ensure Socket Path: Verify the ClamAV socket path in getClamavSocket matches your server's configuration.

// Function to scan file for viruses using ClamAV
// function scanFileForVirus($file)
// {


//     $validator = new ClamClient();

//     // printArr($file);

//     // // always check this code as originally it accepts three arguments
//     // $validateFile = $validator->validate($file);

//     if (!$validateFile) {
//         msgException(500, "virus detected");
//     }
// }

// return email once logged in

/**
 * fileLocation: where you want to save the file
 * formInputName : the input name of the $_file
 */
function fileUploadMultiple($fileLocation, $formInputName): array
{
    // scanFileForVirus($_FILES[$formInputName]);
    // Count total files
    $saveFiles = [];
    $countFiles = count($_FILES[$formInputName]['name']);

    // Looping all files
    for ($i = 0; $i < $countFiles; $i++) {
        $fileName = basename($_FILES[$formInputName]['name'][$i]);
        // trim out the space in the file name
        $fileName = str_replace(' ', '', $fileName);
        $fileName = str_replace(',', '', $fileName);
        $fileInfo = pathinfo($fileName);
        $baseName = $fileInfo['filename']; // e.g., "WhatsAppImage2021-01-24at12.00.04(1)"
        $extension = strtolower($fileInfo['extension']); // e.g., "jpeg"
        // Sanitize base name: replace dots and parentheses
        $baseName = preg_replace('/\./', '_', $baseName); // Replace dots with underscores
        $baseName = preg_replace('/[()]/', '', $baseName); // Replace parentheses with underscores

        // Remove any extra underscores that might result from consecutive replacements
        $baseName = preg_replace('/_+/', '_', $baseName); // Replace multiple underscores with a single one
        $fileName = time() . '_' . $baseName . '.' . $extension; // e.g., "WhatsAppImage2021-01-24at12_00_04_1.jpeg"
        $fileTemp = $_FILES[$formInputName]['tmp_name'][$i];
        $fileSize = $_FILES[$formInputName]['size'][$i];
        $pathToImage = "$fileLocation$fileName"; // e.g., "1652634567_WhatsAppImage2021-01-24at12_00_04_1.jpeg"
        $fileError = $_FILES[$formInputName]['error'][$i];
        // Check for upload errors
        if ($fileError !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File size exceeds the maximum allowed size (upload_max_filesize)',
                UPLOAD_ERR_FORM_SIZE => 'File size exceeds the maximum allowed size (form limit)',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            ];
            $errorMsg = $errorMessages[$fileError] ?? 'Unknown upload error';
            throwError(400, $errorMsg);
            continue;
        }

        // virus scan using ClamAV
        new ScanVirus(tempFileLocation: $fileTemp);

        // Validate file
        $picError = "";
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedFormats = ['png', 'jpg', 'gif', 'jpeg', 'heic'];

        if (!in_array($fileExtension, $allowedFormats)) {
            $picError .= 'Format must be PNG, JPG, GIF, HEIC or JPEG. ';
            throwError(400, "IMAGE FORMAT - $picError");
        }


        if ($fileSize > 10485760) { // 10 MB
            $picError .= 'File size must not exceed 10MB';
            throwError(400, "Error Processing Request - post images - $picError");
        }
        // if (file_exists($pathToImage)) {
        //     $picError .= "File $fileName already uploaded";
        //     throwError(401, "Error Processing Request - post images - $picError");
        // }
        if ($picError) {
            throwError(400, "Error Processing Request - post images - $picError");
            continue; // skip this file upload
        }

        // Move uploaded file
        if (!move_uploaded_file($fileTemp, $pathToImage)) {
            $_SESSION['imageUploadOutcome'] = 'Image was not successfully uploaded';
            throwError(400, "Error Processing Request - post images - Image was not successfully uploaded");
            continue; // Skip optimization if upload failed
        }

        // Resize and crop image
        try {
            if (!file_exists($pathToImage) || !is_readable($pathToImage)) {
                throw new Exception("Cannot read image at: $pathToImage");
            }
            if (!is_writable(dirname($pathToImage))) {
                throw new Exception("Cannot write to directory: " . dirname($pathToImage));
            }

            $image = Image::imagick()->read($pathToImage) ?? Image::gd()->read($pathToImage);
            $image->cover(300, 200);
            $tempPath = $pathToImage . '.tmp';
            $image->save($tempPath);
            if (file_exists($tempPath)) {
                rename($tempPath, $pathToImage);
            } else {
                throw new Exception("Failed to save resized image at $tempPath");
            }
        } catch (Exception $e) {
            throw new Exception("Image processing failed: " . $e->getMessage());
        }

        // Optimize the image
        $optimizerChain = ImgOptimizer::create();
        $optimizerChain->optimize($pathToImage);
        $_SESSION['imageUploadOutcome'] = 'Image was successfully uploaded';

        $saveFiles[] = $fileName;
    }

    return $saveFiles;
}
/**
 * fileLocation: where you want to save the file
 * formInputName : the input name of the $_file
 */
function fileUpload($fileLocation, $formInputName): void
{
    if (!is_dir($fileLocation) || !is_writable($fileLocation)) {
        throw new Exception("Invalid or unwritable file location", 400);
    }

    $file = $_FILES[$formInputName];
    $fileName = basename($file['name']);
    $fileTemp = $file['tmp_name'];
    $fileError = $file['error'];
    $fileSize = $file['size'];

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fileTemp);
    finfo_close($finfo);
    $allowedMimes = ['image/png', 'image/jpeg', 'image/gif'];
    if (!in_array($mime, $allowedMimes)) {
        throw new Exception("Invalid file type", 400);
    }

    // trim out the space in the file name
    $fileName = str_replace(' ', '', $fileName);

    // Check for virus using ClamAV
    new ScanVirus(tempFileLocation: $fileTemp);

    if (!$fileName) {
        throw new Exception("No File Name ", 1);
    }

    if ($fileError !== UPLOAD_ERR_OK) {

        throw new Exception('File upload error: ' . $fileError);
    }

    if (!$fileTemp) {
        throw new Exception("No Temp File", 1);
    }

    # the file temp name

    if (!$fileSize) {
        throw new Exception("File has no size", 1);
    }

    // throw exception if fileLocation does not exist  
    if (!file_exists($fileLocation) || !is_dir($fileLocation)) {
        throw new \Exception("File location does not exist", 1);
    }

    $fileName_location = "$fileLocation$fileName";

    // sanitise the file
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION); # use pathinfo to get the file extension
    $fileExtension = strtolower($fileExtension); #turn the extension to a lowercase
    if ($fileExtension != 'png' && $fileExtension != 'jpg' && $fileExtension != 'gif' && $fileExtension != 'jpeg') {
        throw new \Exception("Format must be PNG, JPG, JPEG or GIF", 1);
    }
    if ($fileSize > 10485760) { // 10 MB

        throwError(400, "post imagee size error");
    }
    if (file_exists($fileName)) {
        throw new \Exception("File $fileName already uploaded", 1);
    }
    if (!move_uploaded_file($fileTemp, $fileName_location)) {
        throw new \Exception("Sorry, there was an error uploading your file.", 1);
    }

    $optimizerChain = ImgOptimizer::create();
    $optimizerChain->optimize($fileName_location);
}

// ADD COUNTRY CODE

function addCountryCode($mobile, $code): string
{
    $telephone = $mobile;
    $telephone = substr($telephone, 1);
    $telephone = $code . $telephone;
    return $telephone;
}

/**
 * return a bulma panel row
 */
function bulmaPanelMoney($input, $label, $color): void
{
    echo " <div class='column'>
        <div class='panel panel-$color'>
            <div class='panel-heading'>
                <div class='has-text-centered'>
                    <div class=huge>
                       £" . number_format($input)
        . "</div>
                    <div>$label</div>
                </div>
            </div>
        </div>
    </div>";
}


function bulmaPanel($input, $label, $color): void
{
    echo " <div class='column'>
        <div class='panel panel-$color'>
            <div class='panel-heading'>
                <div class='has-text-centered'>
                    <div class=huge>
                    $input
                    </div>
                    <div>$label</div>
                </div>
            </div>
        </div>
    </div>";
}

function changeToJs($variableName, $variable): void
{
    echo "<script> const $variableName = $variable </script>";
}

/**
 * 
 * @param mixed $time that is the full date and time e.g 2010-04-28 17:25:43
 * @return string | bool
 */

function humanTiming($time)
{
    try {
        $time = strtotime($time);
        $time = time() - $time; // to get the time since that moment
        $time = ($time < 1) ? 1 : $time;
        $tokens = array(
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
        }
        return 'just now'; // Default fallback if time is not within any unit
    } catch (\Throwable $th) {
        showError($th);
        return false;
    }
}

function milliSeconds(): string
{
    $microtime = microtime();
    $comps = explode(' ', $microtime);

    // Note: Using a string here to prevent loss of precision
    // in case of "overflow" (PHP converts it to a double)
    return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
}



function checkEmailExist($email): array|int|string
{
    $query = Select::formAndMatchQuery(selection: 'SELECT_COUNT_ONE', table: 'account', identifier1: 'email');
    return Select::selectFn2(query: $query, bind: [$email]);
}

function getPostDataAxios()
{
    return json_decode(file_get_contents("php://input"), true);
}
