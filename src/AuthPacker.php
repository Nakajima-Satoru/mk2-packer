<?php

/*

- mk2 standard packer -

AuthPacker

A simple authentication component.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class AuthPacker extends Packer{

	public $authName="Mk2Auth";
	public $parityCheckSalt="509fjaoire0e9r0ajfaae0r9gjpoAoriJC";

	public $redirect=[
		"login"=>"@login",
		"logined"=>"/",
	];
	public $allowList=[];

	public function __construct($option){
		parent::__construct($option);

		$this->setPacker([
			"Session",
		]);

	}

	# convertAuthData

	public function convertAuthData($data){

		$loginDate=date_format(date_create("now"),"Y-m-d H:i:s");
		$parityCode=hash("sha256",$this->parityCheckSalt.json_encode($data).$loginDate);

		if(gettype($data)=="object"){
			$data->loginDate=$loginDate;
			$data->parityCode=$parityCode;
		}
		else if(gettype($data)=="array"){
			$data["loginDate"]=$loginDate;
			$data["parityCode"]=$parityCode;
		}

		return $data;

	}

	# refresh

	public function refresh($data,$ChangeParityCode=null){
		if($ChangeParityCode){
			$data=$this->convertAuthData($data);
		}
		$this->Packer->Session->change_ssid();		
		$this->Packer->Session->write($this->authName,$data);
	}

	# loginCheck

	public function loginCheck(){

		if(!empty($this->Packer->Session->read($this->authName))){
				
			if($this->_convertUrl(Request::$params["url"])==$this->_convertUrl($this->redirect["login"])){
				$this->redirect($this->redirect["logined"]);
			}
			else{

				$authData=$this->Packer->Session->read($this->authName);

				# parityCheck
				$buff=$authData;
				unset($buff["parityCode"],$buff["loginDate"]);
				$parityCode=hash("sha256",$this->parityCheckSalt.json_encode($buff).$authData["loginDate"]);

				if($parityCode!=$authData["parityCode"]){
					$this->Packer->Session->delete($this->authName);
					$this->redirect($this->redirect["login"]);
				}

				return $authData;
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
					$this->redirect($this->redirect["login"]);
				}
			}

		}
	}

	# logout

	public function logout(){
		$this->Packer->Session->delete($this->authName);
		$this->Packer->Session->change_ssid();		
	}

	# (private) _convertUrl

	private function _convertUrl($params){

		$url=$this->getUrl($params);
		$url=explode("?",$url);
		$url=$url[0];

		if(substr($url,strlen($url)-1,1)!="/"){
			$url.="/";
		}

		return $url;

	}
}