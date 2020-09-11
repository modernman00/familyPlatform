<?php

use Philo\Blade\Blade;
use voku\helper\Paginator;
use App\model\EmailData;


    function view($path, array $data = []) {
        // 1st param = accept the path to the file we actually want to load
        //2nd param array = any data that we want to pass to the view
        $view = __DIR__."/../../../resources/view";
        $cache = __DIR__."/../../../bootstrap/cache";
        $blade = new Blade($view, $cache);
        // 2 parameters
        //requires a path to the view -> folder where views are located
        // 2nd is the path to a cache directory -> cache under bootstrap
          echo $blade->view()->make($path, $data)->render();
    }

    function make($filename, $data) {
        extract($data);
        ob_start();  // turn on output buffering so no output is sent but store inside the internal buffer
        include (__DIR__."/../../resources/views/emails/".$filename.".php"); // include template
        $content = ob_get_contents(); //get content out of the file
        ob_end_clean(); // erase output turn off output buffering although stored in $content variable
        return $content;

    }

    function printArr(array $data){
        echo "<pre>";
        var_export($data);
        echo "</pre>";
    }

    function dateFormat($date)
    {
        $stringDate = strtotime($date);
        $dateFormatted = date('jS \of F Y', $stringDate);
        return $dateFormatted;
    }

  function replace_whitespace($string) {
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

    // pagination function

    function pagination($recordNo, $recordTotal, $tableName, $model) {
        // no of record we want to show
        // argument
        //
        // create a new object
        $categories= [];
        $pages = new Paginator($recordNo,'p');
        $pages->set_total($recordTotal);
        //if using a database you limit the records by placing $pages->get_limit() in your query, this will limit the number of records
        $data = Capsule::select("SELECT * FROM $tableName ORDER BY created_at DESC" . $pages->get_limit());


        // by default the page_links method created links starting with ? this can be changed by passing in a parameter to the method:
        // The method also allows you to pass in extra data such as a series of GET's

        $categories = $model->transform($data);


        return [$categories, $pages->page_links()];



    }

  function number2word(int $number) {

       try {
            $fool = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
            $fool->setTextAttribute(\NumberFormatter::DEFAULT_RULESET, "%spellout-numbering-verbose");
            $output = $fool->format($number);
            return strtoupper($output);

       } catch (TypeError $e) {
           echo $e->getMessage(). "\n";
       }


  }

  function checkInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    $data = htmlentities($data);
    $data = preg_replace('/[^0-9A-Za-z.@-]/', ' ', $data);
    return $data;
  }

  function checkInput_email($data) {

        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = strip_tags($data);
        $data = htmlentities($data);
        $data = filter_var($data, FILTER_SANITIZE_EMAIL);
        return $data;
  }



  // score application
  function Scoring ($postName, $policy1, $policy2, int $score1, int $score2)
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

    function setMinMaxLimit(array $minPolicy, array $maxPolicy, array $post)
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
    function compare($var1, $var2)
    {
        if ($var1 != $var2) {
            return false;
        }
        return true;

    }

    // GET IP ADDRESS

    function getUserIpAddr()
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
     */
    function addMonthsToDate($months, $date) {

    $dt = new DateTime($date, new DateTimeZone('Europe/London'));
    $oldDay = $dt->format("d");
    $dt->add(new DateInterval("P{$months}M"));
    $newDay = $dt->format("d");
    if($oldDay != $newDay) {
    // Check if the day is changed, if so we skipped to the next month.
    // Substract days to go back to the last day of previous month.
    $dt->sub(new DateInterval("P" . $newDay . "D"));
    }
    $newDay3 = $dt->format("Y-m-d");
    $newDay2 = $dt->format(" jS \of F Y"); // 2016-02-29
  //  return $newDay2;
    $datetime = ['fullDate' => $newDay2, 'dateFormat'=>$newDay3];
    return $datetime;
}


function cleanSession($x) {
    $z = preg_replace('/[^0-9A-Za-z@.]/', '', $x);
    return $z;
  }

// SHOW THE ERROR EXCEPTION MESSAGE

  function showError($th) {
     echo "Error msg - ".$th->getMessage();
      echo "<br>";
      	 echo "Error Line - ".$th->getLine();
         echo "<br>";
			 echo "Error code - ".$th->getCode();
        echo "<br>";
			echo "Error File- " . $th->getFile();
            echo "<br>";
             echo "<br>";
  }


  function spinner()
  {
      return  '<div class="loader"></div>' ;
  }

  // FUNCTION TO SEND TEXT TO PHONE


function sendText($message, $numbers)
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

// return email once logged in



/**
 * filelocation: where you want to save the file
 * formInputName : the input name of the $_file
 */
function fileUpload($filelocation, $formInputName)
{
    // UPLOAD PICTURE
    $filename = basename($_FILES[$formInputName]['name']); #the filename
    $filetemp = $_FILES[$formInputName]['tmp_name'];    # the file temp name
    $size = $_FILES[$formInputName]['size'];  # the file size

    $filename_location = $filelocation . $filename;

    // sanitise the file
    $pic_error = "";
    $ext = pathinfo($filename, PATHINFO_EXTENSION); # use pathinfo to get the file extension
    $ext = strtolower($ext); #turn the extension to a lowercase
    if ($ext != 'png' && $ext != 'jpg' && $ext != 'gif') {
        $pic_error .= 'Format must be PNG, JPG, or GIF';
    }
    if ($size > 512000) {
        $pic_error .= 'File size must not exceed 500kb';
    }
    if (file_exists($filename)) {
        $pic_error .= "File $filename already uploaded";
    }

    if (!$pic_error) {
        move_uploaded_file($filetemp, $filename_location);
    }
}

// ADD COUNTRY CODE

function addCountryCode($mobile, $code)
{
    $telephone = $mobile;
    $telephone = substr($telephone, 1);
    $telephone = $code . $telephone;
    return $telephone;
}

/**
 * return a bulma panel row
 */

function bulmaPanelMoney($input, $label, $color)
{
    echo " <div class='column'>
        <div class='panel panel-$color'>
            <div class='panel-heading'>
                <div class='has-text-centered'>
                    <div class=huge>
                       £".number_format($input) 
                    ."</div>
                    <div>$label</div>
                </div>
            </div>
        </div>
    </div>";
}


function bulmaPanel($input, $label, $color)
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

function changeToJs($variableName, $variable)
{
    echo "<script> const $variableName = $variable </script>";

}





