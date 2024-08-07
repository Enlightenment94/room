<?php

require_once(__DIR__ . "/EnlAes.php");
define("PUBLIC_KEY_PATH", __DIR__ . "/../secretDir/" . 'public.key');
define("PRIVATE_KEY_PATH", __DIR__ . "/../secretDir/" . 'private.key');

class EnlRsa{
	public function genPairKey($sp = "", $privateKeyPath = "", $publicKeyPath = ""){
		$config = array(
		  "digest_alg" => "sha512",
		  "private_key_bits" => 2048,
		  "private_key_type" => OPENSSL_KEYTYPE_RSA,
		);
		$res = openssl_pkey_new($config);

		$privateKey = "";
		openssl_pkey_export($res, $privateKey);
		$publicKey = openssl_pkey_get_details($res)['key'];

		$plainText = "Hello world";
		openssl_public_encrypt($plainText, $encryptedText, $publicKey);
		openssl_private_decrypt($encryptedText, $decryptedText, $privateKey);


		if($privateKeyPath == ""){
			$path = PRIVATE_KEY_PATH;
		}else{
			$path = $privateKeyPath;
		}

		if($privateKeyPath == ""){
			$privateKeyPath = PRIVATE_KEY_PATH;
		}

		if($publicKeyPath == ""){
			$publicKeyPath = PUBLIC_KEY_PATH;
		}

		file_put_contents($path, '');
		file_put_contents($path, $privateKey);

		$enlAes = new EnlAes();
		if($sp != ""){
			$enlAes->setSp($sp);
		}
		$encryptedRsaPrivateKey = $enlAes->encryptKey($path, $enlAes->sp);

		/*
		echo "Klucz publiczny:\n$publicKey\n\n";
		echo "</br></br>";
		echo "Klucz prywatny:\n$privateKey\n\n";
		echo "</br></br>";
		echo "Tekst do zaszyfrowania:\n$plainText\n\n</br>";
		echo "Tekst po zaszyfrowaniu:\n$encryptedText\n\n</br>";
		echo "Tekst po odszyfrowaniu:\n$decryptedText\n\n</br></br>";
		echo "Klucz zaszyfrowany: </br>";
		echo "<br></br>";
		echo "Klucz odszyfrowany: </br>";
		echo $enlAes->decryptKey("private.key", $sp);
		*/

		$decryptedRsaPrivateKey = $enlAes->decryptKey($path, $enlAes->sp);
		$this->renderEncryptionResults($publicKey, $privateKey, $encryptedRsaPrivateKey, $decryptedRsaPrivateKey, $plainText, $encryptedText, $decryptedText, $enlAes->sp);

		file_put_contents($publicKeyPath, '');
		$fp = fopen($publicKeyPath, "w");
		fwrite($fp, $publicKey);
		fclose($fp);
	}

	public function rsaDecrypt($data, $privateKey) {
	    //$decodedData = base64_decode($data);
		$decodedData = $data;
	    $plainText = false;
	    if (openssl_private_decrypt($decodedData, $plainText, $privateKey, OPENSSL_PKCS1_PADDING)) {
	        return $plainText;
	    } else {
	        return "err_decrypt";
	    }
	}

	public function rsaEncrypt($plainText, $publicKey = "", $base = ""){
		$encryptedText = "";
		if($publicKey == ""){
			$publicKey = file_get_contents(PUBLIC_KEY_PATH);
		}

		//$publicKey = openssl_pkey_get_public($publicKey);

		openssl_public_encrypt($plainText, $encryptedText, $publicKey);

		if($base == "base"){
			return base64_encode($encryptedText);
		}else{
			return $encryptedText;
		}
	}

	public function rsaEncryptPadding($plainText, $publicKey = "", $base = ""){
		$encryptedText = "";
		if($publicKey == ""){
			$publicKey = file_get_contents(PUBLIC_KEY_PATH);
		}

		//$publicKey = openssl_pkey_get_public($publicKey);

		openssl_public_encrypt($plainText, $encryptedText, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);

		if($base == "base"){
			return base64_encode($encryptedText);
		}else{
			return $encryptedText;
		}
	}
	
	private function renderEncryptionResults($publicKey, $privateKey, $encryptedRsaPrivateKey, $decrytpedRsaPrivateKey, $plainText, $encryptedText, $decryptedText, $sp) {
		echo '
		<!DOCTYPE html>
		<html lang="pl">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Wyniki Szyfrowania</title>
			<style>
				body {
					font-family: Arial, sans-serif;
					margin: 20px;
					padding: 20px;
					background-color: #f4f4f9;
				}
				.container {
					max-width: 800px;
					margin: auto;
					padding: 20px;
					background: #fff;
					box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
				}
				h2 {
					color: #333;
					border-bottom: 2px solid #eee;
					padding-bottom: 10px;
				}
				pre {
					background: #f8f8f8;
					border: 1px solid #ddd;
					padding: 10px;
					overflow-x: auto;
				}
				.info {
					background: #e7f3ff;
					padding: 10px;
					border: 1px solid #c7e0ff;
					margin-bottom: 20px;
				}
			</style>
		</head>
		<body>
			<div class="container">
				<h2>Wyniki Szyfrowania</h2>
	
				<div class="info">
					<strong>Klucz publiczny:</strong>
					<pre>' . htmlspecialchars($publicKey) . '</pre>
				</div>
	
				<div class="info">
					<strong>Klucz prywatny:</strong>
					<pre>' . htmlspecialchars($privateKey) . '</pre>
				</div>

				<div class="info">
					<strong>AES sp (key):</strong>
					<pre>' . htmlspecialchars($sp) . '</pre>
				</div>

				<div class="info">
					<strong>Klucz zaszyfrowany przez AES:</strong>
					<pre>' . htmlspecialchars($encryptedRsaPrivateKey) . '</pre>
				</div>

				<div class="info">
					<strong>Klucz odszyfrowany przez AES:</strong>
					<pre>' . htmlspecialchars($decrytpedRsaPrivateKey) . '</pre>
				</div>
	
				<div class="info">
					<strong>Tekst do zaszyfrowania RSA:</strong>
					<pre>' . htmlspecialchars($plainText) . '</pre>
				</div>
	
				<div class="info">
					<strong>Tekst po zaszyfrowaniu RSA:</strong>
					<pre>' . htmlspecialchars($encryptedText) . '</pre>
				</div>
	
				<div class="info">
					<strong>Tekst po odszyfrowaniu RSA:</strong>
					<pre>' . htmlspecialchars($decryptedText) . '</pre>
				</div>
			</div>
		</body>
		</html>';
	}
}
