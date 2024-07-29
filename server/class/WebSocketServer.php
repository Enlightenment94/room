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

    function search_file($directory, $filename) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === $filename) {
                return $file->getPathname();
            }
        }
        return false;
    }

    public function onOpen(ConnectionInterface $conn){ 
        file_put_contents( __DIR__ ."/debug.log", "Hello");       
        
        $password = 'backend219';
        $uri = $conn->httpRequest->getUri();
        parse_str($uri->getQuery(), $params);
        $clientPassword = $params['room'];
        $session = $params['session'];        

        $session = __DIR__ . "/../sessions/" . $session;        
        $aesKey = file_get_contents($session);

        if( !file_exists($session)){
            $path = $this->search_file( __DIR__ . "/../" , $session);
            file_put_contents( __DIR__ ."/debug.log", $path . " " . $session);
            die("Session File not exist !!!");
        }

        $enlAes = new EnlAes();
        try{
            $decryptedClientPassword = $enlAes->decryptAES($clientPassword, $aesKey);
        }catch(Exception $e){
            file_put_contents( __DIR__ ."/debug.log", $e);
            echo $e;
            die();
        }

        try{
            file_put_contents( __DIR__ ."/debug.log", $decryptedClientPassword . " ============= " . $clientPassword . " -------- " . $session);
        }catch(Exception $e){
            file_put_contents( __DIR__ ."/debug.log", $e);
            echo $e;
        }

        if ($decryptedClientPassword !== $password) {
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

    public function formatXmlData($xml) {
        $dom = new DOMDocument();
        $dom->formatOutput = true; // Enable pretty printing
        $dom->loadXML($xml);
        return $dom->saveXML();
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        
        echo "<style>
            pre {
                background-color: #f4f4f4;
                border: 1px solid #ccc;
                padding: 10px;
                border-radius: 4px;
                white-space: pre-wrap;
                word-break: break-word; 
                width 100%:  
            }
            code {
                display: block;
                font-family: Consolas, 'Courier New', monospace;
                font-size: 14px;
                color: #333;
            }
        </style>";

        echo "<pre><code>";
        echo htmlspecialchars( $this->formatXmlData($msg) ); 
        echo "</code></pre>";

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

        if(isset($parsed['1'])){
            $clientId  = $parsed['1'];
        }else{
            $clientId  = "";
        }
        
        if(isset($parsed['2'])){
            $parsedMsg = $parsed['2'];
        }else{
            $parsedMsg = "";
        }

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