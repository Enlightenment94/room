<?php

/*
$config = array(
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);
$res = openssl_pkey_new($config);

// Pobieranie klucza publicznego
openssl_pkey_export($res, $privateKey);
$publicKey = openssl_pkey_get_details($res)["key"];*/



openssl_public_encrypt($dataToEncrypt, $encrypted, $publicKey);
openssl_private_decrypt($encrypted, $decrypted, $privateKey);

echo $decrypted;