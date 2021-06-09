<?php

namespace App\classes\websocket;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use React\Socket\Server;
use React\EventLoop\Factory;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;
use React\Socket\ConnectionInterface;

use App\classes\Pusher;


class ReactServer
{

    public static function startChat()
    {
        try {
            $loop = Factory::create();
            $pusher = new Pusher;
            $socket = new Server('127.0.0.1:80', $loop);
            $webServer = new IoServer(
                new HttpServer(
                    new WsServer(
                        new WampServer($pusher)
                        )
                ), $socket);
            // set up incoming TCP socket to receive the messages to push
             $socket = new Server(getenv('APP_URL'), $loop);
            $socket->on('connection', function(ConnectionInterface $conn) use ($pusher){
                // new data arrives at the socket 
                $conn->on('data', function($data) use ($conn, $pusher) {
                    //push the new blog entry update 
                    $pusher->onBlogEntry($data);
                    // close the incoming connection when the message is consumed
                    $conn->close();
                });
            });

            return $loop->run();
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
