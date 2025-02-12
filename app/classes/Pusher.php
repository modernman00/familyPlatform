<?php

namespace App\classes;


class Pusher  {
     
    public static function pusher()
    {
        $pusher = new Pusher(
            getenv('PUSHER_APP_KEY'),
            getenv('PUSHER_APP_SECRET'),
            getenv('PUSHER_APP_ID'),
            [
                'cluster' => getenv('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ]
        );
    }

    public static function broadcast($theChannel, $theEvent, $theData)
    {
        $pusher = self::pusher();
        $pusher->trigger($theChannel, $theEvent, $theData);
    }
}