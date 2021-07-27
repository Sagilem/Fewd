<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\TCore;


class TDatabaseMysql extends ADatabase
{


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		string     $name,
		string     $host,
		string     $port,
		string     $user,
		string     $pass)
	{
		parent::__construct($core, $data, $name, $host, $port, $user, $pass);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Driver
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDriver() : string
	{
		return 'mysql';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Connects database (returns a potential error message)
	//------------------------------------------------------------------------------------------------------------------
	public function Connect() : string
	{
		$res = parent::Connect();

		if($res === '')
		{
			// With MySQL, communication between PHP and MySQL must be explicitely done with UTF-8
			$this->Handle()->exec('SET NAMES  \'utf8\'');
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// ENGINE statement
	//------------------------------------------------------------------------------------------------------------------
	public function EngineStatement($fulltexts = array()) : string
	{
		// Determines engine :
		// - Tables having fulltext fields are MyISAM
		// - Otherwise InnoDB (to be able to open transactions on it)
		$engine = 'MyISAM';

		if(empty($fulltexts))
		{
			$engine = 'InnoDB';
		}

		// Adds engine name to statement
		$res = "\n" . 'ENGINE=' . $engine;

		// Adds datatable encoding (UTF-8)
		$res.= ' DEFAULT CHARACTER SET = utf8';
		$res.= ' COLLATE = utf8_unicode_ci';

		// Result
		return $res;
	}
}
