<?php

/*

- mk2 standard packer -

CurlPacker

For external requests using the Curl module.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class CurlPacker extends Packer{

	// access
	public function access($params=array()){

		$this->_output=null;

		$curl=curl_init();

		if(empty($params["method"])){
			$params["method"]="get";
		}

		if($params["method"]=="post"){
			if(!empty($params["data"])){
				curl_setopt($curl, CURLOPT_POST, TRUE);
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(@$params["data"]));
			}
			if(!empty($params["data_get"])){
				$buildurl=http_build_query($params["data_get"]);
				$params["url"].="?".$buildurl;
			}
		}
		else
		{
			if(!empty($params["data"])){
				$buildurl=http_build_query($params["data"]);
				$params["url"].="?".$buildurl;
			}
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		}

		curl_setopt($curl, CURLOPT_URL, @$params["url"]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		//on header
		if(!empty($params["headers"])){
			$headers=array();
			foreach($params["headers"] as $key=>$p_){
				$headers[]=$key.": ".$p_;
			}
			curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
		}

		//get option data
		$output=new \stdClass();
		$out=curl_exec($curl);
		$output->resHeader=curl_getinfo($curl);
		$output->response=$out;
		if(!empty(curl_error($curl))){
			$output->error=curl_error($curl);
		}

		curl_close($curl);

		if(!empty(json_decode($output->response))){
			$output->response=json_decode($output->response);
		}

		return $output;
	}

	# access GET
	
	public function accessGet($url,$getData=[],$headerOption=[]){

		$params=[
			"url"=>$url,
			"method"=>"get",
			"data"=>$getData,
			"headers"=>$headerOption,
		];

		return $this->access($params);
		
	}

	# access POST

	public function accessPost($url,$postData=[],$getData=[],$headerOption=[]){

		$params=[
			"url"=>$url,
			"method"=>"post",
			"data"=>$postData,
			"data_get"=>$getData,
			"headers"=>$headerOption,
		];

		return $this->access($params);
	}

	# accessHeaders
	
	public function accessHeaders($url,$headerOption=[]){

		$params=[
			"url"=>$url,
			"method"=>"get",
			"headers"=>$headerOption,
		];

		return $this->access($params);

	}
}
