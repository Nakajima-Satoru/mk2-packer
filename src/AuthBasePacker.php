<?php

/**
 * 
 * [mk2 standard packer]
 * AuthBasePacker
 * 
 * A simple authentication component.
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Packer;
use mk2\core\Request;

class AuthBasePacker extends Packer{

	public $authName="Mk2Auth";
	public $parityCode=[
		"algo"=>"sha256",
		"salt"=>"509fjaoire0e9r0ajfaae0r9gjpoAoriJC",
		"stretch"=>3,
	];

	public $redirect=[
		"login"=>"@login",
		"logined"=>"/",
	];
	public $allowList=[];

	public $usePackerClass=[
		"Session"=>"Session",
	];

	public function __construct($option){
		parent::__construct($option);

		$this->Loading->Packer([
			$this->getUsePackerClass("Session"),
		]);

	}

	/**
	 * convertAuthData
	 */
	public function convertAuthData($data){

		$loginDate=date_format(date_create("now"),"Y-m-d H:i:s");
		$parityCode=$this->_makeParityCode($data,$loginDate);

		return [
			"loginDate"=>$loginDate,
			"parityCode"=>$parityCode,
			"data"=>$data,	
		];

	}

	/**
	 * getAuthData
	 */
	public function getAuthData(){
		
		$getData=$this->Packer->{$this->getUsePackerClass("Session")}->read($this->authName);
		if($getData){
			return $getData["data"];
		}
	}

	/**
	 * refresh
	 */
	public function refresh($data){
		$data=$this->convertAuthData($data);
		$this->Packer->{$this->getUsePackerClass("Session")}->change_ssid();
		$this->Packer->{$this->getUsePackerClass("Session")}->write($this->authName,$data);
	}

	/**
	 * loginCheck
	 */
	public function loginCheck(){

		if(!empty($this->Packer->{$this->getUsePackerClass("Session")}->read($this->authName))){
				
			if($this->_convertUrl(Request::$params["url"])==$this->_convertUrl($this->redirect["login"])){
				$this->Response->redirect($this->redirect["logined"]);
			}
			else{

				$authData=$this->Packer->{$this->getUsePackerClass("Session")}->read($this->authName);

				# parityCheck
				$buff=$authData["data"];
				$parityCode=$this->_makeParityCode($buff,$authData["loginDate"]);

				if($parityCode!=$authData["parityCode"]){
					$this->Packer->{$this->getUsePackerClass("Session")}->delete($this->authName);
					$this->Response->redirect($this->redirect["login"]);
				}

				return $authData["data"];
			}

		}
		else{

			if($this->_convertUrl(Request::$params["url"])==$this->_convertUrl($this->redirect["login"])){

			}
			else{

				$jugement=false;
				if(!empty($this->allowList)){
					foreach($this->allowList as $a_){
						if($this->_convertUrl(Request::$params["url"])==$this->_convertUrl($a_)){
							$jugement=true;
							break;
						}
					}
				}

				if(!$jugement){
					$this->Response->redirect($this->redirect["login"]);
				}
			}

		}
	}

	/**
	 * logout
	 */
	public function logout(){
		$this->Packer->{$this->getUsePackerClass("Session")}->delete($this->authName);
		$this->Packer->{$this->getUsePackerClass("Session")}->change_ssid();		
	}

	/**
	 * (private) _convertUrl
	 */
	private function _convertUrl($params){

		$url=$this->Response->getUrl($params);
		$url=explode("?",$url);
		$url=$url[0];

		if(substr($url,strlen($url)-1,1)!="/"){
			$url.="/";
		}

		return $url;

	}

	/**
	 * (private) _makeParityCode
	 */
	private function _makeParityCode($data,$loginDate){

		if(empty($this->parityCode["algo"])){
			$this->parityCode["algo"]="sha256";
		}
		if(empty($this->parityCode["salt"])){
			$this->parityCode["salt"]="f0a9rgjairojfia10fAoidfjAOZiOER097777+a";
		}
		if(empty($this->parityCode["stretch"])){
			$this->parityCode["stretch"]=2;
		}

		$parityCode=json_encode($data);
		for($v1=0;$v1<$this->parityCode["stretch"];$v1++){
			$parityCode=hash($this->parityCode["algo"],$parityCode);
		}

		return $parityCode;

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