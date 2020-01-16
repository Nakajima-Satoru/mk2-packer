<?php

/*

- mk2 standard packer -

AuthPacker

A database table authentication component.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

Import::Packer("AuthBase");

class AuthPacker extends AuthBasePacker{

	public $dbTable=[
		"table"=>"User",
		"username"=>"username",
		"password"=>"password",
		"addRule"=>null,
		"fields"=>null,
		"hash"=>[
			"algo"=>"sha256",
			"salt"=>"9f0a90r9eigajifoapeijfaig",
			"stretch"=>4,
		],
	];
	
	# login

	public function login($post,$forceLoginLimitter=false){

		if(empty($this->dbTable["table"])){
			return false;
		}
		if(empty($this->dbTable["username"])){
			$this->dbTable["username"]="username";
		}
		if(empty($this->dbTable["password"])){
			$this->dbTable["password"]="password";
		}
		if(empty($post[$this->dbTable["username"]])){
			return false;
		}
		if(empty($post[$this->dbTable["password"]])){
			return false;
		}

		$this->setTable([$this->dbTable["table"]]);

		$obj=$this->Table->{$this->dbTable["table"]};

		$params=[
			"type"=>"first",
		];

		$params["where"]=[
			[$this->dbTable["username"],$post[$this->dbTable["username"]]],
		];

		// if force Login limitter is "false"...
		if(empty($forceLoginLimitter)){
			$pwHash=$this->getPasswordHash($post[$this->dbTable["password"]]);
			$params["where"][]=[$this->dbTable["password"],$pwHash];
		}

		if(is_array($this->dbTable["addRule"])){
			foreach($this->dbTable["addRule"] as $ar_){
				$params["where"][]=$ar_;
			}
		}

		if(!empty($this->dbTable["fields"])){
			$params["fields"]=$this->dbTable["fields"];
		}
		
		$check=$obj->select($params);

		unset($check->password);

		if(!$check){
			return false;
		}
		else
		{

			$this->refresh($check);
			return true;

		}

	}

	# getPasswordHash

	public function getPasswordHash($password){

		$algo="sha256";
		$stretch=2;
		$salt="9922001920";

		if(!empty($this->dbTable["hash"]["algo"])){
			$algo=$this->dbTable["hash"]["algo"];
		}
		if(!empty($this->dbTable["hash"]["stretch"])){
			$stretch=$this->dbTable["hash"]["stretch"];
		}
		if(!empty($this->dbTable["hash"]["salt"])){
			$salt=$this->dbTable["hash"]["salt"];
		}

		$hash=$password;
		for($v1=0;$v1<$stretch;$v1++){
			$hash=hash($algo,$hash);
		}

		return $hash;
	}

	# forceLogin

	public function forceLogin($username){

		return $this->login([$this->dbTable["username"]=>$username]);
		
	}

}