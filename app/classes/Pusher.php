<?php

namespace App\classes;
use Pusher\Pusher as PusherNotification;


class Pusher  {
     
    public static function pusher(): PusherNotification
    {
        $pusher = new PusherNotification(
            getenv('MIX_PUSHER_APP_KEY'),
            getenv('MIX_PUSHER_APP_SECRET'),
            getenv('MIX_PUSHER_APP_ID'),
            [
                'cluster' => getenv('MIX_PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ]
        );
        return $pusher;
    }

    public static function broadcast(string $theChannel, string $theEvent, array $theData): void
    {
        $pusher = self::pusher();
        $pusher->trigger($theChannel, $theEvent, $theData);
    }
}