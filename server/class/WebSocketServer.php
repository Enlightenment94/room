<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once("EnlRsa.php");

class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $connArr;

    public function __construct(){
        $this->clients = new \SplObjectStorage;
        $this->connArr = array();
    }

    public function getClientsIds() {
        $response = "";
        $clientsIds = array();
        $response = "<tb>";
        $response .= "<instance>clients</instance>";
        foreach ($this->clients as $client) {
            $response .= "<id>" . $client->clientId . "</id>";
        }
        $response .= "</tb>";
        return $response;
    }

    private function getConnById($clientId) {
        foreach ($this->connArr as $conn) {
            if($conn[1] == $clientId){
                return $conn[0];
            }
        }
        return null;
    }

    public function sendMessageToClient($clientId, $data){
        $conn = $this->getConnById($clientId);
        if ($conn !== null) {
            $conn->send($data);
        } else {
            echo "Conn not found: $clientId\n";
            //die();
        }
    }


    public function onOpen(ConnectionInterface $conn){        
        $password = 'backend219';
        $uri = $conn->httpRequest->getUri();
        parse_str($uri->getQuery(), $params);
        $clientPassword = $params['room'];

        if ($clientPassword !== $password) {
            $conn->close();
            return;
        }

        $clientId = uniqid('client_');
        $conn->clientId = $clientId;
        $this->clients->attach($conn);
        $response = "<tb>";
        $response .= "<instance>you</instance>";
        $response .= "<id>" . $clientId . "</id>";
        $response .= "</tb>";
        $conn->send($response);
        array_push($this->connArr, array($conn, $clientId));
    }

    public function onClose(ConnectionInterface $conn) {
        if ($this->clients->contains($conn)) {
            $this->clients->detach($conn);
        }

        echo "Connection {$conn->resourceId} has disconnected.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        
        echo $msg . "</br>";
        $parsed = array();
        $xml = simplexml_load_string($msg);
        foreach ($xml->children() as $element) {
            //echo $element->getName() . ": " . $element . "<br>";
            array_push($parsed, $element);
            foreach ($element->attributes() as $name => $value) {
                //echo "$name: $value<br>";
            }
        }

        $instance  = $parsed['0'];
        $clientId  = $parsed['1'];
        $parsedMsg = $parsed['2'];

        if(count($parsed) == 4){
            $myId = $parsed['3'];
        }

        switch ($instance) {
            case "pls_key":
                $pls  = "<tb>";
                $pls .= "<instance>pls</instance>";
                $pls .= "<id>". $clientId ."</id>";
                $pls .= "<msg>". "" ."</msg>";
                $pls .= "<mid>". $myId ."</mid>";
                $pls .= "</tb>";
                $this->sendMessageToClient($clientId, $pls);
                break;

            case "key":
                $this->sendMessageToClient($myId, $msg);
                break;

            case "clients":
                $clients = $this->getClientsIds();
                $from->send($clients);
                break;

            case "send":
                $msgXml = "<tb>";
                $msgXml .= "<instance>" . "msg" . "</instance>";
                $msgXml .= "<id>" . $myId . "</id>";
                $msgXml .= "<msg>" . $parsedMsg . "</msg>";
                $msgXml .= "</tb>";
                $this->sendMessageToClient($clientId, $msgXml);

                $response = "<tb>";
                $response .= "<instance>send</instance>";
                $response .= "</tb>";
                $from->send($response);
                break;

            default:
                $response = "<tb>";
                $response .= "<instance>default</instance>";
                $response .= "</tb>";
                $from->send($response);
                break;
        }
    }
}