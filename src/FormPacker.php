<?php

/**
 * 
 * [mk2 standard packer]
 * FormPacker
 * 
 * For form tag generation.
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Packer;
use mk2\core\Request;

class FormPacker extends Packer{

	public static $tokenSalt="4049far3afjie7ep02Aar09f0g098a6r005hjFuf";
	public static $errorMessage=[];
	public static $_cssFramework=null;

	/**
	 * __construct
	 */
	public function __construct($option){
		parent::__construct($option);

		if(!empty($option["cssFramework"])){
			self::$_cssFramework=$option["cssFramework"];
			unset($option["cssFramework"]);
		}

	}

	/**
	 * setErrors
	 */
	public function setErrors($data){
		self::$errorMessage=$data;
	}

	/**
	 * setToken
	 */
	public function setToken($tokenSalt){
		self::$tokenSalt=$tokenSalt;
		return $this;
	}

	/**
	 * verify
	 */
	public function verify(){
		
		if(empty(Request::$post["__token"])){
			return false;
		}

		$targetToken=Request::$post["__token"];

		$ref=getallheaders();

		if(!empty($ref["referer"])){
			$referer=$ref["referer"];
		}
		else if(!empty($ref["Referer"])){
			$referer=$ref["Referer"];
		}

		$referer=str_replace("http://","",$referer);
		$referer=str_replace("https://","",$referer);

		if(empty($referer)){
			return false;
		}


		$targetToken2=hash("sha256",self::$tokenSalt.$referer);
		
		if($targetToken!=$targetToken2){
			return false;
		}

		unset(Request::$post["__token"]);
		
		return true;
	}
}

/**
 * FormPackerUI
 */
class FormPackerUI extends FormPacker{

	private $method="post";
	public $cssFramework=null;

	/**
	 * start
	 */
	public function start($option=null){
		$str='<form';

		if(empty($option["method"])){
			$option["method"]="post";
		}

		$this->method=$option["method"];
		if(!empty($option["cssFramework"])){
			$this->cssFramework=$option["cssFramework"];
			unset($option["cssFramework"]);
		}
		if(!empty(self::$_cssFramework)){
			$this->cssFramework=self::$_cssFramework;
		}

		if(!empty($option["fileUpload"])){
			$option["enctype"]="multipart/form-data";
			unset($option["fileUpload"]);
		}

		$str=$this->_setTagAttribute($str,$option);

		$str.=">";
		return $str."\n";
	}

	/**
	 * end
	 */
	public function end(){

		$this->cssFramework=null;
		return "</form>";

	}

	/**
	 * setInput
	 */
	public function setInput($name,$option=null){
		$str='<input';

		$option["name"]=$this->_setName($name);
		if(empty($option["type"])){
			$option["type"]="text";
		}

		if($option["type"]!="radio" && $option["type"]!="checkbox"){

			$defValue="";
			if(!empty($option["value"])){
				$defValue=$option["value"];
			}
			$ans=$this->_requestCheck($name,$defValue);

			$value="";
			if($ans){
				$value=$ans;
			}

			if(empty($option["fixedValue"])){
				$option["value"]=$value;
			}
			else{
				unset($option["fixedValue"]);
			}
		}

		# css Framework case...
		if($this->cssFramework=="bootstrap"){

			if($option["type"]!="radio" && $option["type"]!="checkbox" && $option["type"]!="submit" && $option["type"]!="button"){

				# is bootstrap...

				if(empty($option["class"])){
					$option["class"]="";
				}
				$option["class"].=" form-control";
			
				$res=$this->_getErrorMessage($name);

				if(!empty($res)){
					$option["class"].=" is-invalid";
				}

			}

		}
		else{

			# is other...

			$res=$this->_getErrorMessage($name);

			if(!empty($res)){
				if(empty($option["class"])){
					$option["class"]="";
				}
				$option["class"].=" is-error";
			}

		}

		$str=$this->_setTagAttribute($str,$option);

		$str.=">";
		return $str."\n";
	}

	/**
	 * setHidden
	 */
	public function setHidden($name,$option=null){
		$option["type"]="hidden";
		return $this->setInput($name,$option);
	}

	/**
	 * setTextarea
	 */
	public function setTextarea($name,$option=null){
		$str='<textarea';

		$defValue="";
		if(!empty($option["value"])){
			$defValue=$option["value"];
		}

		$ans=$this->_requestCheck($name,$defValue);

		$value="";
		if($ans){
			$value=$ans;
		}

		if(empty($option["fixedValue"])){
			$option["value"]=$value;
		}
		else{
			unset($option["fixedValue"]);
		}

		$option["name"]=$this->_setName($name);

		# css Framework case...
		if($this->cssFramework=="bootstrap"){

			# is bootstrap...
			if(empty($option["class"])){
				$option["class"]="";
			}

			$option["class"].=" form-control";

			$res=$this->_getErrorMessage($name);

			if(!empty($res)){
				$option["class"].=" is-invalid";
			}
			
		}
		else{

			# is other...

			$res=$this->_getErrorMessage($name);

			if(!empty($res)){
				if(empty($option["class"])){
					$option["class"]="";
				}
				$option["class"].=" is-error";
			}

		}
		
		unset($option["value"]);

		$str=$this->_setTagAttribute($str,$option);

		$str.=">".$value."</textarea>";

		return $str."\n";
	}

