<?php
namespace App;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use \Firebase\JWT\JWT;
use Cake\ORM\Query;
use Cake\ORM\Entity;
use Cake\ORM\ResultSet;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\ORM\Locator\TableLocator;

use Cake\Utility\Security;
use Cake\Datasource\ConnectionManager;
use App\Model\Table\MessagesTable;

#cake php data source
class Chat implements MessageComponentInterface {

    protected $clients;
    

    public function __construct(){
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Stores new connection to send messages to later
        
        $querystring = $conn->httpRequest->getUri()->getQuery(); // gets token from Url
        parse_str($querystring,$queryarray);// seperates the token from url
        $id = $queryarray['token']; // Sets variable $id to token
        //echo "New connection ({$queryarray['token']})\n";

        // --- Decodes the Jwt token
        $de = JWT::decode($id,'e45b0b2592fb0023a11f29d5912d39df148ccb530b3f23951ca1688d529cafa6', ['HS256']);
        //echo "new $de->sub";

        //Set the connection id to user_id
        $conn->resourceId = $de->sub;
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        

    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
            
           
        //--- sets the configuration settings to get information from tables
           ConnectionManager::drop('default');
           ConnectionManager::setConfig('default',[
            'className' => 'Cake\Database\Connection',
                'driver' => 'Cake\Database\Driver\Mysql',
                'persistent' => false,
                'host' => 'mysql_steven',
                'username' => 'root',
                'password' => 'tiger',
                'database' => 'docker',
                'encoding' => 'utf8',
                'timezone' => 'UTC'
        ]);
        // call a locator to get table 
        $locator = new TableLocator();
        //gets table needed
        $message = $locator->get('Messages');
        $threadUser = $locator->get('ThreadsUsers');
            

        $data = json_decode($msg,true); // gets the information from the message sent
        $data['from'] = $data['user_id']; // sets from to the user id
        $thread_id = $data['thread_id']; // sets variable thread_id to the thread_id from the data recieved
        $data['msg']  = $data['body']; // sets msg to the body data from data recieved
        $data['created']  = date("M-d-y h:i:s"); // formats the data that was recieved
        

            //---- Calls database table to get information needed --
            $query = $threadUser->find('all')->where(['thread_id' => $thread_id]);
            foreach($query as $user){
                echo "test : $user->user_id\n";
            foreach ($this->clients as $client) {
                
                if ($client->resourceId == $user->user_id) {
                    echo "Client : $client->resourceId\n";
                    $client->send(json_encode($data));
                } 
                
            }
            
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