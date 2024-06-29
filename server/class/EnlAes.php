<?php

class EnlAes{

	public $sp;

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

}
