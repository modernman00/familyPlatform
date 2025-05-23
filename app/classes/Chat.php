<?php 

namespace App\classes;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class Chat implements MessageComponentInterface 
{
    /**
     * @var \SplObjectStorage
     */
    protected \SplObjectStorage $client;

    public function __construct(){
        $this->client = new \SplObjectStorage;
    }

    /**
     * @return void
     */
    public function onOpen(ConnectionInterface $conn) 
    {
        $this->client->attach($conn);
         echo "New connection! ({$conn->resourceId})\n";
    }

    /**
     * @return void
     */
    public function onMessage(ConnectionInterface $from, $msg) 
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
    }

    /**
     * @return void
     */
    public function onClose(ConnectionInterface $conn) 
    {
         // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    /**
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $e) 
    {
         echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

}