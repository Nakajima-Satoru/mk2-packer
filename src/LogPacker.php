<?php

/**
 * 
 * [mk2 standard packer]
 * LogPacker
 * 
 * Log output component.
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Packer;

class LogPacker extends Packer{

	public $tmpDir=MK2_PATH_APP_TEMPORARY."log";

	public $writeError=[
		"simple"=>false,
		"splitDate"=>"Ymd",
	];

	public $writeAccess=[
		"simple"=>false,
		"splitDate"=>"Ymd",
	];

	/**
	 * writeError
	 */
	public function writeError($error){

		$strings="";
		$this->_writeDefault();

		$strings=$this->_writeStrError($error);
		if(empty($this->writeError["simple"])){
			$strings.="------------------------------------------------------\n";
		}

		if(empty($this->writeError["fileName"])){
			$this->writeError["fileName"]="error";
		}

		$fileName=$this->writeError["fileName"];
		if(!empty($this->writeError["splitDate"])){
			$fileName=date_format(date_create("now"),$this->writeError["splitDate"])."-".$fileName;
		}

		# error log write
		if(!empty($this->tmpDir)){
			error_log($strings,3,$this->tmpDir."/".$fileName);
		}
		else
		{
			error_log($strings);
		}

	}

	/**
	 * (privte) _writeDefault
	 */
	private function _writeDefault($mode="error"){
		
		if(!is_dir($this->tmpDir)){
			@mkdir($this->tmpDir,0777,true);
		}

	}

	/**
	 * (privte) _writeStrError
	 */
	private function _writeStrError($error){

		$lof=" | ";
		if(!$this->writeError["simple"]){
			$lof="\n";
		}
		$str=date_format(date_create("now"),"Y-m-d H:i:s").$lof;
		$str.=$_SERVER["REMOTE_ADDR"].$lof;
		$str.=$_SERVER["REQUEST_URI"].$lof;
		if(!empty($_SERVER["HTTP_USER_AGENT"])){
			$str.=$_SERVER["HTTP_USER_AGENT"].$lof;
		}
		$str.=$error->getFile()."(".$error->getLine().")".$lof;
		$str.=$error->getMessage();

		if(!$this->writeError["simple"]){

			$str.="\n"."Trace:".$error->getFile()."(".$error->getLine().")\n";

			$trace=$error->getTrace();
			foreach($trace as $e_){
				$str.="Trace:".$e_["file"]."(".$e_["line"].") function:".$e_["function"]." class:".$e_["class"]."\n";
			}

		}

		$str.="\n";

		return $str;
	}

	/**
	 * writeAccess
	 */
	public function writeAccess(){

		$strings="";
		$this->_writeDefault("access");

		$strings=$this->_writeStrAccess();
		if(empty($this->writeAccess["simple"])){
			$strings.="------------------------------------------------------\n";
		}

		if(empty($this->writeAccess["fileName"])){
			$this->writeAccess["fileName"]="access";
		}

		$fileName=$this->writeAccess["fileName"];
		if(!empty($this->writeAccess["splitDate"])){
			$fileName=date_format(date_create("now"),$this->writeAccess["splitDate"])."-".$fileName;
		}

		# access log write
		if(!empty($this->tmpDir)){
			error_log($strings,3,$this->tmpDir."/".$fileName);
		}
		else
		{
			error_log($strings);
		}

	}

	/**
	 * (private) _writeStrAccess
	 */
	private function _writeStrAccess(){

		$lof=" | ";
		if(!$this->writeAccess["simple"]){
			$lof="\n";
		}

		$str=date_format(date_create("now"),"Y-m-d H:i:s").$lof;
		$str.=$_SERVER["REMOTE_ADDR"].$lof;
		$str.=$_SERVER["REQUEST_URI"].$lof;
		if(!empty($_SERVER["HTTP_USER_AGENT"])){
			$str.=$_SERVER["HTTP_USER_AGENT"].$lof;
		}		
		$str.="\n";

		if(!$this->writeAccess["simple"]){
			$str.="\n";
		}

		return $str;

	}

	/**
	 * write
	 */
	public function write($fileName,$message,$lof=false){

		$strings="";
		$this->_writeDefault();

		$strings=$this->_writeStr($message,$lof);

		 # log write
		if(!empty($this->tmpDir)){
			error_log($strings,3,$this->tmpDir."/".$fileName);
		}
		else
		{
			error_log($strings);
		}

	}

	/**
	 * (private) _writeStr
	 */
	private function _writeStr($message,$simple){

		$lof=" | ";
		if(!$simple){
			$lof="\n";
		}

		$str=date_format(date_create("now"),"Y-m-d H:i:s").$lof;
		$str.=$_SERVER["REMOTE_ADDR"].$lof;
		$str.=$_SERVER["REQUEST_URI"].$lof;
		if(!empty($_SERVER["HTTP_USER_AGENT"])){
			$str.=$_SERVER["HTTP_USER_AGENT"].$lof;
		}
		$str.=$message.$lof;
		$str.="\n";

		if(!$simple){
			$str.="\n";
		}

		return $str;

	}
}