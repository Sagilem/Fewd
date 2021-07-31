<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\AThing;
use Fewd\Core\TCore;


class TDatatable extends AThing
{
	// Data
	private $_Data;
	public final function Data() : TData { return $this->_Data; }

	// Database
	private $_Database;
	public final function Database() : ADatabase { return $this->_Database; }

	// Name
	private $_Name;
	public final function Name() : string { return $this->_Name; }

	// Fields
	private $_Fields;
	public final function Fields()                  : array           { return $this->_Fields;                }
	public final function Field(      string $name) : string          { return $this->_Fields[$name] ?? null; }
	public final function HasField(   string $name) : bool            { return isset($this->_Fields[$name]);  }
	public       function AddField(   string $name, string $datatype) { $this->_Fields[$name] = $datatype;    }
	public       function RemoveField(string $name)                   { unset($this->_Fields[$name]);         }

	// Keys
	private $_Keys;
	public final function Keys()                  : array           { return $this->_Keys;                }
	public final function Key(      string $name) : string          { return $this->_Keys[$name] ?? null; }
	public final function HasKey(   string $name) : bool            { return isset($this->_Keys[$name]);  }
	public       function AddKey(   string $name, string $datatype)
	{
		$this->_Keys[$name] = $datatype;
		$this->AddField($name, $datatype);
	}
	public       function RemoveKey(string $name)                   { unset($this->_Keys[$name]);         }

	// Fulltext indexes
	private $_Fulltexts;
	public final function Fulltexts()                  : array  { return $this->_Fulltexts;                }
	public final function Fulltext(      string $name) : string { return $this->_Fulltexts[$name] ?? null; }
	public final function HasFulltext(   string $name) : bool   { return isset($this->_Fulltexts[$name]);  }
	public       function AddFulltext(   string $name)          { $this->_Fulltexts[$name] = $name;        }
	public       function RemoveFulltext(string $name)          { unset($this->_Fulltexts[$name]);         }

	// Records that must always be present in the database (no deletion is possible on them)
	// DefaultRecord
	private $_DefaultRecords;
	public function DefaultRecords() : array       { return $this->_DefaultRecords;     }
	public function AddDefaultRecord(array $value) { $this->_DefaultRecords[] = $value; }

	// Indicates if a SORT field is automatically set
	private $_IsSorted;
	public final function IsSorted() : bool { return $this->_IsSorted;  }
	public final function SortOn()          { $this->_IsSorted = true;  }
	public final function SortOff()         { $this->_IsSorted = false; }

	// Indicates if some CREATED/UPDATED BY/WHEN fields are automatically set
	private $_IsManaged;
	public final function IsManaged() : bool { return $this->_IsManaged;  }
	public final function ManagementOn()     { $this->_IsManaged = true;  }
	public final function ManagementOff()    { $this->_IsManaged = false; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		ADatabase  $database,
		string     $name,
		array      $fields,
		bool       $isSorted,
		bool       $isManaged)
	{
		parent::__construct($core);

		$this->_Data      = $data;
		$this->_Database  = $database;
		$this->_Name      = $name;
		$this->_Fields    = $fields;
		$this->_IsSorted  = $isSorted;
		$this->_IsManaged = $isManaged;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$fields = $this->Fields();

		$this->_Name           = $this->DefineName();
		$this->_Keys           = $this->DefineKeys();
		$this->_Fields         = $this->DefineFields();
		$this->_Fulltexts      = $this->DefineFulltexts();
		$this->_DefaultRecords = $this->DefineDefaultRecords();
		$this->_IsSorted       = $this->DefineIsSorted();
		$this->_IsManaged      = $this->DefineIsManaged();

		$this->AddFields($fields);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Name
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineName() : string
	{
		return $this->Name();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Keys
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineKeys() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Fields
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineFields() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Fulltexts
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineFulltexts() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : DefaultRecords
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDefaultRecords() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : IsSorted
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineIsSorted() : bool
	{
		return $this->IsSorted();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : IsManaged
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineIsManaged() : bool
	{
		return $this->IsManaged();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds an array of fields
	//------------------------------------------------------------------------------------------------------------------
	public function AddFields(array $fields)
	{
		foreach($fields as $k => $v)
		{
			if(substr($k, -1) === '*')
			{
				$this->AddKey(substr($k, 0, -1), $v);
			}
			else
			{
				$this->AddField($k, $v);
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given field is an auto-increment key
	//------------------------------------------------------------------------------------------------------------------
	public function IsAutoIncrement(string $field) : bool
	{
		// Field must be a key
		if(!$this->HasKey($field))
		{
			return false;
		}

		// Field must be a code
		if($this->Field($field) !== TData::DATATYPE_ID)
		{
			return false;
		}

		// Field must be the only key
		return (count($this->Keys()) === 1);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the complete array of fields (including special fields like SORT, CREATED BY, ...)
	//------------------------------------------------------------------------------------------------------------------
	public function AllFields() : array
	{
		$data = $this->Data();

		$res = $this->Fields();

		if($this->IsSorted())
		{
			$res[$data->FieldSort()] = TData::DATATYPE_SORT;
		}

		if($this->IsManaged())
		{
			$res[$data->FieldCreatedBy()  ] = TData::DATATYPE_CODE;
			$res[$data->FieldCreatedWhen()] = TData::DATATYPE_DATETIME;
			$res[$data->FieldUpdatedBy()  ] = TData::DATATYPE_CODE;
			$res[$data->FieldUpdatedWhen()] = TData::DATATYPE_DATETIME;
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// CREATE TABLE query
	//------------------------------------------------------------------------------------------------------------------
	public function CreateQuery() : string
	{
		return $this->Database()->CreateTableStatement(
			$this->Name(),
			$this->Keys(),
			$this->AllFields(),
			$this->Fulltexts());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs CREATE TABLE query
	//------------------------------------------------------------------------------------------------------------------
	public function Create() : string
	{
		return $this->Database()->Run($this->CreateQuery());
	}


	//------------------------------------------------------------------------------------------------------------------
	// DROP TABLE query
	//------------------------------------------------------------------------------------------------------------------
	public function DropQuery() : string
	{
		return $this->Database()->DropTableStatement($this->Name());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs DROP TABLE query
	//------------------------------------------------------------------------------------------------------------------
	public function Drop() : string
	{
		return $this->Database()->Run($this->DropQuery());
	}


	//------------------------------------------------------------------------------------------------------------------
	// TRUNCATE TABLE query
	//------------------------------------------------------------------------------------------------------------------
	public function TruncateQuery() : string
	{
		return $this->Database()->TruncateTableStatement($this->Name());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs TRUNCATE TABLE query
	//------------------------------------------------------------------------------------------------------------------
	public function Truncate() : string
	{
		return $this->Database()->Run($this->TruncateQuery());
	}
}
