<?php

namespace App\classes;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\classes\Select;

class PushNotificationClass extends VapidClass
{
  // Function to send push notifications
  public static function sendPushNotification(string $userId, string $message, $url = null)
  {
    // You can use the web-push-php library here to send notifications
    $subscription = self::getUserPushSubscription($userId);  // Retrieve user's push subscription details


    if ($subscription) {
      // Validate VAPID keys exist (using MIX_ prefix to match .env)
      $vapidPublicKey = $_ENV['MIX_VAPID_PUBLIC_KEY'] ?? getenv('MIX_VAPID_PUBLIC_KEY');
      $vapidPrivateKey = $_ENV['MIX_VAPID_PRIVATE_KEY'] ?? getenv('MIX_VAPID_PRIVATE_KEY');

      if (empty($vapidPublicKey) || empty($vapidPrivateKey)) {
        throw new \Exception(
          'VAPID keys are not configured. Please set MIX_VAPID_PUBLIC_KEY and MIX_VAPID_PRIVATE_KEY in your .env file. ' .
          'Public key: ' . ($vapidPublicKey ? 'SET' : 'MISSING') . ', ' .
          'Private key: ' . ($vapidPrivateKey ? 'SET' : 'MISSING')
        );
      }

      // VAPID keys should be base64url-encoded strings, not arrays
      $auth = [
        'VAPID' => [
          'subject' => 'mailto:waledevtest@gmail.com',
          'publicKey' => $vapidPublicKey,  // Pass as string directly
          'privateKey' => $vapidPrivateKey, // Pass as string directly
        ],
      ];

      $webPush = new WebPush(auth: $auth);

      // DEBUG: Show what we got from the database
      error_log("Subscription data from database: " . print_r($subscription, true));

      // The database query returns an array of results, get the first one
      if (is_array($subscription) && isset($subscription[0])) {
        $subscription = $subscription[0];
      }

      // Convert subscription array to Subscription object
      // The database returns an array, but we need a Subscription object
      if (!isset($subscription['endpoint'])) {
        throw new \Exception(
          'Invalid subscription data. Missing endpoint. ' .
          'Available keys: ' . implode(', ', array_keys($subscription ?? [])) . '. ' .
          'Full data: ' . json_encode($subscription)
        );
      }

      // Handle different possible column names from database
      // Your database uses: p256dhKey and authKey
      $p256dh = $subscription['p256dhKey']      // Your database column name
        ?? $subscription['keys']['p256dh']  // Standard nested structure
        ?? $subscription['p256dh']           // Alternative flat structure
        ?? $subscription['key']              // Another alternative
        ?? null;

      $auth = $subscription['authKey']          // Your database column name
        ?? $subscription['keys']['auth']      // Standard nested structure
        ?? $subscription['auth']              // Alternative flat structure
        ?? $subscription['authSecret']        // Another alternative
        ?? null;

      if (empty($p256dh) || empty($auth)) {
        throw new \Exception(
          'Invalid subscription data. Missing encryption keys. ' .
          'p256dh: ' . ($p256dh ? 'SET' : 'MISSING') . ', ' .
          'auth: ' . ($auth ? 'SET' : 'MISSING') . '. ' .
          'Full data: ' . json_encode($subscription)
        );
      }

      $subscriptionObject = Subscription::create([
        'endpoint' => $subscription['endpoint'],
        'keys' => [
          'p256dh' => $p256dh,
          'auth' => $auth,
        ],
      ]);

      $webPush->sendOneNotification(
        subscription: $subscriptionObject,  // Use the Subscription object
        payload: json_encode(value: [
          'title' => 'New Notification',
          'body' => $message,
          'url' => $url,  // Change the URL to where the user should be redirected
          'icon' => getenv(name: 'APP_LOGO')
        ])
      );

      // Send notifications
      foreach ($webPush->flush() as $report) {
        if ($report->isSuccess()) {
          echo "Notification sent successfully.\n";
        } else {
          echo "Failed: " . $report->getReason() . "\n";
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