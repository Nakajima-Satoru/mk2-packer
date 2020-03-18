<?php

/*

- mk2 standard packer -

CookiePacker

For cookie data management.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class CookiePacker extends Packer{

	public $name="mk2_cookie_fields";
	public $limit=30;
	public $encrypt=[
		"encType"=>"aes-256-cbc",
		"hashNumber"=>"YoduudirogM2oF+fa89r8a749ga84f5a1f6a216ae5r4a98g4g5aaSIriaj(kiof",
		"password"=>"Pforjifpaqrei44490fiiidorijgJ9f6af9899fa5r6ae13ag16d48af111531652",
	];
	public $path="";
	public $domain="";
	public $secure="";

	public $usePackerClass=[
		"Encrypt"=>"Encrypt",
	];

	# __construct

	public function __construct($option){
		parent::__construct($option);

		$this->setPacker([
			$this->usePackerClass["Encrypt"],
		]);

	}

	# write

	public function write($name,$value,$option=array()){

		if(!empty($this->name)){
			$cookie_name=$this->name.$name;
		}
		else
		{
			$cookie_name=$name;
		}

		$value=$this->Packer->Encrypt->encode($value,$this->encrypt);

		if(!empty($option["value_direct"])){
			$value=$value;
		}
		else
		{
			$value=json_encode($value);
		}
	
		if(!empty($option["limit"])){

			if($option["limit"]=="no"){
				$limit=0;
			}
			else
			{
				$limit=time()+$option["limit"];
			}
		}
		else
		{
			$limit=time()+$this->limit;
		}

		if(!empty($option["path"])){
			$path=$option["path"];
		}
		else
		{
			if(!empty($this->path)){
				$path=$this->path;
			}
			else
			{
				$path="/";
			}
		}

		if(!empty($option["domain"])){
			$domain=$option["domain"];
		}
		else
		{
			if(!empty($this->domain)){
				$domain=$this->domain;
			}
		}

		if(!empty($option["secure"])){
			$secure=$option["secure"];
		}
		else
		{
			if(!empty($this->secure)){
				$secure=$this->secure;
			}
		}

		setcookie($cookie_name,$value,@$limit,$path,@$domain,@$secure);
		
	}

	# read

	public function read($name){

		if(!empty($this->name)){
			$cookie_name=$this->name.$name;
		}
		else
		{
			$cookie_name=$name;
		}

		if(!empty($_COOKIE[$cookie_name])){
			$source=@$_COOKIE[$cookie_name];
		}
		else
		{
			return null;
		}

		$source=$this->Packer->Encrypt->decode($source,$this->encrypt);

		return $source;
	}

	# delete

	public function delete($name,$option=array()){


		if(!empty($this->name)){
			$cookie_name=$this->name.$name;
		}
		else
		{
			$cookie_name=$name;
		}

		if(!empty($option["path"])){
			$path=$option["path"];
		}
		else
		{
			if(!empty($this->path)){
				$path=$this->path;
			}
			else
			{
				$path="/";
			}
		}

		if(!empty($option["domain"])){
			$domain=$option["domain"];
		}
		else
		{
			if(!empty($this->domain)){
				$domain=$this->domain;
			}
		}

		if(!empty($option["secure"])){
			$secure=$option["secure"];
		}
		else
		{
			if(!empty($this->secure)){
				$secure=$this->secure;
			}
		}

		setcookie($cookie_name,"",time()-1000,@$path,@$domain,@$secure);

		return;
	}
}
