<?php

/*

- mk2 standard packer -

PrivateApiPacker

It is Backpack which can implement PrivateAPI simply without external release.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class PrivateApiPacker extends Packer{

	public $ipAllow=true;
	public $originAllow=true;

	# (constructor)

	public function __construct(){
		parent::__construct();

		$this->setPacker([
			"Curl",
		]);
	}

	# request

	public function request($authName,$url,$option=[]){

		$config=$this->{$authName};

		if(empty($config["token"]["algo"])){
			$config["token"]["algo"]="sha256";
		}

		$token=$this->getApiToken($authName);
		$option["header"]["token"]=$token;

		$params=array(
			"url"=>@$config["url"].$url,
			"method"=>"post",
			"header"=>@$config["header"],
			"data"=>@$option["post"],
			"data_get"=>@$option["get"],
		);

		if(!empty($config["header"])){
			foreach($config["header"] as $key=>$o_){
				$params["headers"][$key]=$o_;
			}
		}
		if(!empty($option["header"])){
			foreach($option["header"] as $key=>$o_){
				$params["headers"][$key]=$o_;
			}
		}
		$res=$this->Packer->Curl->access($params);
		return $res;
	}

	# requestGet

	public function requestGet($authName,$url,$getData=[],$headerData=[]){

		$option=[
			"method"=>"get",
			"get"=>$getData,
			"header"=>$headerData,
		];
		return $this->request($authName,$url,$option);

	}

	# requestPost

	public function requestPost($authName,$url,$postData=[],$getData=[],$headerData=[]){

		$option=[
			"method"=>"post",
			"post"=>$postData,
			"get"=>$getData,
			"header"=>$headerData,
		];
		return $this->request($authName,$url,$option);

	}

	# listen

	public function listen($name){

		$config=$this->{$name};

		if(empty($config["token"]["limit"])){
			$config["token"]["limit"]=20;
		}
		if(empty($config["token"]["algo"])){
			$config["token"]["algo"]="sha256";
		}

		$juge=false;

		$getHeader=getallheaders();

		if(!empty($getHeader["token"])){
			$token=$getHeader["token"];

			for($u1=0;$u1<$config["token"]["limit"];$u1++){
				$tims=set_strtotime()-$u1;

				$target=hash($config["token"]["algo"],@$config["token"]["key"]."||".$tims);

				if($target==$token){
					$juge=true;
					if(!empty($config["header"])){

						foreach($config["header"] as $key=>$ch_){
							if(@$getHeader[$key]!=$ch_){
								$juge=false;
								break;
							}
						}
					}
					break;
				}
			}
		}

		if($this->originAllow){
			header("Access-Control-Allow-Origin: *");
		}

		if($juge){
			if($this->ipAllow){
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	# getApiToken
	public function getApiToken($authname){

		$config=$this->{$authname};
		return hash($config["token"]["algo"],$config["token"]["key"]."||".set_strtotime());

	}

	# addIpFilter

	public function addIpFilter($mode,$ipList){

		$ipAddress=$this->request->params["option"]["remote"];

		if($mode=="allow"){
			$juge=false;
		}
		else if($mode=="ignore"){
			$juge=true;
		}
		foreach($ipList as $i_){
			if($i_==$ipAddress){
				if($mode=="allow"){
					$juge=true;
				}
				else if($mode=="ignore"){
					$juge=false;
				}
				break;
			}
		}

		$this->ipAllow=$juge;

		return $this;
	}

	# addIpAllow

	public function addIpAllow($ipList){
		return $this->addIpFilter("allow",$ipList);
	}

	# appIpIgnore

	public function appIpIgnore($ipList){
		return $this->addIpFilter("ignore",$ipList);
	}

	# addOriginFilter

	public function addOriginFilter($mode,$originList){

		$headers=getallheaders();

		$origin="";
		if(!empty($headers["Origin"])){
			$origin=$headers["Origin"];
		}

		if($mode=="allow"){
			$juge=false;
		}
		else if($mode=="ignore"){
			$juge=true;
		}

		foreach($originList as $o_){
			$patternA="http://".$o_;
			$patternB="https://".$o_;
			if($origin==$patternA || $origin==$patternB){
				if($mode=="allow"){
					$juge=true;
				}
				else if($mode=="ignore"){
					$juge=false;
				}
				break;
			}
		}

		$this->originAllow=$juge;

		return $this;
	}

	# addOriginAllow

	public function addOriginAllow($ipList){
		return $this->addOriginFilter("allow",$ipList);
	}

	# appOriginIgnore

	public function appOriginIgnore($ipList){
		return $this->addOriginFilter("ignore",$ipList);
	}

}
