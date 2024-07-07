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
    $publicKey = $_GET['pk'];    
    $prvKey = $_GET['prk'];
    $session = generateRandomString();
    $filePath = __DIR__. "/sessions/" . $session;
    $aesKey = EnlAes::generateKey();
    file_put_contents($filePath, $aesKey);

    $enlRsa = new EnlRsa();    
    $aesKeyEncryptedByRsa = $enlRsa->rsaEncrypt($aesKey, $publicKey);
    $aesKeyDecryptedByRsa = $enlRsa->rsaDecrypt($aesKeyEncryptedByRsa, $prvKey);
    $encoded = base64_encode($aesKeyEncryptedByRsa);


    echo "<response>" .
         "<session>$session</session>" .
         //"<field>$aesKeyEncryptedByRsa</field>" .
         "<field>$encoded</field>" .         
         "<dec>$aesKeyDecryptedByRsa</dec>" .
         "<plus>+</plus>".
         "</response>";
}
