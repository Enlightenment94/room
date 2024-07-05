<?php

//ini_set("display_errors", true);
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
    //Zweryfikuj czy to poprawny klucz
    $session = generateRandomString();
    $filePath = __DIR__. "/sessions/" . $session;
    $aesKey = EnlAes::generateKey();
    $enlRsa = new EnlRsa();
    file_put_contents($filePath, $aesKey);
    $aesKeyEncryptedByRsa = $enlRsa->rsaEncrypt($aesKey, $publicKey);

    echo "<response>" .
         "<session>$session</session>" .
         "<field>$aesKeyEncryptedByRsa</field>" .
         "</response>";
}
