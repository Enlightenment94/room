<?php

class EnlAes{

	public $sp;

	public static function generateKey($length = 256) {
        if (!in_array($length, [128, 192, 256])) {
            throw new InvalidArgumentException('Invalid key length. Allowed lengths are 128, 192, or 256 bits.');
        }
        $bytes = $length / 8;
        $key = random_bytes($bytes);
        return bin2hex($key);
    }

	public function __construct($sp = ""){
		if($sp == ""){
			$sp = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';	
		}
	}

	public function setSp($sp){
		$this->sp = $sp;
	}

	public  function encryptKey($inputFile, $key) {
		$iv = random_bytes(16); 
		$method = 'AES-256-CBC';
		$encryptedFile = $inputFile;
	
		$inputData = file_get_contents($inputFile);
	
		$encryptedData = openssl_encrypt($inputData, $method, $key, OPENSSL_RAW_DATA, $iv);
	
		$outputData = $iv . $encryptedData;
	
		file_put_contents($encryptedFile, $outputData);
	
		return $encryptedFile;
	}
	
	public function decryptKey($encryptedFile, $key) {
		$method = 'AES-256-CBC';
	
		$encryptedData = file_get_contents($encryptedFile);
	
		$ivSize = openssl_cipher_iv_length($method);
		$iv = substr($encryptedData, 0, $ivSize);
	
		$encryptedContent = substr($encryptedData, $ivSize);
	
		$data = openssl_decrypt($encryptedContent, $method, $key, OPENSSL_RAW_DATA, $iv);
	
		return $data;
	}

	public function decryptString($encryptedString, $key) {
		$method = 'AES-256-CBC';
	
		$encryptedData = $encryptedString;
	
		$ivSize = openssl_cipher_iv_length($method);
		$iv = substr($encryptedData, 0, $ivSize);
	
		$encryptedContent = substr($encryptedData, $ivSize);
	
		$data = openssl_decrypt($encryptedContent, $method, $key, OPENSSL_RAW_DATA, $iv);
	
		return $data;
	}

	function decryptAES($encryptedBase64, $keyHex) {
		$key = hex2bin($keyHex);
	
		// Decode the Base64 encoded string to get raw data
		$decodedData = base64_decode($encryptedBase64);
	
		// Extract the IV (first 16 bytes for AES-256-CBC)
		$ivSize = openssl_cipher_iv_length('aes-256-cbc');
		$iv = substr($decodedData, 0, $ivSize);
	
		// Extract the ciphertext
		$ciphertext = substr($decodedData, $ivSize);
	
		// Decrypt the ciphertext
		$decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
	
		if ($decrypted === false) {
			echo "Decryption failed: " . openssl_error_string() . "\n";
			return null;
		}
	
		return $decrypted;
	}

}
