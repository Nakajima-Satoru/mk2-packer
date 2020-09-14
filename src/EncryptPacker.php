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
			$input=json_encode($input);
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

		if(is_array(json_decode($decrypted,true))){
			$output=json_decode($decrypted,true);
		}
		else
		{
			$output=$decrypted;
		}

		return $output;
	}

	/**
	 * (private) _setOption
	 */
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

		return $option;

	}

}