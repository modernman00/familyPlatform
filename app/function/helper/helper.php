<?php

declare(strict_types=1);

use Philo\Blade\Blade;
use App\classes\Select;
use Spatie\ImageOptimizer\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory as ImgOptimizer;
use App\classes\VirusScan as ScanVirus;



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



// score application
function Scoring(string $postName, string $policy1, string $policy2, int $score1, int $score2): void
{
    if ($_POST[$postName] == $policy1) {
        ${$postName} = $score1;
    } else {
        if ($_POST[$postName] == $policy2) {
            ${$postName} = $score2;
        } else {
            ${$postName} = 2;
        }
    }

    ${$postName};
}

/**
 * @psalm-return array<empty, empty>|string
 */
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
    error_log("Error: " . $th->getMessage());
    $errorCode = (int) $th->getCode();
    http_response_code($errorCode); // sets the response to 406
    $error = "Error on line {$th->getLine()} in {$th->getFile()}\n\n: The error message is {$th->getMessage()}\n\n";

    echo json_encode(['error' => $error]);
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
    echo "Error msg - " . $th->getMessage() . $th->getFile(). $th->getLine();
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
function fileUploadMultiple($fileLocation, $formInputName): void
{
    // scanFileForVirus($_FILES[$formInputName]);
    // Count total files
    $countFiles = count($_FILES[$formInputName]['name']);

    ini_set('post_max_size', '20M');
    ini_set('upload_max_filesize', '20M');

    // Looping all files
    for ($i = 0; $i < $countFiles; $i++) {
        $fileName = basename($_FILES[$formInputName]['name'][$i]);
        $fileTemp = $_FILES[$formInputName]['tmp_name'][$i];
        $fileSize = $_FILES[$formInputName]['size'][$i];
        $pathToImage = "$fileLocation$fileName";

        new ScanVirus(tempFileLocation: $fileTemp);

        // sanitise the file
        $picError = "";
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileExtension = strtolower($fileExtension);

        if ($fileExtension != 'png' && $fileExtension != 'jpg' && $fileExtension != 'gif' && $fileExtension != 'jpeg') {
            $picError .= 'Format must be PNG, JPG, or GIF';
        }

        if ($fileSize > 102400000) {
            $picError .= 'File size must not exceed 10240kb';
            msgException(400, "Error Processing Request - post images - $picError");
        }
        // if (file_exists($pathToImage)) {
        //     $picError .= "File $fileName already uploaded";
        //     msgException(401, "Error Processing Request - post images - $picError");
        // }
        if ($picError) {
            msgException(400, "Error Processing Request - post images - $picError");
        }

        $uploadFile = move_uploaded_file($fileTemp, $pathToImage);

        // Check if upload was successful
        if (!$uploadFile) {
            $_SESSION['imageUploadOutcome'] = 'Image was not successfully uploaded';
            continue; // Skip optimization if upload failed
        }

        // Optimise the image

        $optimizerChain = ImgOptimizer::create();
        $optimizerChain->optimize($pathToImage);


        $_SESSION['imageUploadOutcome'] = 'Image was successfully uploaded';
    }
}

/**
 * fileLocation: where you want to save the file
 * formInputName : the input name of the $_file
 */
function fileUpload($fileLocation, $formInputName): void
{
    // UPLOAD PICTURE
    $fileName = basename($_FILES[$formInputName]['name']); #the fileName

    if (!$fileName) {
        throw new Exception("No File Name ", 1);
    }

    $fileError = $_FILES[$formInputName]['error'];

    if ($fileError !== UPLOAD_ERR_OK) {

        throw new Exception('File upload error: ' . $fileError);
    }

    $fileTemp = $_FILES[$formInputName]['tmp_name'];

    if (!$fileTemp) {
        throw new Exception("No Temp File", 1);
    }

       // Check for virus using ClamAV
    new ScanVirus(tempFileLocation: $fileTemp);

    # the file temp name
    $size = $_FILES[$formInputName]['size'];  # the file size
    if (!$size) {
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
    if ($size > 10240000) {
        throw new \Exception("File size is bigger than the required size", 1);
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




