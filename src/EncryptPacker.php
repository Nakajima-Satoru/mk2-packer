<?php

/**
 * 
 * [mk2 standard packer]
 * EncryptPacker
 * 
 * Data encryption/decryption components.
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Packer;

class EncryptPacker extends Packer{

	public $encType="aes-256-cbc";
	public $hashNumber="yerougaf09rgfar56afa4fa1faea4f1dd5d596a8r4f";
	public $password="J0aarogi40495aaajdoe22z5d9a8raf4ar1awf6a5dar1e2gng";

	public $digestAlg="sha256";
	public $privateKeyBits=4096;
	public $privateKeyType=OPENSSL_KEYTYPE_RSA;


	/**
	 * enclist
	 */
	public function enclist(){
		$method_list = openssl_get_cipher_methods();
		return $method_list;
	}

	/**
	 * encode
	 */
	public function encode($input,$option=[]){

		if(is_array($input)){
			$input=jsonEnc($input);
		}

		$option=$this->_setOption($option);

		$ivLength = openssl_cipher_iv_length($option["encType"]);
		$iv = substr($option["hashNumber"],1,$ivLength);
		$options = 0;

		//encodeing...
		$encrypted=openssl_encrypt($input, $option["encType"], $option["password"], $options, $iv);

		if(!empty($option["binaryOutput"])){
			$encrypted=base64_decode($encrypted);
		}

		return $encrypted;
	}

	/**
	 * decode
	 */
	public function decode($input,$option=[]){

		$option=$this->_setOption($option);

		$ivLength = openssl_cipher_iv_length($option["encType"]);
		$iv = substr($option["hashNumber"],1,$ivLength);
		$options=0;

		if(!empty($option["binaryOutput"])){
			$input=base64_encode($input);
		}

		//decode
		$decrypted=openssl_decrypt($input, $option["encType"], $option["password"], $options, $iv);

		if(is_array(jsonDec($decrypted))){
			$output=jsonDec($decrypted);
		}
		else
		{
			$output=$decrypted;
		}

		return $output;
	}

	/**
	 * encodePublicKey
	 */
	public function encodePublicKey($input,$publicKey,$option=[]){

		if(is_array($input)){
			$input=jsonEnc($input);
		}

		$option=$this->_setOption($option);

		// encrypt $input
		openssl_public_encrypt($input, $encrypted, $publicKey);

		if(empty($option["binaryOutput"])){
			$encrypted=base64_encode($encrypted);
		}

		return $encrypted;

	}

	/**
	 * encodePublicKeyMake
	 */
	public function encodePublicKeyMake($input,$option=[]){

		$option=$this->_setOption($option);

		// make public/private key
		$sslKeys=$this->makeKeys($option);

		// public key encreypt
		$encrypted=$this->encodePublicKey($input,$sslKeys["publicKey"],$option);

		return [
			"encrypted"=>$encrypted,
			"publicKey"=>$sslKeys["publicKey"],
			"privateKey"=>$sslKeys["privateKey"],
		];

	}

	/**
	 * decodePublicKey
	 */
	public function decodePublicKey($encrypted,$privateKey,$option=null){

		$option=$this->_setOption($option);

		if(empty($option["binaryOutput"])){
			$encrypted=base64_decode($encrypted);
		}

		openssl_private_decrypt($encrypted, $decrypted, $privateKey);

		if(is_array(jsonDec($decrypted))){
			$decrypted=jsonDec($decrypted);
		}

		return $decrypted;

	}

	/**
	 * encodePrivateKey
	 */
	public function encodePrivateKey($input,$privateKey,$option=[]){

		if(is_array($input)){
			$input=jsonEnc($input);
		}

		$option=$this->_setOption($option);

		// encrypt $input
		openssl_private_encrypt($input, $encrypted, $privateKey);

		if(empty($option["binaryOutput"])){
			$encrypted=base64_encode($encrypted);
		}

		return $encrypted;

	}

	/**
	 * encodePrivateKeyMake
	 */
	public function encodePrivateKeyMake($input,$option=[]){

		$option=$this->_setOption($option);

		// make public/private key
		$sslKeys=$this->makeKeys($option);

		// private key encreypt
		$encrypted=$this->encodePrivateKey($input,$sslKeys["privateKey"],$option);

		return [
			"encrypted"=>$encrypted,
			"publicKey"=>$sslKeys["publicKey"],
			"privateKey"=>$sslKeys["privateKey"],
		];

	}

	/**
	 * decodePrivateKey
	 */
	public function decodePrivateKey($encrypted,$publicKey,$option=[]){

		$option=$this->_setOption($option);

		if(empty($option["binaryOutput"])){
			$encrypted=base64_decode($encrypted);
		}

		openssl_public_decrypt($encrypted, $decrypted, $publicKey);

		if(is_array(jsonDec($decrypted))){
			$decrypted=jsonDec($decrypted);
		}

		return $decrypted;

	}

	/**
	 * makeKeys
	 */
	public function makeKeys($option){

		$config=[
			"digest_alg" => $option["digestAlg"],
			"private_key_bits" => $option["privateKeyBits"],
			"private_key_type" => $option["privateKeyType"],
		];

		// Create the private and public key
		$res = openssl_pkey_new($config);

		// output privKey
		openssl_pkey_export($res, $privateKey);

		// output the public key
		$publicKey=openssl_pkey_get_details($res);
		$publicKey=$publicKey["key"];

		return [
			"privateKey"=>$privateKey,
			"publicKey"=>$publicKey,
		];

	}

	private function _setOption($option){

		if(empty($option["encType"])){
			$option["encType"]=$this->encType;
		}

		if(empty($option["hashNumber"])){
			$option["hashNumber"]=$this->hashNumber;
		}

		if(empty($option["password"])){
			$option["password"]=$this->password;
		}

		if(empty($option["digestAlg"])){
			$option["digestAlg"]=$this->digestAlg;
		}

		if(empty($option["privateKeyBits"])){
			$option["privateKeyBits"]=$this->privateKeyBits;
		}

		if(empty($option["privateKeyType"])){
			$option["privateKeyType"]=$this->privateKeyType;
		}

		return $option;

	}

}