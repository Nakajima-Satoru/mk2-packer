<?php

/*

- mk2 standard packer -

FormPacker

For form tag generation.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class FormPacker extends Packer{

	public static $tokenSalt="4049far3afjie7ep02Aar09f0g098a6r005hjFuf";
	public static $errorMessage=[];

	public function setErrors($data){
		self::$errorMessage=$data;
	}

	public function setToken($tokenSalt){
		self::$tokenSalt=$tokenSalt;				
		return $this;
	}
	public function verify(){
		
		return false;

	}
}
class FormPackerUI extends FormPacker{

	private $method="post";
	private $cssFramework=null;

	# start

	public function start($option=null){
		$str='<form';

		if(empty($option["method"])){
			$option["method"]="post";
		}
		$this->method=$option["method"];
		if(!empty($option["cssFramework"])){
			$this->cssFramework=$option["cssFramework"];
		}

		$str=$this->_setTagAttribute($str,$option);

		$str.=">";
		return $str."\n";
	}

	# end

	public function end(){

		$this->cssFramework=null;
		return "</form>";

	}

	# set Input

	public function setInput($name,$option=null){
		$str='<input';

		$option["name"]=$name;
		if(empty($option["type"])){
			$option["type"]="text";
		}

		if($option["type"]!="radio" && $option["type"]!="checkbox"){
			if($this->_requestCheck($name)){
				$option["value"]=$this->_requestCheck($name);
			}
		}

		# css Framework case...
		if($this->cssFramework=="bootstrap"){

			if($option["type"]!="radio" && $option["type"]!="checkbox"){

				# is bootstrap...

				if(empty($option["class"])){
					$option["class"]="";
				}
				$option["class"].=" form-control";

				if(!empty(self::$errorMessage[$name])){
					$option["class"].=" is-invalid";
				}

			}

		}
		else{

			# is other...

			if(!empty(self::$errorMessage[$name])){
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
	# set Hidden

	public function setHidden($name,$option=null){
		$option["type"]="hidden";
		return $this->setInput($name,$option);
	}

	# set Textarea

	public function setTextarea($name,$option=null){
		$str='<textarea';

		$option["value"]=$this->_requestCheck($name);

		$value="";
		if(!empty($option["value"])){
			$value=$option["value"];
			unset($option["value"]);
		}

		$option["name"]=$name;

		# css Framework case...
		if($this->cssFramework=="bootstrap"){

			# is bootstrap...
			if(empty($option["class"])){
				$option["class"]="";
			}

			$option["class"].=" form-control";

			if(!empty(self::$errorMessage[$name])){
				$option["class"].=" is-invalid";
			}
			
		}
		else{

			# is other...

			if(!empty(self::$errorMessage[$name])){
				if(empty($option["class"])){
					$option["class"]="";
				}
				$option["class"].=" is-error";
			}

		}
		

		$str=$this->_setTagAttribute($str,$option);

		$str.=">".$value."</textarea>";

		return $str."\n";
	}

	# set Radio

	public function setRadio($name,$values=null,$option=null){

		$str="";

		if(is_array($values)){

			$ans=$this->_requestCheck($name);

			foreach($values as $key=>$textname){

				$opt=$option;

				$opt["value"]=$key;
				$opt["type"]="radio";
				$opt["id"]="radio".$name.$key;
				if($ans==$key){
					$opt["checked"]=true;				
				}
				$str.='<div class="radio">';
				$str.=$this->setInput($name,$opt);
				$str.='<label for="'.$opt["id"].'">'.$textname.'</label>';
				$str.="</div>";

			}

		}

		return $str;
	}

	# set Pulldown

	public function setPulldown($name,$values=null,$option=null){

		$str="<select";

		$option["name"]=$name;
		$ans=$this->_requestCheck($name);

		# css Framework case...
		if($this->cssFramework=="bootstrap"){

			# is bootstrap...

			if(empty($option["class"])){
				$option["class"]="";
			}
			$option["class"].=" form-control";

			if(!empty(self::$errorMessage[$name])){
				$option["class"].=" is-invalid";
			}

		}
		else{

			# is other...

			if(!empty(self::$errorMessage[$name])){
				if(empty($option["class"])){
					$option["class"]="";
				}
				$option["class"].=" is-error";
			}

		}

		$str=$this->_setTagAttribute($str,$option);

		$str.=">\n";

		if(is_array($values)){

			foreach($values as $key=>$textname){
				$checked="";
				if($ans==$key){
					$checked="selected";
				}
				$str.='<option value="'.$key.'" '.$checked.'>'.$textname.'</option>'."\n";
			}

		}
		$str.="</select>";

		return $str."\n";

	}

	# setCheckbox

	public function setCheckbox($name0,$values=null,$option=null){

		$str="";

		if(is_array($values)){

			$ind=0;
			foreach($values as $key=>$textname){

				$name1=$name0."[".$ind."]";
				$name2=$name0.".".$ind;

				$ans=$this->_requestCheck($name2);

				$opt=$option;
				$opt["value"]=$key;
				$opt["type"]="checkbox";
				$opt["id"]="checkbox".$name2;
				if($ans==$key){
					$opt["checked"]=true;				
				}
				$str.='<div class="checkbox">';
				$str.=$this->setInput($name1,$opt);
				$str.='<label for="'.$opt["id"].'">'.$textname.'</label>';
				$str.='</div>';
				$ind++;
			}

		}
		
		return $str;

	}
	
	# setToken

	public function setToken($option=null){

	}

	# setFile

	public function setFile($name,$option=null){
		
		$option["type"]="file";
		return $this->setInput($name,$option);

	}

	# setButton

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

	# setError

	public function setError($name,$option=null){

		$str="";

		if($this->cssFramework=="bootstrap"){
			$str.='<div class="invalid-feedback" style="display:block">';
		}
		else{
			$str.='<div class="error-message">';
		}

		if(!empty(self::$errorMessage[$name])){
			$str.=self::$errorMessage[$name];
		}

		$str.="</div>";		
		return $str;
	}

	private function _requestCheck($name){

		$getData=null;
		if($this->method=="post"){
			if(!empty(Request::$post)){
				$getData=Request::$post;
			}		
		}
		else if($this->method=="get"){
			if(!empty(Request::$get)){
				$getData=Request::$get;
			}
		}

		if(!$getData){
			return;
		}

		$names=explode(".",$name);

		$dataType=gettype($getData);

		if(count($names)==1){
			if($dataType=="object"){
				if(!empty($getData->{$names[0]})){
					return $getData->{$names[0]};
				}	
			}
			else if($dataType=="array"){
				if(!empty($getData[$names[0]])){
					return $getData[$names[0]];
				}	
			}
		}
		else if(count($names)==2){
			if($dataType=="object"){
				if(!empty($getData->{$names[0]}->{$names[1]})){
					return $getData->{$names[0]}->{$names[1]};
				}
			}
			else if($dataType=="array"){
				if(!empty($getData[$names[0]][$names[1]])){
					return $getData[$names[0]][$names[1]];
				}
			}
		}
		else if(count($names)==3){
			if($dataType=="object"){
				if(!empty($getData->{$names[0]}->{$names[1]}->{$names[2]})){
					return $getData->{$names[0]}->{$names[1]}->{$names[2]};
				}
			}
			else if($dataType=="array"){
				if(!empty($getData[$names[0]][$names[1]][$names[2]])){
					return $getData[$names[0]][$names[1]][$names[2]];
				}	
			}
		}
		else if(count($names)==4){
			if($dataType=="object"){
				if(!empty($getData->{$names[0]}->{$names[1]}->{$names[2]}->{$names[3]})){
					return $getData->{$names[0]}->{$names[1]}->{$names[2]}->{$names[3]};
				}
			}
			else if($dataType=="array"){
				if(!empty($getData[$names[0]][$names[1]][$names[2]][$names[3]])){
					return $getData[$names[0]][$names[1]][$names[2]][$names[3]];
				}
			}
		}

	}

	private function _setTagAttribute($str,$option){

		if(is_array($option)){
			foreach($option as $key=>$value){
				$field=' '.$key.'="'.$value.'"';
				$str.=$field;
			}
		}
		return $str;

	}
}