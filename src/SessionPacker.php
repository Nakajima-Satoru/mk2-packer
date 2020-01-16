<?php

/*

- mk2 standard packer -

SessionPacker

session data management component.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class SessionPacker extends Packer{

	public $tmpPath="session";
	public $name="mk2sess";
	public $limit=10800;
	public $encrypt=[
		"encType"=>"aes-256-cbc",
		"hashNumber"=>"yodirog+aj23rf9Ad4ji3o7af98areaw52fa6gad",
		"password"=>"msorig0ra90gfa47f80A3er6a485j10ivjorjfaUe2Fr9f0a8f9agafadfa1ga54r5e6a1",
	];

	# __construct

	public function __construct($option){
		parent::__construct($option);

		$this->setPacker([
			"Encrypt",
		]);

		$tmp="../tmp/";
		if(defined('MK2_PATH_TMP')){
			$tmp=MK2_PATH_TMP;
		}

		if(!empty($this->tmpPath)){
			@mkdir($tmp.$this->tmpPath,0775,true);
			@session_save_path($tmp.$this->tmpPath);
		}
		@session_start();
	}

	# session data write

	public function write($name,$value,$secondPw=null){

		$source=$this->read();

		//second Password...
		if($secondPw){
			$value=$this->Packer->Encrypt->encode($value,[
				"password"=>$secondPw,
			]);
		}

		if($name){
			$source[$name]=$value;
		}
		else
		{
			$source=$value;
		}

		if(!empty($this->limit)){
			$nowUnix=date_format(date_create("now"),"U");
			$source["__limit"]=date_format(date_create("@".intval($nowUnix+$this->limit)),"Y-m-d H:i:s");
		}

		if(!empty($this->encrypt)){
			$source=$this->Packer->Encrypt->encode($source,$this->encrypt);
		}

		$_SESSION[$this->name]=$source;

		return $this;
	}

	# _write

	private function _write($source){

		if(!empty($this->limit)){
			$nowUnix=date_format(date_create("now"),"U");
			$source["__limit"]=date_format(date_create("@".intval($nowUnix+$this->limit)),"Y-m-d H:i:s");
		}

		if(!empty($this->encrypt)){
			$source=$this->Packer->Encrypt->encode($source,$this->encrypt);
		}

		$_SESSION[$this->name]=$source;

	}

	# read

	public function read($name=null,$secondPw=null){

		if(!empty($_SESSION[$this->name])){
			$source=$_SESSION[$this->name];
		}
		else
		{
			return null;
		}

		if(!empty($this->encrypt)){
			$source=$this->Packer->Encrypt->decode($source,$this->encrypt);
		}

		if(!empty($this->limit)){

			$before_time=0;
			$getUnix=date_format(date_create("now"),"U");
			if(!empty($source["__limit"])){
				$before_time=date_format(date_create($source["__limit"]),"U");
			}
			if($getUnix>$before_time){
				@session_regenerate_id(true);
				$this->_write($source);
				$source["__limit"]=date_format(date_create("@".intval($getUnix+$this->limit)),"Y-m-d H:i:s");
			}
		}

		if(empty($opt["on_limit"])){
			unset($source["__limit"]);
		}
		if(!empty($opt["on_ssid"])){
			$source["__ssid"]=session_id();
		}

		if($name){
			if(!empty($source[$name])){
				$output=$source[$name];
			}
			else
			{
				return null;
			}
		}
		else
		{
			$output=$source;
		}

		if($secondPw){
			$output=$this->Packer->Encrypt->decode($output,[
				"password"=>$secondPw,
			]);
		}

		return $output;

	}

	# flash

	public function flash($name){

		$output=$this->read($name);
		$this->delete($name);

		return $output;

	}

	# delete

	public function delete($name=null){

		$source=$this->read();

		if($name){
			if(!empty($source[$name])){
				unset($source[$name]);
			}
			$this->write(null,$source);
		}
		else
		{
			if(!empty($_SESSION[$this->name])){
				unset($_SESSION[$this->name]);
			}
		}
	}

	# readCache

	public function readCache($name,$refreshLimit,$callback){

		$buff=$this->read($name);

		$refreshed=true;
		$getUnix=date_format(date_create("now"),"U");
		if($buff){

			if(date_format(date_create($buff["limit"]),"U")>$getUnix){
				$refreshed=false;
			}
		}

		if($refreshed){
			$buff=[
				"result"=>call_user_func($callback),
				"limit"=>date_format(date_create("@".intval($getUnix+$refreshLimit)),"Y-m-d H:i:s"),
			];

			$this->write($name,$buff);
		}

		return $buff["result"];

	}

	# get limit

	public function get_limit(){
		$limit=$this->read("__limit",array(
			"on_limit"=>true,
		));
		return $limit;
	}

	# change session id

	public function change_ssid(){
		session_regenerate_id(true);
	}

	# get session id

	public function get_ssid(){
		return session_id();
	}
}