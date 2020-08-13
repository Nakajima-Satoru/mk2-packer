<?php

/**
 * 
 * [mk2 standard packer]
 * UpdatePacker
 * 
 * Packer for executing the update process.
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Packer;

class UpdatePacker extends Packer{

	const UPDATOR_PATH=MK2_PATH_APP."Updator/";
	const NOWVERSION_FILE="../.version";

	private $nowVersion=null;

	public function update($installMode=false){

		$newVersion=\mk2\core\Config::get("version");
	
		$this->nowVersion=null;
		if(!$installMode){
			if(file_exists(self::NOWVERSION_FILE)){
				$this->nowVersion=file_get_contents(self::NOWVERSION_FILE);
			}
		}
	
		$result=null;
		if(version_compare($this->nowVersion,$newVersion,"<")){
	
			if(file_exists(self::UPDATOR_PATH."Updator.php")){
				$result=include(self::UPDATOR_PATH."Updator.php");
			}
	
			file_put_contents(self::NOWVERSION_FILE,$newVersion);
	
		}
		
		return $result;
	
	}
	
	public function install(){
		return $this->update(true);
	}

	public function versionCheck($targetVersion=null){
			
		if(!$targetVersion){
			$targetVersion=\mk2\core\Config::get("version");
		}
		if(!$this->nowVersion){
			if(file_exists(self::NOWVERSION_FILE)){
				$this->nowVersion=file_get_contents(self::NOWVERSION_FILE);
			}
		}

		if(version_compare($this->nowVersion,$targetVersion,"<")){
			return true;
		}

		return false;
	}

	public function getVersions(){

		$result=$this->versionCheck();

		$newVersion=\mk2\core\Config::get("version");

		return [
			"newVersion"=>$newVersion,
			"nowVersion"=>$this->nowVersion,
			"result"=>$result,
		];

	}

}