	/**
	 * setRadio
	 */
	public function setRadio($name,$values=null,$option=null){

		$str="";

		if(is_array($values)){

			$ans=$option["value"];
			if($ansa=$this->_requestCheck($name)){
				$ans=$ansa;
			}

			foreach($values as $key=>$textname){

				$opt=$option;

				$opt["value"]=$key;
				$opt["type"]="radio";
				$opt["id"]="radio".$name.$key;

				if(empty($option["fixedValue"])){
					if((string)$ans===(string)$key){
						$opt["checked"]=true;				
					}
				}
				else{
					unset($option["fixedValue"]);
				}

				if(empty($opt["class"])){
					$opt["class"]="form-check-input";
				}
				else{
					$opt["class"].=" form-check-input";
				}
				
				if($this->cssFramework=="bootstrap"){
					// if bootstra....
					$strHead='<div class="form-check form-check-inline">';
					$strFoot='<label for="'.$opt["id"].'" class="form-check-label">'.$textname.'</label></div>';
				}

				$str.=$strHead.$this->setInput($name,$opt).$strFoot;

			}

		}

		return $str;
	}

	/**
	 * setPulldown
	 */
	public function setPulldown($name,$values=null,$option=null){

		$str="<select";

		$option["name"]=$this->_setName($name);
		$ans=$this->_requestCheck($name);

		# css Framework case...
		if($this->cssFramework=="bootstrap"){

			# is bootstrap...

			if(empty($option["class"])){
				$option["class"]="";
			}
			$option["class"].=" form-control";

			$res=$this->_getErrorMessage($name);

			if(!empty($res)){
				$option["class"].=" is-invalid";
			}

		}
		else{

			# is other...

			$res=$this->_getErrorMessage($name);

			if(!empty($res)){
				if(empty($option["class"])){
					$option["class"]="";
				}
				$option["class"].=" is-error";
			}

		}

		$str=$this->_setTagAttribute($str,$option);

		$str.=">\n";

		if(!empty($option["empty"])){
			$str.='<option value="">'.$option["empty"].'</option>'."\n";
			unset($option["empty"]);
		}

		if(empty($option["fixedValue"])){

			if(is_array($values)){
				foreach($values as $key=>$textname){
					$checked="";
					if((string)$ans===(string)$key){
						$checked="selected";
					}
					if(is_array($textname)){

						$str.='<optgroup label="'.$key.'">';

						foreach($textname as $key2=>$textname2){
							$str.='<option value="'.$key2.'">'.$textname2.'</option>';
						}

						$str.='</optgroup>';
					}
					else
					{
						$str.='<option value="'.$key.'" '.$checked.'>'.$textname.'</option>'."\n";
					}
				}

			}
		}
		else{
			unset($option["fixedValue"]);
		}

		$str.="</select>";

		return $str."\n";

	}

	/**
	 * setCheckbox
	 */
	public function setCheckbox($name0,$values=null,$option=null){

		$str="";

		if(!is_array($values)){
			$values=[1=>$values];
			$singles=true;
		}

		$ind=0;
		foreach($values as $key=>$textname){

			$name2=$name0;
			if(empty($singles)){
				$name2=$name0.".".$ind;
			}

			$chkjuge=false;
			if(!empty($option["checked"])){
				if(!empty($singles)){
					if($key==$option["checked"]){
						$chkjuge=true;
						break;
					}
				}
				else
				{
					foreach($option["checked"] as $chk_){
						if($key==$chk_){
							$chkjuge=true;
							break;
						}
					}	
				}
			}

			$ans=$this->_requestCheck($name2,$chkjuge);

			$opt=$option;
			unset($opt["checked"]);

				$opt["value"]=$key;
				$opt["type"]="checkbox";
				$opt["id"]="checkbox".$name2;

				if(empty($option["fixedValue"])){
					if($ans){
						$opt["checked"]=true;
					}
				}
				else{
					unset($option["fixedValue"]);
				}
				
				if(empty($opt["class"])){
					$opt["class"]="form-check-input";
				}
				else{
					$opt["class"].=" form-check-input";
				}


				if($this->cssFramework=="bootstrap"){
					// if bootstra....
					$strHead='<div class="form-check form-check-inline">';
					$strFoot='<label for="'.$opt["id"].'" class="form-check-label">'.$textname.'</label></div>';
				}

				$str.=$strHead.$this->setInput($name2,$opt).$strFoot;

				$ind++;

		}
		
		return $str;

	}

	/**
	 * setToken
	 */
	public function setToken($option=null){

		$url=Request::$params["url"];
		$url=Request::$params["domain"].$url;

		$token=hash("sha256",self::$tokenSalt.$url);
		return $token;
	}

