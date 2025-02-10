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
    try {
      // Ensure this route triggers the WebSocket server

      $webSocket = new WsServer(new NotificationWebSocketHandler());
      echo "it works";


      $server = IoServer::factory(
        component: new HttpServer($webSocket),
        port: 8082,
        address: '0.0.0.0'
      );

      echo "WebSocket server started at ws://" . URL . ":8080\n";

      $server->run();
    } catch (\Throwable $th) {
      showError($th);
    }
  }


  public static function sendSSEResponse()
  {

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('Access-Control-Allow-Origin: *');

    // Function to send SSE data
    function sendEvent($data)
    {
      echo "data: " . json_encode($data) . "\n\n";
      flush(); // Ensure the data is sent
    }

    // Example: Send a new message every 2 seconds
    $counter = 0;
    while (true) {
      $data = [
        'message' => "Hello, this is message $counter",
        'timestamp' => date('Y-m-d H:i:s')
      ];
      sendEvent($data);
      $counter++;
      sleep(10); // Wait for 2 seconds
    }
  }

    public static function getSSEResponse()
  {

  view('test');
  }
}
