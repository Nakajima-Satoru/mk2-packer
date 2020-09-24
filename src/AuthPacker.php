<?php

/**
 * 
 * [mk2 standard packer]
 * AuthPacker
 * 
 * A database table authentication component.
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Import;

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
	
	/*
	public $bruteForceLimit=[
		"retry"=>20,
		"interval"=>14400,
	];
	*/

	public $usePackerClass=[
		"Session"=>"Session",
		"Cache"=>"Cache",
	];

	
	public function __construct($option){
		parent::__construct($option);

		$this->Loading->Packer([
			$this->getUsePackerClass("Cache"),
		]);

		$this->Packer->{$this->getUsePackerClass("Cache")}->name="authbruteforceblock.".$this->authName;

	}

	/**
	 * login
	 */
	public function login($post,$forceLoginLimitter=false){

		if(empty($this->dbTable["table"])){
			return false;
		}
		if(empty($this->dbTable["username"])){
			$this->dbTable["username"]=["username"];
		}
		if(!is_array($this->dbTable["username"])){
			$this->dbTable["username"]=[$this->dbTable["username"]];
		}
		if(empty($this->dbTable["password"])){
			$this->dbTable["password"]="password";
		}

		$juge1=false;
		foreach($this->dbTable["username"] as $u_){
			if(!empty($post[$u_])){
				$juge1=true;
				break;
			}
		}

		if(!$juge1){
			return false;
		}

		if(empty($post[$this->dbTable["password"]])){
			return false;
		}


		// bruteForceLimitCheck
		if(!$this->bruteForceLimitCheck()){
			return false;
		}

		$this->Loading->Table([$this->dbTable["table"]]);

		$obj=$this->Table->{$this->dbTable["table"]};

		$params=[
			"type"=>"first",
		];

		$params["where"]=[];
		$userValue="";
		foreach($this->dbTable["username"] as $ind=>$u_){
			if(!empty($post[$u_])){
				$userValue=$post[$u_];
			}
		}

		foreach($this->dbTable["username"] as $ind=>$u_){
			$params["where"][]=[$u_,$userValue,"OR",2];
		}

		// if force Login limitter is "false"...
		if(empty($forceLoginLimitter)){
			$pwHash=$this->getPasswordHash($post[$this->dbTable["password"]]);
			$params["where"][]=[$this->dbTable["password"],$pwHash];
		}

		if(!empty($this->dbTable["addRule"])){
			if(is_array($this->dbTable["addRule"])){
				foreach($this->dbTable["addRule"] as $ar_){
					$params["where"][]=$ar_;
				}
			}
		}

		if(!empty($this->dbTable["fields"])){
			$params["fields"]=$this->dbTable["fields"];
		}

		$check=$obj->select($params);

		unset($check->password);

		if(!$check){
			$this->bruteForceLimitAddCount();
			return false;
		}
		else
		{

			$this->refresh($check);
			return true;

		}

	}

	/**
	 * getPasswordHash
	 */
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

	/**
	 * forceLogin
	 */
	public function forceLogin($username){

		return $this->login([$this->dbTable["username"]=>$username]);
		
	}

		
	private function getUsePackerClass($name){

		$buff=$this->usePackerClass[$name];

		if(is_array($buff)){
			return key($buff);
		}
		else{
			return $buff;
		}

	}

	public function bruteForceCheck(){
		return $this->bruteForceLimitCheck();
	}

	public function bruteForceList(){

		$getList=$this->Packer->{$this->getUsePackerClass("Cache")}->read();
		if(!$getList){ return null; }

		$output=[];
		foreach($getList as $ip=>$g_){
			$g_=json_decode($g_,true);
			if(!empty($g_["blocked"])){
				$output[$ip]=$g_["releaseDate"];
			}
		}
		return $output;

	}

	public function bruteForceRelease($ipAddress){
		$getList=$this->Packer->{$this->getUsePackerClass("Cache")}->delete($ipAddress);
	}

	private function bruteForceLimitCheck(){

		if(
			empty($this->bruteForceLimit["retry"]) ||
			empty($this->bruteForceLimit["interval"])
		){
			return true;
		}

		$remoteIp=\mk2\core\Request::$params["option"]["remote"];
		$readCache=$this->Packer->{$this->getUsePackerClass("Cache")}->read($remoteIp);
		$readCache=json_decode($readCache,true);

		if(!$readCache){
			return true;
		}

		if(!empty($readCache["blocked"])){	
			$nowDate=date_format(date_create("now"),"YmdHis");

			if($nowDate>$readCache["releaseDate"]){			
				$this->Packer->{$this->getUsePackerClass("Cache")}->delete($remoteIp);
				return true;
			}
			return false;

		}
		else{


			if($readCache["count"]<($this->bruteForceLimit["retry"]-1)){
				return true;
			}

			$readCache=[
				"blocked"=>true,
				"releaseDate"=>date_format(date_create("+".$this->bruteForceLimit["interval"]." seconds"),"YmdHis"),
			];
			$readCache=json_encode($readCache);
			$this->Packer->{$this->getUsePackerClass("Cache")}->write($remoteIp,$readCache);

			return false;
		}

	}
	private function bruteForceLimitAddCount(){

		if(empty($this->bruteForceLimit)){
			return true;
		}
		$remoteIp=\mk2\core\Request::$params["option"]["remote"];
		$readCache=$this->Packer->{$this->getUsePackerClass("Cache")}->read($remoteIp);

		$readCache=json_decode($readCache,true);

		if(empty($readCache)){
			$readCache=[
				"count"=>0,
			];
		}

		$readCache["count"]++;
		$readCache=json_encode($readCache);

		$this->Packer->{$this->getUsePackerClass("Cache")}->write($remoteIp,$readCache);

	}
}
