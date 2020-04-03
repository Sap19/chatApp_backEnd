<?php
namespace App;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Model;
class Chat implements MessageComponentInterface {

    protected $clients;

    public function __construct(){
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Stores new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        $data = json_decode($msg,true);
            $data['from'] = $data['user_id'];
            $data['msg']  = $data['body'];
            $data['created']  = date("M-d-y h:i:s");
         foreach ($this->clients as $client) {
            if ($from == $client) {
                $data['from']  = "Me";
            } else {
                $data['from']  = $data['user_id'];
            }
            $client->send(json_encode($data));
        
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}