<?php

/*

- mk2 standard packer -

EncryptPacker

Data encryption/decryption components.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class EncryptPacker extends Packer{

	public $encType="aes-256-cbc";
	public $hashNumber="yerougaf09rgfar56afa4fa1faea4f1dd5d596a8r4f";
	public $password="J0aarogi40495aaajdoe22z5d9a8raf4ar1awf6a5dar1e2gng";

	# enclist

	public function enclist(){
		$method_list = openssl_get_cipher_methods();
		return $method_list;
	}

	# encode

	public function encode($input,$option=array()){

		if(is_array($input)){
			$input=jsonEnc($input);
		}

		if(!empty($option["encType"])){
			$enc_method=$option["encType"];
		}
		else
		{
			$enc_method=$this->encType;
		}

		if(!empty($option["hashNumber"])){
			$hash_number=$option["hashNumber"];
		}
		else
		{
			$hash_number=$this->hashNumber;
		}

		if(!empty($option["password"])){
			$hash_pw=$option["password"];
		}
		else
		{
			$hash_pw=$this->password;
		}

		$ivLength = openssl_cipher_iv_length($enc_method);
		$iv = substr($hash_number,1,$ivLength);
		$options = 0;

		//encodeing...
		$encrypted=openssl_encrypt($input, $enc_method, $hash_pw, $options, $iv);

		return $encrypted;
	}

	# decode

	public function decode($input,$option=array()){

		if(!empty($option["encType"])){
			$enc_method=$option["encType"];
		}
		else
		{
			$enc_method=$this->encType;
		}

		if(!empty($option["hashNumber"])){
			$hash_number=$option["hashNumber"];
		}
		else
		{
			$hash_number=$this->hashNumber;
		}

		if(!empty($option["password"])){
			$hash_pw=$option["password"];
		}
		else
		{
			$hash_pw=$this->password;
		}

		$ivLength = openssl_cipher_iv_length($enc_method);
		$iv = substr($hash_number,1,$ivLength);
		$options=0;

		//decode
		$decrypted=openssl_decrypt($input, $enc_method, $hash_pw, $options, $iv);

		if(is_array(jsonDec($decrypted))){
			$output=jsonDec($decrypted);
		}
		else
		{
			$output=$decrypted;
		}
		return $output;
	}
}

