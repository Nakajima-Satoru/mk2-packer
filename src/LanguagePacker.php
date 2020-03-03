<?php

/*

- mk2 standard packer -

LanguagePacker

Language-specific packer.

Copylight (C) Nakajima Satoru 2020.

*/

namespace mk2\core;

class LanguagePacker extends Packer{

	public static $_lang="en";

	public static $_languageList=[];

	public static $_dataBuffer=[];

	public $languageList=[
		"en"=>"English",
	];

	public $defaultLanguage="en";

	/**
	 * __construct
	 */
	public function __construct($option){
		parent::__construct($option);

		$this->setPacker([
			"Session",
		]);

		self::$_languageList=$this->languageList;

		if($nowLang=$this->Packer->Session->read("_language")){

			if(!empty(self::$_languageList[$nowLang])){
				self::$_lang=$nowLang;
			}
			else
			{
				self::$_lang=$this->defaultLanguage;
				$this->Packer->Session->write("_language",$this->defaultLanguage);
				$this->Packer->Session->write("_languageName",self::$_languageList[$this->defaultLanguage]);
			}
		}
		else{
			self::$_lang=$this->defaultLanguage;
			$this->Packer->Session->write("_language",$this->defaultLanguage);
			$this->Packer->Session->write("_languageName",self::$_languageList[$this->defaultLanguage]);
		}

	}

	/**
	 * getLanguageList
	 */
	public function getLanguageList(){
		return self::$_languageList;
	}

	/**
	 * getLanguage
	 */
	public function getLanguage(){
		return self::$_lang;
	}

	/**
	 * getLanguageName
	 */
	public function getLanguageName(){
		return self::$_languageList[self::$_lang];
	}

	/**
	 * setLanguage
	 */
	public function setLanguage($lang){
		if(!empty(self::$_languageList[$lang])){
			self::$_lang=$lang;
			$this->Packer->Session->write("_language",$lang);
			$this->Packer->Session->write("_languageName",self::$_languageList[$lang]);
		}
		return $this;
	}

	/**
	 * setLanguageList
	 */
	public function setLanguageList($list){
		self::$_languageList=$list;
		return $this;
	}

	/**
	 * text
	 */
	public function text($name1,$name2=null){

		if($name2){
			$sectionName=$name1;
			$name=$name2;
			$path=MK2_PATH_APP."AppConf/Language/".$sectionName.".".$this->getLanguage().".php";
		}
		else
		{
			$sectionName="_def";
			$name=$name1;
			$path=MK2_PATH_APP."AppConf/Language/".$this->getLanguage().".php";
		}

		if(file_exists($path)){
			if(empty(self::$_dataBuffer[$sectionName])){
				self::$_dataBuffer[$sectionName]=include($path);
			}

			if(!empty(self::$_dataBuffer[$sectionName][$name])){
				return self::$_dataBuffer[$sectionName][$name];
			}
		}

	}

	/**
	 * getViewPart
	 */
	public function getViewPart($path,$language=null){

		if($language){
			$path=$path.".".$language;
		}
		else
		{
			$path=$path.".".$this->getLanguage();
		}

		return parent::getViewPart($path);

	}
}
class LanguagePackerUI extends LanguagePacker{

	/**
	 * getViewPart
	 */
	public function getViewPart($path,$language=null,$oBuff=false){

		if($oBuff){
			return parent::getViewPart($path,$language);
		}
		else{
			echo parent::getViewPart($path,$language);
		}
	}

	/**
	 * set
	 */
	public function set($name,$value){
		parent::set($name,$value);
	}

}