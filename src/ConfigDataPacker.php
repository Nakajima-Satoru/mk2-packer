<?php

/**
 * 
 * [mk2 standard packer]
 * ConfigDataPacker
 * 
 * For shared data management.
 * Copylight (C) Nakajima Satoru 2020.
 * URL:https://www.mk2-php.com/
 *
 */

namespace mk2\packer;

use mk2\core\Packer;

class ConfigDataPacker extends Packer{

	# dbTable : Database table information that centrally manages setting data is set here

	public $cfdName="CFD";

	public $dbTable=[
		"table"=>"Systemdata",
		"name"=>"name",
		"value"=>"value",
	];

	public $usePackerClass=[
		"Cache"=>[
			"Cache"=>[
				"mode"=>"file",
				"name"=>"RCACHECONFIG",
				"encrypt"=>[
					"encType"=>"aes-256-cbc",
					"hashNumber"=>"Euf9ar0e98ga9ae9aifaah;o43ijIPFIE9g8a-0erajEPOEIRga8970434j;loDIRoeigGGE",
					"password"=>"SieroiDIorjepga098423jGaEi098fa6f51ji2904jfapreaijg9044j5io598f0a7099gj",
				],
			],
		],
	];

	/**
	 * __construct
	 */
	public function __construct(){
		parent::__construct();

		$this->setPacker([
			$this->getUsePackerClass("Cache"),
		]);

	}

	/**
	 * read
	 */
	public function read($name=null){

		$buff=$this->Packer->{$this->getUsePackerClass("Cache")}->buffering($this->cfdName,0,function(){

			// model setting
			$table=$this->dbTable["table"];

			$this->setTable($table);
			
			$res=$this->Table->{$table}->select([
				"type"=>"list",
				"fields"=>[$this->dbTable["name"],$this->dbTable["value"]],
			]);

			return $res;
		});

		if($name){
			if(!empty($buff[$name])){
				return $buff[$name];
			}
		}
		else
		{
			return $buff;
		}

	}

	/**
	 * write
	 */
	public function write($data){

		// model setting
		$table=$this->dbTable["table"];

		$this->setTable($table);

		try{

			$saveObj=$this->Table->{$table}->save()->tsBegin();

			foreach($data as $key=>$value){

				//exist recode check
				$check=$this->Table->{$table}->select([
					"type"=>"first",
					"where"=>[
						[$this->dbTable["name"],$key],
					],
					"fields"=>[$this->Table->{$table}->primaryKey,$this->dbTable["value"]],
				]);

				// If the same content has already been registered ...
				if(!empty($check->{$this->dbTable["value"]})){
					if($check->{$this->dbTable["value"]}==$value){
						continue;
					}
				}

				$saveData=[
					$this->dbTable["name"]=>$key,
					$this->dbTable["value"]=>$value,
				];

				if(!empty($check->{$this->Table->{$table}->primaryKey})){
					$saveData[$this->Table->{$table}->primaryKey]=$check->{$this->Table->{$table}->primaryKey};
				}

				$saveObj->save($saveData);

			}

			$saveObj->tsCommit();

		}catch(\Exception $e){
			$saveObj->tsRollback();
			return false;
		}

		// buffering refresh allow
		$this->Packer->{$this->getUsePackerClass("Cache")}->bufferingAllow($this->cfdName);

		return $this->read();
	}

	/**
	 * refresh
	 */
	public function refresh(){

		// buffering refresh allow
		$this->Packer->{$this->getUsePackerClass("Cache")}->bufferingAllow($this->cfdName);
		return $this->read();

	}

	/**
	 * allClear
	 */
	public function allClear(){

		// model setting
		$table=$this->dbTable["table"];

		$this->setTable($table);

		$this->Table->{$table}->delete()->allDel();

		// cache data delete
		$this->Packer->{$this->getUsePackerClass("Cache")}->delete($this->cfdName);

	}

	private function getUsePackerClass($name){

		$buff=$this->usePackerClass[$name];

		if(is_array($buff)){
			return key($buff);
		}
		else{
			return $buff;
		}

	}

}