<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . "/class/WebSocketServer.php");

use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use React\Socket\SecureServer;
use React\Socket\Server as ReactServer;

$port = 8081;
$pidFile = __DIR__ . '/server.pid';

function isProcessRunning($pid) {
    return posix_kill($pid, 0);
}

function startServer($port, $pidFile) {
    $loop = \React\EventLoop\Factory::create();

    $webSock = new SecureServer(
        new ReactServer("0.0.0.0:$port", $loop),
        $loop,
        [
            'local_cert' => '/home/p599816/ssl/certs/enlightenment_xaa_pl_bd334_7102b_1726838873_38fc951eb23ff00a389fb3c931234f5b.crt', 
            'local_pk' => '/home/p599816/ssl/keys/bd334_7102b_33caa354727bbab35c96cb25fd879806.key',
            'allow_self_signed' => false,    
            'verify_peer' => false           
        ]
    );

    $server = new IoServer(
        new HttpServer(
            new WsServer(
                new WebSocketServer()
            )
        ),
        $webSock,
        $loop
    );

    file_put_contents($pidFile, getmypid());

    echo "Secure server running on port $port\n";
    $server->run();
}

if (file_exists($pidFile)) {
    $pid = (int)file_get_contents($pidFile);

    if($pid != 0){
        if (isProcessRunning($pid)) {
            echo "Stopping existing process with PID $pid...\n";
            posix_kill($pid, SIGTERM); 
            sleep(1);
        }

        unlink($pidFile); 
    }
}

startServer($port, $pidFile);
?>