	/**
	 * setTokenHidden
	 */
	public function setTokenHidden($option=null){
		$option["value"]=$this->setToken();
		$option["fixedValue"]=true;
		return $this->setHidden("__token",$option);
	}

	/**
	 * setFile
	 */
	public function setFile($name,$option=null){
		
		$option["type"]="file";
		return $this->setInput($name,$option);

	}

	/**
	 * setButton
	 */
	public function setButton($name,$option=null){

		$str="<input";

		$option["value"]=$name;

		if(empty($option["type"])){
			$option["type"]="submit";
		}

		$str=$this->_setTagAttribute($str,$option);
		
		$str.=">";
		return $str;

	}

	/**
	 * setError
	 */
	public function setError($name,$option=null){

		$str="<div";

		if(empty($option)){
			$option=[];
		}
		
		$option["data-name"]=$name;

		if($this->cssFramework=="bootstrap"){

			if(empty($option["class"])){
				$option["class"]="";
			}
			$option["class"].=" invalid-feedback";

			$res=$this->_getErrorMessage($name);

			if(!empty($res)){
				if(empty($option["style"])){
					$option["style"]="";
				}
				$option["style"].=";display:block;";
			}
		}
		else{

			if(empty($option["class"])){
				$option["class"]="";
			}

			$option["class"].=" error-message";

			$res=$this->_getErrorMessage($name);

			if(!empty($res)){
				if(empty($option["style"])){
					$option["style"]="";
				}
				$option["style"].=";display:block;";
			}
		}

		if(isset($option)){
			foreach($option as $field=>$value){
				$str.=" ".$field.'="'.$value.'"';
			}
		}

		$str.=">";

		if(!empty($res)){
			$str.=$res;
		}

		$str.="</div>";		
		return $str;
	}

	/**
	 * getErrorMessage
	 */
	public function getErrorMessage($name=null){
		if($name){
			return $this->_getErrorMessage($name);
		}
		else
		{
			return self::$errorMessage;
		}
	}

	/**
	 * (private)_requestCheck
	 */
	private function _requestCheck($name,$defValue=null){

		$getData=null;
		if($this->method=="post"){
			if(!empty(Request::$post)){
				$getData=Request::$post;
			}
			else{
				return $defValue;
			}
		}
		else if($this->method=="get"){
			if(!empty(Request::$get)){
				$getData=Request::$get;
			}
			else{
				return $defValue;
			}
		}

		if(!$getData){
			return;
		}

		$names=explode(".",$name);

		$dataType=gettype($getData);

		if(count($names)==1){
			if($dataType=="object"){
				if(isset($getData->{$names[0]})){
					return $getData->{$names[0]};
				}
			}
			else if($dataType=="array"){
				if(isset($getData[$names[0]])){
					return $getData[$names[0]];
				}	
			}
		}
		else if(count($names)==2){
			if($dataType=="object"){
				if(isset($getData->{$names[0]}->{$names[1]})){
					return $getData->{$names[0]}->{$names[1]};
				}
			}
			else if($dataType=="array"){
				if(isset($getData[$names[0]][$names[1]])){
					return $getData[$names[0]][$names[1]];
				}
			}
		}
		else if(count($names)==3){
			if($dataType=="object"){
				if(isset($getData->{$names[0]}->{$names[1]}->{$names[2]})){
					return $getData->{$names[0]}->{$names[1]}->{$names[2]};
				}
			}
			else if($dataType=="array"){
				if(isset($getData[$names[0]][$names[1]][$names[2]])){
					return $getData[$names[0]][$names[1]][$names[2]];
				}	
			}
		}
		else if(count($names)==4){
			if($dataType=="object"){
				if(isset($getData->{$names[0]}->{$names[1]}->{$names[2]}->{$names[3]})){
					return $getData->{$names[0]}->{$names[1]}->{$names[2]}->{$names[3]};
				}
			}
			else if($dataType=="array"){
				if(isset($getData[$names[0]][$names[1]][$names[2]][$names[3]])){
					return $getData[$names[0]][$names[1]][$names[2]][$names[3]];
				}
			}
		}

	}

	/**
	 * (private)_setTagAttribute
	 */
	private function _setTagAttribute($str,$option){

		if(is_array($option)){
			foreach($option as $key=>$value){
				$field=' '.$key.'="'.$value.'"';
				$str.=$field;
			}
		}
		return $str;

	}

	/**
	 * (private)_setName
	 */
	private function _setName($name){
		$names=explode(".",$name);

		$str="";
		foreach($names as $ind=>$n_){
			if($ind){
				$str.="[".$n_."]";
			}
			else{
				$str.=$n_;
			}
		}

		return $str;

	}

	/**
	 * (private)_getErrorMessage
	 */
	private function _getErrorMessage($name){

		$buff=self::$errorMessage;
		$res=null;

		$names=explode(".",$name);

		foreach($names as $n_){
			if(!empty($buff[$n_])){
				$buff=$buff[$n_];
				$res=$buff;
			}
			else
			{
				$res=null;
				break;
			}
		}

		return $res;
	}

}