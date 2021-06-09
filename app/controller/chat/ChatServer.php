<?php

namespace App\controller\chat;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\classes\Chat;

class ChatServer
{

    public static function startChat()
    {
        try {
            $server = IoServer::factory(
                new HttpServer(new WsServer(new Chat())),
                8080
            );
            return $server->run();
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
