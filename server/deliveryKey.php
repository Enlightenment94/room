<?php

//ini_set("display_errors", true);
header('Content-Type: text/xml; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once( __DIR__ . "/class/EnlAes.php" );
require_once( __DIR__ . "/class/EnlRsa.php" );

function generateRandomString($length = 32, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}

if( isset($_GET['pk']) ){
    $publicKey = base64_decode($_GET['pk']); 
    $session = generateRandomString();
    $filePath = __DIR__. "/sessions/" . $session;
    $aesKey = EnlAes::generateKey();
    file_put_contents($filePath, $aesKey);

    $enlRsa = new EnlRsa();    
    $aesKeyEncryptByRsa = $enlRsa->rsaEncryptPadding($aesKey, $publicKey, "base");
    //$aesKeyEncryptByRsa = $enlRsa->rsaEncrypt($aesKey, $publicKey);

    $response =  "<response>" .
         "<session>$session</session>" .
         "<enc>$aesKeyEncryptByRsa</enc>" .
         "</response>";

    echo $response;
    file_put_contents("here", $response);
}
