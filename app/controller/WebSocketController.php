<?php 

namespace App\Controller;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\classes\websocket\NotificationWebSocketHandler;

class WebSocketController 
{
    public function startServer()
    {
      try{
              // Ensure this route triggers the WebSocket server

        $webSocket = new WsServer(new NotificationWebSocketHandler());
              echo "it works";
      

        $server = IoServer::factory(
            component: new HttpServer($webSocket),
            port: 8082,
            address: '0.0.0.0'
        );

        echo "WebSocket server started at ws://".URL.":8080\n";

        $server->run();

      
      } catch(\Throwable $th){
          showError($th);
      }
  
    }
}
