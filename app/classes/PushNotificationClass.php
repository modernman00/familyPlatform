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
                'publicKey' => self::urlBase64ToUint8Array(base64String: getenv('VAPID_PUBLIC_KEY')),
                'privateKey' => self::urlBase64ToUint8Array(base64String: getenv('VAPID_PRIVATE_KEY')),
            ),
      ];

      $webPush = new WebPush(auth: $auth);

      $webPush->sendOneNotification(
        subscription: $subscription,
        payload: json_encode(value: [
          'title' => 'New Notification',
          'body' => $message,
          'url' => $url,  // Change the URL to where the user should be redirected
          'icon' => getenv(name: 'APP_LOGO')  
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
  private static function getUserPushSubscription($userId): mixed
  {
        $queryData = [
        'selection' => 'SELECT_ONE',
        'table' => 'pushNotification',
        'identifier1' => 'id',
        'bind' => [$userId]
    ];

    return Select::combineSelect(array: $queryData, callback: 'selectFn2', switch: 'ONE_IDENTIFIER');


  }


}