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


    /**
     * @param false|string $base64String
     *
     * @return int[]
     *
     * @psalm-return list{0?: int<0, 255>,...}
     */
    public static function urlBase64ToUint8Array(string|false $base64String): array 
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
   


