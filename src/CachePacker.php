<?php

/**
 * 
 * [mk2 standard packer]
 * CachePacker
 * 
 * Data cache management component.
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Packer;

class CachePacker extends Packer{

	/**
	 * mode 	: Cache method.
	 * file		= file cache(Automatically generated in the temporary directory)
	 * memory	= Cache in shared memory (requires APCu module)
	 */

	public $mode="file"; 
	public $publics=true;

	# name : Cache space name

	public $name="mk2_cache";

	# limit : Default expiration time when caching files.

	public $limit=0;
	public $encrypt=[
		"encType"=>"aes-256-cbc",
		"hashNumber"=>"FIEROGF9230945FJIERG7f99fa0e9r8GIORg",
		"password"=>"Ysfr9a08regpoiD980raejAIDorie0-9g8a",
		"binaryOutput"=>true,
	];
	public $fileTmp=MK2_PATH_APP_TEMPORARY;

	public $usePackerClass=[
		"Encrypt"=>"Encrypt",
	];

	/**
	 * __construct
	 */
	public function __construct($option=null){
		parent::__construct($option);

		$this->Loading->Packer([
			$this->usePackerClass["Encrypt"],
		]);

	}

	/**
	 * public
	 */
	public function public(){

		$this->publics=true;
		return $this;

	}

	/**
	 * private
	 */
	public function private(){

		$this->publics=false;
		return $this;

	}

	/**
	 * read
	 */
	public function read($name=null,$secondPw=null){

		$get=$this->_read($name);

		if($name){

			if($this->mode=="file"){
				if(!empty($get[$name])){

					if($secondPw){

						$encOpt=$this->encrypt;
						$encOpt["password"]=$secondPw;

						$get=$this->Packer->{$this->usePackerClass["Encrypt"]}->decode($get,$encOpt);
					}
				}

				return $get[$name];

			}
			else if($this->mode=="memory"){
				return $get;
			}
		}
		else
		{
			return $get;
		}

	}

	/**
	 * flash
	 */
	public function flash($name){

		$out=$this->read($name);
		$this->delete($name);

		return $out;

	}

	/**
	 * buffering
	 */
	public function buffering($name,$limit=0,$callbacks){

		$buff=$this->read($name);

		$refreshed=true;

		if($buff){

			if(empty($buff["refresh"])){

				if(!empty($buff["limit"])){
					if(date_format(date_create($buff["limit"]),"YmdHis")>date_format(date_create("now"),"YmdHis")){
						$refreshed=false;
					}
				}
				else
				{
					$refreshed=false;
				}

			}
		}

		if($refreshed){

			if($limit){
				$buff=[
					"result"=>call_user_func($callbacks),
					"limit"=>date_format(date_create("+".$limit." seconds"),"YmdHis"),
					"refresh"=>false,
				];
			}
			else
			{
				$buff=[
					"result"=>call_user_func($callbacks),
					"refresh"=>false,
				];
			}

			$this->write($name,$buff);
		}

		return $buff["result"];

	}

	/**
	 * bufferingAllow
	 */
	public function bufferingAllow($name){

		$buff=$this->read($name);

		if($buff){
			$buff["refresh"]=true;

			$this->write($name,$buff);
		}

	}

	/**
	 * write
	 */
	public function write($name,$value,$secondPw=null){

		if($secondPw){

			$encOpt=$this->encrypt;
			$encOpt["password"]=$secondPw;

			$get[$name]=$this->Packer->{$this->usePackerClass["Encrypt"]}->encode($value,$encOpt);

		}
		else
		{
			$get[$name]=$value;
		}

		$res=$this->_write($name,$get);

	}

	/**
	 * delete
	 */
	public function delete($name=null){

		if($name){
			return $this->_delete($name);
		}
		else
		{
			$this->clear();
		}

	}

	/**
	 * clear
	 */
	public function clear(){

		$this->_delete();

	}

	/**
	 * getMemoryInfo
	 */
	public function getMemoryInfo(){

		return apcu_cache_info();

	}

	/**
	 * getMemoryUsed
	 */
	public function getMemoryUsed($errMsged=false){

		if($this->mode=="memory"){
			try{

				apcu_fetch($this->name."_public");
				return true;

			}catch(\Error $e){
				if($errMsged){
					return $e;
				}
				else
				{
					return false;
				}
			}
		}
	}

	/**
	 * setMemoryClear
	 */
	public function setMemoryClear($full=false){

		if($this->publics){
			if($full){
				$memoryPath=$this->name."_public";
			}
		}
		else
		{
			$memoryPath=$this->name."_private_".$this->_getPrivateCacheId();
		}

		apcu_delete($memoryPath);

	}

	/**
	 * (private) _write
	 */
	private function _write($name,$value){

		if($this->mode=="file"){
			$this->_writeFile($name,$value);
		}
		else if($this->mode=="memory"){
			$this->_writeMemory($name,$value);
		}

	}

	/**
	 * (private) _writeFile
	 */
	private function _writeFile($name,$value){

		if(!empty($this->encrypt)){
			$value=$this->Packer->{$this->usePackerClass["Encrypt"]}->encode($value,$this->encrypt);
		}
		else
		{
			$value=json_enc($value);
		}

		if($this->publics){
			//public cache write
			$filedir=$this->fileTmp.$this->name."/_public";
			$filename=$this->fileTmp.$this->name."/_public/".hash("sha256",$name);
			@mkdir($filedir,0775,true);
			$fs=fopen($filename,"w");
			fputs($fs,$value);
			fclose($fs);
		}
		else
		{
			//private cache write
			$filedir=$this->fileTmp.$this->name."/_private/".$this->_getPrivateCacheId();
			$filename=$this->fileTmp.$this->name."/_private/".$this->_getPrivateCacheId()."/".hash("sha256",$name);
			@mkdir($filedir,0775,true);
			$fs=fopen($filename,"w");
			fputs($fs,$value);
			fclose($fs);
		}

	}

	/**
	 * (private) _writeMemory
	 */
	private function _writeMemory($name,$value){

		if($this->publics){
			$memoryPath=$this->name."_public";
		}
		else
		{
			$memoryPath=$this->name."_private_".$this->_getPrivateCacheId();
		}

		$get=$this->_read();

		$get[$name]=$value[$name];

		if(!empty($this->encrypt)){
			$get=$this->Packer->{$this->usePackerClass["Encrypt"]}->encode($get,$this->encrypt);
		}

		$res=apcu_store($memoryPath,$get,$this->limit);

	}

	/**
	 * (private) _read
	 */
	private function _read($name=null){

		if($this->mode=="file"){
			return $this->_readFile($name);
		}
		else if($this->mode=="memory"){
			return $this->_readMemory($name);
		}

	}

	/**
	 * (private) _readFile
	 */
	private function _readFile($name=null){


		$get=null;

		if($this->publics){
			if($name){
				$filename=$this->fileTmp.$this->name."/_public/".hash("sha256",$name);
				if(file_exists($filename)){
					$fs=fopen($filename,"r");
					$get=fgets($fs);
					fclose($fs);

					if(!empty($this->encrypt)){
						$get=$this->Packer->{$this->usePackerClass["Encrypt"]}->decode($get,$this->encrypt);
					}
					else
					{
						$get=json_dec($get);
					}
				}
			}
			else
			{
				$filedir=$this->fileTmp.$this->name."/_public";
				$list=glob($filedir."/*");
				foreach($list as $l_){
					$n=basename($l_);
					$fs=fopen($l_,"r");
					$buff=fgets($fs);
					fclose($fs);

					if(!empty($this->encrypt)){
						$buff=$this->Packer->{$this->usePackerClass["Encrypt"]}->decode($buff,$this->encrypt);
					}
					else
					{
						$buff=json_dec($buff);
					}

					if($buff){
						foreach($buff as $key=>$value){
							$get[$key]=$value;
						}
					}
				}
			}
		}
		else
		{
			if($name){
				$filename=$this->fileTmp.$this->name."/_private/".$this->_getPrivateCacheId()."/".hash("sha256",$name);
				if(file_exists($filename)){
					$fs=fopen($filename,"r");
					$get=fgets($fs);
					fclose($fs);

					if(!empty($this->encrypt)){
						$get=$this->Packer->{$this->usePackerClass["Encrypt"]}->decode($get,$this->encrypt);
					}
					else
					{
						$get=json_dec($get);
					}
				}
			}
			else
			{
				$filedir=$this->fileTmp.$this->name."/_private/".$this->_getPrivateCacheId();
				$list=glob($filedir."/*");
				foreach($list as $l_){
					$n=basename($l_);
					$fs=fopen($l_,"r");
					$buff=fgets($fs);
					fclose($fs);

					if(!empty($this->encrypt)){
						$buff=$this->Packer->{$this->usePackerClass["Encrypt"]}->decode($buff,$this->encrypt);
					}
					else
					{
						$buff=json_dec($get);
					}

					foreach($buff as $key=>$value){
						$get[$key]=$value;
					}

				}
			}
		}

		return $get;

	}

	/**
	 * (private) _readMemory
	 */
	private function _readMemory($name=null){

		if($this->publics){
			$memoryPath=$this->name."_public";
		}
		else
		{
			$memoryPath=$this->name."_private_".$this->_getPrivateCacheId();
		}

		$get=apcu_fetch($memoryPath);

		if(!empty($this->encrypt)){
			$get=$this->Packer->{$this->usePackerClass["Encrypt"]}->decode($get,$this->encrypt);
		}

		if($name){

			if(!empty($get[$name])){
				return $get[$name];
			}

		}
		else
		{
			return $get;
		}

	}

	/**
	 * (private) _delete
	 */
	private function _delete($name=null){

		if($this->mode=="file"){
			$this->_deleteFile($name);
		}
		else if($this->mode=="memory"){
			$this->_deleteMemory($name);
		}

	}

	/**
	 * (private) _deleteFile
	 */
	private function _deleteFile($name=null){

		if($name){
			if($this->publics){
				$filepath=$this->fileTmp.$this->name."/_public/".hash("sha256",$name);
				@unlink($filepath);
			}
			else
			{
				$filepath=$this->fileTmp.$this->name."/_private/".$this->_getPrivateCacheId()."/".hash("sha256",$name);
				@unlink($filepath);
			}
		}
		else
		{
			if($this->publics){
				$filedir=$this->fileTmp.$this->name."/_public";
				$list=glob($filedir."/*");
				foreach($list as $l_){
					unlink($l_);
				}
			}
			else{
				$filedir=$this->fileTmp.$this->name."/_private/".$this->_getPrivateCacheId();
				$list=glob($filedir."/*");
				foreach($list as $l_){
					unlink($l_);
				}
			}
		}
	}

	/**
	 * (private) _deleteMemory
	 */
	private function _deleteMemory($name=null){

		$get=$this->read();

		if($this->publics){
			$memoryPath=$this->name."_public";
		}
		else
		{
			$memoryPath=$this->name."_private_".$this->_getPrivateCacheId();
		}

		if($name){

			unset($get[$name]);

		}
		else
		{
			$get=[];
		}

		if(!empty($this->encrypt)){
			$get=$this->Packer->{$this->usePackerClass["Encrypt"]}->encode($get,$this->encrypt);
		}

		apcu_store($memoryPath,$get,$this->limit);

	}

	/**
	 * (private) _getPrivateCacheId
	 */
	private function _getPrivateCacheId(){

		if($this->publics){
			return null;
		}

		if(!empty($_COOKIE["CACHEID"])){
			return $_COOKIE["CACHEID"];
		}
		else
		{
			$cacheId=hash("sha256",time()."|_CDA_");
			setcookie("CACHEID",$cacheId,0);
			return $cacheId;
		}
	}

}