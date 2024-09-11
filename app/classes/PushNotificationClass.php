<?php

namespace App\classes;
use Minishlink\WebPush\WebPush;
use App\classes\Select;

class PushNotificationClass extends VapidClass
{
  // Function to send push notifications
  public static function sendPushNotification($userId, $message, $url=null)
  {
    // You can use the web-push-php library here to send notifications
    $subscription = self::getUserPushSubscription($userId);  // Retrieve user's push subscription details
    

    if ($subscription) {
      $auth = [
           'VAPID' => array(
                'subject' => 'mailto:waledevtest@gmail.com',
                'publicKey' => self::urlBase64ToUint8Array(getenv('VAPID_PUBLIC_KEY')),
                'privateKey' => self::urlBase64ToUint8Array(getenv('VAPID_PRIVATE_KEY')),
            ),
      ];

      $webPush = new WebPush($auth);

      $webPush->sendOneNotification(
        $subscription,
        json_encode([
          'title' => 'New Notification',
          'body' => $message,
          'url' => $url,  // Change the URL to where the user should be redirected
          'icon' => getenv('APP_LOGO')  
        ])
      );

      foreach ($webPush->flush() as $report) {
        if (!$report->isSuccess()) {
          error_log("Push notification failed for subscription: {$report->getReason()}");
        }
      }
    }
  }

  // Fetch the user's push subscription data from the database
  private static function getUserPushSubscription($userId)
  {
        $queryData = [
        'selection' => 'SELECT_ONE',
        'table' => 'push_notification',
        'identifier1' => 'id',
        'bind' => [$userId]
    ];

    return Select::combineSelect($queryData, 'selectFn2', 'ONE_IDENTIFIER');


  }


}