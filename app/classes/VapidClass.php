<?php

namespace App\classes;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;


class VapidClass
{
    private function index(): array
    {
        $vapidPublicKey = self::urlBase64ToUint8Array(getenv('VAPID_PUBLIC_KEY'));
        $vapidPrivateKey = self::urlBase64ToUint8Array(getenv('VAPID_PRIVATE_KEY'));
        $auth = array(
            'VAPID' => array(
                'subject' => 'mailto:waledevtest@gmail.com',
                'publicKey' => $vapidPublicKey,
                'privateKey' => $vapidPrivateKey,
            ),
        );
        return $auth;
    }

    // Function to send push notifications
    public function sendPushNotifications($subscriptionData, $payload, $auth)
    {
        $subscription = Subscription::create([
            'endpoint' => $subscriptionData['endpoint'],
            'keys' => [
                'p256dh' => $subscriptionData['keys']['p256dh'],
                'auth' => $subscriptionData['keys']['auth'],
            ],
        ]);

        // Create a new WebPush instance
        $webPush = new WebPush($auth);

        // Send the push notification
        $webPush->sendNotification(
            $subscription,
            $payload // The notification payload (JSON string)
        );


        // Handle any errors or confirmations
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                echo "Notification sent successfully!";
            } else {
                echo "Failed to send notification: " . $report->getReason();
            }
        }





    }

    public static function urlBase64ToUint8Array($base64String) 
    {
        $padding = str_repeat('=', (4 - strlen($base64String) % 4) % 4);
        $base64 = strtr($base64String . $padding, '-_', '+/');
        $rawData = base64_decode($base64);
        $outputArray = array();
        for ($i = 0; $i < strlen($rawData); ++$i) {
            $outputArray[] = ord($rawData[$i]);
        }
        return $outputArray;
    }
}
   


