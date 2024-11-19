<?php 

// namespace
namespace App\classes;


class EventEmitter {
    private static $listeners = [];

    public static function on($event, $callback) {
        self::$listeners[$event][] = $callback;
    }

    public static function emit($event, $data) {
        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $listener) {
                $listener($data);
            }
        }
    }
}
