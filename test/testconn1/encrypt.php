<?php

function decryptAES($encrypted, $keyHex) {
    // Convert the hexadecimal key to binary
    $key = hex2bin($keyHex);

    // Decode the Base64 encoded string
    $decodedData = base64_decode($encrypted);

    // Extract the first 8 bytes to check for "Salted__" (only if using password)
    $saltHeader = substr($decodedData, 0, 8);
    if ($saltHeader === "Salted__") {
        // If there's a salt, extract it (though this part may vary if the key was directly used)
        $salt = substr($decodedData, 8, 8);
        // Adjust data offsets accordingly
        $decodedData = substr($decodedData, 16);  // Skip the salt header and actual salt
    }

    // Extract the IV (first 16 bytes for AES-256-CBC)
    $ivSize = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($decodedData, 0, $ivSize);
    $ciphertext = substr($decodedData, $ivSize);

    // Debug output
    echo "IV Length: " . strlen($iv) . "\n";
    echo "Ciphertext Length: " . strlen($ciphertext) . "\n";

    // Decrypt the ciphertext
    $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    if ($decrypted === false) {
        echo "Decryption failed: " . openssl_error_string() . "\n";
    }

    return $decrypted;
}

// Provided encrypted message and key
$encryptedMessage = 'U2FsdGVkX1/UcseW86HH5Kh+GoJgMQZ9cGL65qIfYV4=';
$keyHex = '1d657dd9f65cf4a7c6a914be4d21790d832376604149c647c17be2904c98c2b7';

// Decrypt the message
$decryptedMessage = decryptAES($encryptedMessage, $keyHex);

echo 'Decrypted message: ' . $decryptedMessage . PHP_EOL;

?>