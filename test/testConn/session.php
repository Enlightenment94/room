<?php

//php encrypt key by jsencrypt key and send AES key

require_once( __DIR__ . "/../server/class/EnlAes.php");
require_once( __DIR__ . "/../server/class/EnlRsa.php");


//https://test.room.pl.lndo.site/testConn/session.php?srv=t
if( isset($_GET['srv']) ){
    $pub = $_GET['srv'];
    $enlRsa = new EnlRsa();
    $enlRsa->genPairKey("0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef", "key", "key.pub");
}

if( isset($_GET['pub']) ){
    $publicKeyJs = $_GET['pub'];
    $enlAes = new EnlAes();
    $enlRsa = new EnlRsa();

    $sessionKey = "RoomChat";
    $aesKey = $enlAes->generateKey();    

    $aesKeyEncrypted = $enlRsa->rsaEncrypt($aesKey, $publicKeyJs);

    file_put_contents("sessionKey", $sessionKey);    
    echo $aesKeyEncrypted;
    die();
}

if( isset($_GET['pub_srv']) ){
    echo file_get_contents("key.pub");
}

if( isset($_GET['token']) ){
    $enlAes = new EnlAes();
    $enlRsa = new EnlRsa();
    $token = $_GET['token'];
    $aesKey = $enlRsa->rsaDecrypt($token, $prv);
    echo $token;
}