<?php

declare(strict_types=1);

namespace App\controller\members;

use App\classes\{
    Db,
    Insert,
    Sanitise,
    InnerJoin
};

use App\model\AllMembersData;
// use Exception;


class Event extends AllMembersData
{
    private const REDIRECT = "Location: /member/ProfilePage";

    static function processEvent()
    {

        try {
            // SANITISE THE ENTRY
            $sanitise = new Sanitise($_POST);

            $result = $sanitise?->getData();
            $error = $sanitise?->error;

            if (count($error) > 0) {
                view('error/genError', compact('error'));
            }
            $result['id'] = checkInput($_SESSION['id']);

            Insert::submitForm2('events', $result);

            header(self::REDIRECT);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    static function sendReminder()
    {
            // * 2. get the data from the database about all event; get the id of the event 

        $data = parent::getMembers(['personal', 'events'], 'eventDate');

        // printArr($data);

        //   * 1. set today's day 

        $todayDate = date('Y-m-d');

        //  * 2. get the difference between today and the date itself 

        $eventDate = date('21-04-08');
        $eventDateSevenDays = modifyDate($eventDate, '7 days', 'date_add')['fullDate'];
        echo $eventDateSevenDays;
        // // GET THE EVENT DAY AND MONTH
        // $eventMonth = date('m', strtotime($eventDate));
        // $eventDay = date('d', strtotime($eventDate));


        // 7 DAYS  

        foreach($data as $data){
          $diff = dateDifference($todayDate, $data['eventDate']);
          if($diff === "-7 days") {

              // DO THIS 

              // generate email wrapper 
              $view = 'msg/events/event';
              $firstName = $data['firstName'];
              $eventName = $data['eventName'];
              $subject = "$firstName's $eventName EVENT is on the $eventDateSevenDays";

              $emailWrapper = genEmailArray($view, $data, $subject);

              sendEmailWrapper($emailWrapper, "admin");

              /**
               * send a message to all depending on the group on the events using location
               */

              

              

          } elseif ($diff = "0 days") {
              // DO THIS

              // ALSO CHECK IF THE EVENT IS RECURRING
              // IF YES, ADD THE RECURRING TO THE EVENT DATE AND UPDATE THE EVENT DATE
          }
        }
        //  // $diff == "-9 days"?  echo "xxxx". BR : ;
          
        // }



        /**
       
     
        
         * if it 7 days, send a 7 day reminder to the group by location excluding the id from the event
         * if it 1 day, send a 1 day reminder 
         * if same day, send a message to the celebrants
         * Build the message template based on the celebration
         * WHAT TO BUILD: 
         * 
         */



        // $today = date('Y-m-d');

        // if ($today == $notify1) {
        //     $subject = "Your SHOWAL payment will be due in 7 days: " . $notify10;

        //     $text = "Gentle reminder: Hello $name, Your Loan payment of ₦$expected_nextpayment will be due in 7 days: $notify10";
        // }
        // if ($today == $notify2) {
        //     $subject = "Your SHOWAL payment will be due tomorrow: " . $notify10;

        //     $text = "Gentle reminder: Hello $name, Your Showal Loan payment of ₦$expected_nextpayment will be due tomorrow: $notify10";
        // }
        // if ($today == $paymentdd) {
        //     $subject = "Your SHOWAL payment will be due today: " . $notify10;

        //     $text = "Hello $name, Your Showal Loan payment of ₦$expected_nextpayment is due today: $notify10";
        // }

        // ob_start();
        // require(__DIR__ . "/paymentHtml.php");
        // $pdf2 = ob_get_contents();
        // ob_end_clean();


        // for ($i = 0; $i < 3; $i++) {
        //     if ($today == $notify[$i]) {
        //         send_email('wale@showalinvest.com', $name, $subject, $pdf2);
        //     }
        // }
    }
}
