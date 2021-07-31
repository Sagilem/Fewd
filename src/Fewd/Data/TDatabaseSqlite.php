<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\TCore;


class TDatabaseSqlite extends ADatabase
{
	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		string     $name,
		string     $host)
	{
		parent::__construct($core, $data, $name, $host, '', '', '');
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
		return 'sqlite';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Auto-increment statement
	//------------------------------------------------------------------------------------------------------------------
	public function AutoIncrementStatement() : string
	{
		return 'AUTOINCREMENT';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Datatype statement
	//------------------------------------------------------------------------------------------------------------------
	public function DatatypeStatement(string $datatype) : string
	{
		$datatype = $this->Data()->Datatype($datatype);

		switch($datatype)
		{
			case TData::DATATYPE_ID         : return 'INT';
			case TData::DATATYPE_NUMBER     : return 'INTEGER';
			case TData::DATATYPE_FLOAT      : return 'REAL';
		}

		return 'TEXT';
	}


	//------------------------------------------------------------------------------------------------------------------
	// TRUNCATE TABLE statement
	//------------------------------------------------------------------------------------------------------------------
	public function TruncateTableStatement(string $tableName) : string
	{
		return 'DELETE FROM ' . $this->quote($tableName) . '; VACUUM;';
	}
}