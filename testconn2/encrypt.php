<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['publicKeyJs'])) {
        $encodedPublicKey = $_GET['publicKeyJs'];
        $publicKeyPem = base64_decode($encodedPublicKey);
        $publicKey = openssl_pkey_get_public($publicKeyPem);

        if ($publicKey) {
            $messageToEncrypt = "This is a secret message";

            if (openssl_public_encrypt($messageToEncrypt, $encryptedData, $publicKey, OPENSSL_PKCS1_OAEP_PADDING)) {
                $encryptedDataBase64 = base64_encode($encryptedData);
                echo $encryptedDataBase64;
            } else {
                echo "Encryption failed.";
            }
        } else {
            echo "Invalid public key.";
        }
    } else {
        echo "No public key provided.";
    }
} else {
    echo "Invalid request method.";
}
?>