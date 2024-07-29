<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . "/class/WebSocketServer.php");

use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;


$port = 8080;
$pidFile = __DIR__ . '/server.pid';

function isProcessRunning($pid) {
    return posix_kill($pid, 0);
}

function startServer($port, $pidFile) {
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new WebSocketServer()
            )
        ),
        $port
    );

    file_put_contents($pidFile, getmypid());

    echo "Server running on port $port\n";
    $server->run();
}

// Check if the PID file exists
if (file_exists($pidFile)) {
    $pid = (int)file_get_contents($pidFile);

    if($pid != 0){
        if (isProcessRunning($pid)) {
            echo "Stopping existing process with PID $pid...\n";
            posix_kill($pid, SIGTERM); // Gracefully terminate the process
            sleep(1); // Allow some time for the process to terminate
        }

        unlink($pidFile); 
    }
}

startServer($port, $pidFile);
?>