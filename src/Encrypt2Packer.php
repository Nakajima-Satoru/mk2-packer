<?php

/**
 * 
 * [mk2 standard packer]
 * Encrypt2Packer
 * 
 * Data encryption/decryption components.
 * For public key cryptography and certificate issuance and authentication.
 * 
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Import;
Import::Packer("Encrypt");

class Encrypt2Packer extends EncryptPacker{

	public $digestAlg="sha256";
	public $privateKeyBits=4096;
	public $privateKeyType=OPENSSL_KEYTYPE_RSA;

	/**
	 * encodePublicKey
	 */
	public function encodePublicKey($input,$publicKey,$option=[]){

		if(is_array($input)){
			$input=json_encode($input);
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
	 * encodePublicKeyAuto
	 */
	public function encodePublicKeyAuto($input,$option=[]){

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

		if(is_array(json_decode($decrypted,true))){
			$decrypted=json_decode($decrypted,true);
		}

		return $decrypted;

	}

	/**
	 * encodePrivateKey
	 */
	public function encodePrivateKey($input,$privateKey,$option=[]){

		if(is_array($input)){
			$input=json_encode($input);
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
	 * encodePrivateKeyAuto
	 */
	public function encodePrivateKeyAuto($input,$option=[]){

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

		if(is_array(json_decode($decrypted,true))){
			$decrypted=json_decode($decrypted,true);
		}

		return $decrypted;

	}

	/**
	 * makeKeys
	 */
	public function makeKeys($option=[]){

		$option=$this->_setOption($option);

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

	/**
	 * makeCsr
	 */
	public function makeCsr($dn,$privateKey,$option=[]){

		// make csr
		$csr=openssl_csr_new($dn, $privateKey);

		if(!empty($option["resourceOutput"])){
			return $csr;
		}
		else
		{
			// convert text for csr
			openssl_csr_export($csr, $csrout);
			return $csrout;
		}
	}

	/**
	 * makeCsrAuto
	 */
	public function makeCsrAuto($dn,$option=[]){

		$makeKeys=$this->makeKeys($option);

		$csr=$this->makeCsr($dn,$makeKeys["privateKey"],$option);

		return [
			"csr"=>$csr,
			"publicKey"=>$makeKeys["publicKey"],
			"privateKey"=>$makeKeys["privateKey"],
		];

	}

	/**
	 * makeCsrSign
	 */
	public function makeCsrSign($dn,$privateKey,$option=[]){

		$option["resourceOutput"]=true;

		// make csr 
		$csr=$this->makeCsr($dn,$privateKey,$option);

		$day=365;
		if(!empty($option["day"])){
			$day=$option["day"];
		}

		if(!empty($option["cacert"])){

			$usercert=openssl_csr_sign($csr,$option["cacert"],$privateKey,$day,$option);

		}
		else
		{

			$usercert=openssl_csr_sign($csr,null,$privateKey,$day,$option);

		}

		openssl_x509_export($usercert, $certout);

		return $certout;
	
	}

	/**
	 * makeCsrSignAuto
	 */
	public function makeCsrSignAuto($dn,$option=[]){

		$makeKeys=$this->makeKeys($option);

		$cert=$this->makeCsrSign($dn,$makeKeys["privateKey"],$option);

		return [
			"cert"=>$cert,
			"publicKey"=>$makeKeys["publicKey"],
			"privateKey"=>$makeKeys["privateKey"],
		];
	}
	
	/**
	 * (private) _setOption
	 */
	private function _setOption($option){

		$option=parent::_setOption($option);

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