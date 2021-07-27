<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\AThing;
use Fewd\Core\TCore;


abstract class AModel extends AThing
{
	// Data
	private $_Data;
	public final function Data() : TData { return $this->_Data; }

	// Database
	private $_Database;
	public final function Database() : ADatabase { return $this->_Database; }

	// Datatables
	private $_Datatables;
	public final function Datatables() : array                  { return $this->_Datatables;              }
	public final function HasDatatable(string $id) : bool       { return isset($this->_Datatables[$id]);  }
	public final function Datatable(   string $id) : TDatatable
	{
		if(isset($this->_Datatables[$id]))
		{
			return $this->_Datatables[$id];
		}

		$res = $this->CreateDatatable($id);

		if($res !== null)
		{
			$this->_Datatables[$id] = $res;
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(ADatabase $database)
	{
		parent::__construct($database->Core());

		$this->_Data     = $database->Data();
		$this->_Database = $database;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a datatable
	//------------------------------------------------------------------------------------------------------------------
	protected function DeclareDatatable(
		string $name,
		array  $fields,
		bool   $isSorted  = false,
		bool   $isManaged = true)
	{
		return $this->Data()->MakeDatatable($this->Database(), $name, $fields, $isSorted, $isManaged);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Creates a datatable after its name
	//------------------------------------------------------------------------------------------------------------------
	protected function CreateDatatable(string $name) : ?TDatatable
	{
		return $this->DeclareDatatable($name, array());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Creates a database query for some table names
	//------------------------------------------------------------------------------------------------------------------
	protected function DatatablesQuery(array $tableNames) : string
	{
		$res = '';
		$sep = '';

		// Drops existing datatables
		foreach($tableNames as $v)
		{
			$datatable = $this->Datatable($v);

			if($datatable !== null)
			{
				$res.= $sep . $datatable->DropQuery() . ';';
				$sep = "\n\n";
			}
		}

		// Creates datatables
		foreach($tableNames as $v)
		{
			$datatable = $this->Datatable($v);

			if($datatable !== null)
			{
				$res.= $sep . $datatable->CreateQuery() . ';';
				$sep = "\n\n";
			}
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a datatabase creation script
	//------------------------------------------------------------------------------------------------------------------
	public function DatabaseQuery() : string
	{
		return '';
	}
}
