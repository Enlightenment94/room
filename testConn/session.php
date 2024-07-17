<?php

//php encrypt key by jsencrypt key and send AES key

require_once( __DIR__ . "/server/class/EnlAes.php");
require_once( __DIR__ . "/server/class/EnlRsa.php");

if( isset($_GET['pub']) ){
    $pub = $_GET['pub'];
}