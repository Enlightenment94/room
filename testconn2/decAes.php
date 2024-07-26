<?php

function decryptAES($encrypted, $keyHex) {
    // Convert the hexadecimal key to binary
    $key = hex2bin($keyHex);

    // Decode the Base64 encoded string
    $decodedData = base64_decode($encrypted);

    // Extract the IV (first 16 bytes for AES-256-CBC)
    $ivSize = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($decodedData, 0, $ivSize);

    // Extract the ciphertext
    $ciphertext = substr($decodedData, $ivSize);

    // Decrypt the ciphertext
    $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    return $decrypted;
}

// Provided encrypted message and key
$encryptedMessage = 'U2FsdGVkX1/UcseW86HH5Kh+GoJgMQZ9cGL65qIfYV4=';
$keyHex = '1d657dd9f65cf4a7c6a914be4d21790d832376604149c647c17be2904c98c2b7';

// Decrypt the message
$decryptedMessage = decryptAES($encryptedMessage, $keyHex);

echo 'Decrypted message: ' . $decryptedMessage . PHP_EOL;

?>