<?php

/*

- mk2 standard packer -

PrivateApiPacker

Private-API Access Packer.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class PrivateApiPacker extends Packer{

	/**
	 * option settiongs
	 * 
	 * {apiName}=>[
	 * 
	 * 		// Request URL
	 * 		url=>""https://api.xxxxxxx.jp/xxxxxxxxx/",
	 * 
	 * 		// Token : For token generation information.
	 * 		token=>[
	 * 			"algo"=>"sha512",							// token create algolizum.
	 * 			"salt"=>"*****************************"",	// token create salt.
	 * 			"stretch"=>6,								// token create strech count.
	 * 			"limit"=>30,								// Token expiration date.
	 * 		],
	 * 
	 * 		// Header : Additional request header information.
	 * 		header=>[
	 * 			"keyword"=>"abcdefg1234",
	 * 		],
	 *
	 * ],
	 */

	public function __construct($option){
		parent::__construct($option);

		$this->setPacker([
			"Curl",
		]);

	}

	/**
	 * access
	 * 
	 * @param apiName
	 * @param addUrl = null
	 * @param requestData = []
	 */
	public function access($apiName,$addUrl=null,$requestData=[]){

		$getConf=$this->{$apiName};

		$url=$getConf["url"].$addUrl;

		$headerOption=[];

		if(!empty($getConf["header"])){
			$headerOption=$getConf["header"];
		}

		// token set
		$headerOption["token"]=$this->getPrivateToken($apiName);

		$res=$this->Packer->Curl->accessPost($url,$requestData,null,$headerOption);

		return $res;

	}

	/**
	 * listen
	 */
	public function listen($apiName){

		$getConf=$this->{$apiName};

		$getHeader=getallheaders();
		
		if(empty($getHeader["token"])){
			return false;
		}

		if(!empty($getConf["header"])){
			$jugement=true;
			foreach($getConf["header"] as $key=>$h_){
				if(!empty($getHeader[$key])){
					if($getHeader[$key]!=$h_){
						$jugement=false;
						break;
					}
				}
				else
				{
					$jugement=false;
				break;
				}
			}

			if(!$jugement){
				return false;
			}

		}

		if(!$this->checkListenPrivateToken($getHeader["token"],$getConf)){
			return false;
		}

		return true;

	}

	/**
	 * (private)getPrivateToken
	 */
	private function getPrivateToken($apiName){

		$getConf=$this->{$apiName};

		if(empty($getConf["token"]["algo"])){
			$getConf["token"]["algo"]="sha256";
		}
		if(empty($getConf["token"]["salt"])){
			$getConf["token"]["salt"]="abcdefg2Go";
		}
		if(empty($getConf["token"]["stretch"])){
			$getConf["token"]["stretch"]=2;
		}

		$hash=hash($getConf["token"]["algo"],$getConf["token"]["salt"].date_format(date_create("now"),"YmdHis"));
		for($n1=0;$n1<$getConf["token"]["stretch"];$n1++){
			$hash=hash($getConf["token"]["algo"],$hash);
		}

		return $hash;

	}

	/**
	 * (private)checkListenPrivateToken
	 */
	private function checkListenPrivateToken($targetToken,$getConf){

		if(empty($getConf["token"]["algo"])){
			$getConf["token"]["algo"]="sha256";
		}
		if(empty($getConf["token"]["salt"])){
			$getConf["token"]["salt"]="abcdefg2Go";
		}
		if(empty($getConf["token"]["stretch"])){
			$getConf["token"]["stretch"]=2;
		}
		if(empty($getConf["token"]["limit"])){
			$getConf["token"]["limit"]=180;
		}

		$jugement=false;
		for($v1=0;$v1<$getConf["token"]["limit"];$v1++){

			$makeToken=hash($getConf["token"]["algo"],$getConf["token"]["salt"].date_format(date_create("-".$v1." second"),"YmdHis"));
			for($n1=0;$n1<$getConf["token"]["stretch"];$n1++){
				$makeToken=hash($getConf["token"]["algo"],$makeToken);
			}
	
			if($makeToken==$targetToken){
				$jugement=true;
				break;
			}
		}

		return $jugement;
	}

}