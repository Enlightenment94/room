<?php

ini_set("display_errors", true);

$secretPath =  __DIR__ . "/secretDir/public.key";
$publicKey = file_get_contents($secretPath);
echo $publicKey;
