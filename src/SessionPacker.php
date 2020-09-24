<?php

/**
 * 
 * [mk2 standard packer]
 * SessionPacker
 * 
 * session data management component.
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Packer;

class SessionPacker extends Packer{

	public $tmpPath=null; //Ex. MK2_PATH_APP_TEMPORARY."session";
	public $name="mk2sess";
	public $limit=10800;
	public $encrypt=[
		"encType"=>"aes-256-cbc",
		"hashNumber"=>"yodirog+aj23rf9Ad4ji3o7af98areaw52fa6gad",
		"password"=>"msorig0ra90gfa47f80A3er6a485j10ivjorjfaUe2Fr9f0a8f9agafadfa1ga54r5e6a1",
	];

	public $usePackerClass=[
		"Encrypt"=>"Encrypt",
	];

	/**
	 * __construct
	 */
	public function __construct($option){
		parent::__construct($option);

		$this->Loading->Packer([
			$this->getUsePackerClass("Encrypt"),
		]);

		if(!empty($this->tmpPath)){
			@mkdir($this->tmpPath,0775,true);
			@session_save_path($this->tmpPath);
		}
		@session_start();
	}

	/**
	 * write
	 */
	public function write($name,$value,$secondPw=null){

		$source=$this->read();

		//second Password...
		if($secondPw){
			$value=$this->Packer->{$this->getUsePackerClass("Encrypt")}->encode($value,[
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
			$nowUnix=date_format(date_create("now"),"YmdHis");
			$source["__limit"]=date_format(date_create("+".$this->limit." seconds"),"YmdHis");
		}

		if(!empty($this->encrypt)){
			$source=$this->Packer->{$this->getUsePackerClass("Encrypt")}->encode($source,$this->encrypt);
		}

		$_SESSION[$this->name]=$source;

		return $this;
	}

	/**
	 * (private)_write
	 */
	private function _write($source){

		if(!empty($this->limit)){
			$nowUnix=date_format(date_create("now"),"YmdHis");
			$source["__limit"]=date_format(date_create("+".$this->limit." seconds"),"YmdHis");
		}

		if(!empty($this->encrypt)){
			$source=$this->Packer->{$this->getUsePackerClass("Encrypt")}->encode($source,$this->encrypt);
		}

		$_SESSION[$this->name]=$source;

	}

	/**
	 * read
	 */
	public function read($name=null,$secondPw=null){

		if(!empty($_SESSION[$this->name])){
			$source=$_SESSION[$this->name];
		}
		else
		{
			return null;
		}

		if(!empty($this->encrypt)){
			$source=$this->Packer->{$this->getUsePackerClass("Encrypt")}->decode($source,$this->encrypt);
		}

		if(!empty($this->limit)){

			$before_time=0;
			$getUnix=date_format(date_create("now"),"YmdHis");
			if(!empty($source["__limit"])){
				$before_time=date_format(date_create($source["__limit"]),"YmdHis");
			}

			if(intval($getUnix)>intval($before_time)){
				@session_regenerate_id(true);
				$this->_write($source);
				$source["__limit"]=date_format(date_create("+".$this->limit." seconds"),"YmdHis");
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
			$output=$this->Packer->{$this->getUsePackerClass("Encrypt")}->decode($output,[
				"password"=>$secondPw,
			]);
		}

		return $output;

	}

	/**
	 * flash
	 */
	public function flash($name){

		$output=$this->read($name);
		$this->delete($name);

		return $output;

	}

	/**
	 * delete
	 */
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

	/**
	 * readCache
	 */
	public function readCache($name,$refreshLimit,$callback){

		$buff=$this->read($name);

		$refreshed=true;
		$getUnix=date_format(date_create("now"),"YmdHis");
		if($buff){

			if(date_format(date_create($buff["limit"]),"YmdHis")>$getUnix){
				$refreshed=false;
			}
		}

		if($refreshed){
			$buff=[
				"result"=>call_user_func($callback),
				"limit"=>date_format(date_create("+".$refreshLimit." seconds"),"YmdHis"),
			];

			$this->write($name,$buff);
		}

		return $buff["result"];

	}

	/**
	 * get_limit
	 */
	public function get_limit(){
		$limit=$this->read("__limit",array(
			"on_limit"=>true,
		));
		return $limit;
	}

	/**
	 * change_ssid
	 */
	public function change_ssid(){
		session_regenerate_id(true);
	}

	/**
	 * get_ssid
	 */
	public function get_ssid(){
		return session_id();
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

}