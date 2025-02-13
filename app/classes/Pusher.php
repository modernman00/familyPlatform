<?php

namespace App\classes;
use Pusher\Pusher as PusherNotification;


class Pusher  {
     
    public static function pusher()
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

    public static function broadcast($theChannel, $theEvent, $theData)
    {
        $pusher = self::pusher();
        $pusher->trigger($theChannel, $theEvent, $theData);
    }
}