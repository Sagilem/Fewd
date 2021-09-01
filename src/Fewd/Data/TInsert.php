<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\TCore;


class TInsert extends ASql
{
	// Records
	private $_Records;
	public final function Records() : array       { return $this->_Records;           }
	public       function AddRecord(array $value) { $this->_Records[] = $value;       }
	public       function ClearRecords()          { $this->_Records = array();        }

	// Indicates if multiple records will be inserted in the same query, or one query per record
	private $_IsBulk;
	public final function IsBulk() : bool { return $this->_IsBulk;  }
	public final function BulkOn()        { $this->_IsBulk = true;  }
	public final function BulkOff()       { $this->_IsBulk = false; }



	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		TDatatable $datatable,
		array      $records,
		bool       $isBulk)
	{
		parent::__construct($core, $data, $datatable, '');

		$this->_Records = $records;
		$this->_IsBulk  = $isBulk;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		$records = $this->Records();

		parent::Init();

		foreach($records as $v)
		{
			$this->AddRecord($v);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Clear
	//------------------------------------------------------------------------------------------------------------------
	public function Clear()
	{
		parent::Clear();

		$this->ClearRecords();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with an INSERT statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillInsert(string &$query, array &$bindings, string $indent)
	{
		$this->Nop($bindings);

		// Adds INSERT statement
		$query.= $indent . 'INSERT INTO ' . $this->Database()->Quote($this->Datatable()->Name());

		// Adds fields statement
		$query.= $this->Ret() . $indent . '(';

		$sep = $this->Ret() . $indent . $this->Tab();

		$fields = $this->Datatable()->RealFields();

		foreach($fields as $k => $v)
		{
			$this->Nop($v);

			// If field is an auto-increment :
			// It cannot be inserted manually
			if($this->Datatable()->IsAutoIncrement($k))
			{
				continue;
			}

			// Adds the current field
			$query.= $sep. $this->Database()->Quote($k);

			$sep = ',' . $this->Ret() . $indent . $this->Tab();
		}

		// Adds the VALUES statement
		$query.= $this->Ret() . $indent . ') VALUES';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with values statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillValues(string &$query, array &$bindings, string $indent, array $values)
	{
		$data = $this->Data();

		// Inits statement
		$query.= '(';

		$sep = $this->Ret() . $indent . $this->Tab();

		// For each field :
		$fields = $this->Datatable()->RealFields();

		foreach($fields as $k => $v)
		{
			// If field is an auto-increment :
			// It cannot be inserted manually
			if($this->Datatable()->IsAutoIncrement($k))
			{
				continue;
			}

			// If value was not provided for current field :
			// Uses a default value
			if(isset($values[$k]))
			{
				$value = $values[$k];
			}
			else
			{
				$value = '';
			}

			// Sort field
			if($this->Datatable()->IsSorted() && ($k === $data->FieldSort()))
			{
				$value = $this->Database()->Microtime();
			}

			// Managed fields
			if($this->Datatable()->IsManaged())
			{
				if(($k === $data->FieldCreatedBy()) || ($k === $data->FieldUpdatedBy()))
				{
					$value = $this->Database()->By();
				}
				elseif(($k === $data->FieldCreatedWhen()) || ($k === $data->FieldUpdatedWhen()))
				{
					$value = $this->Database()->When();
				}
			}

			// Converts value to datatype
			$value = $data->Convert($value, $v);

			// Adds value to bindings
			$counter = $this->BindingsCounter();

			$bindings['value_' . $counter] = $value;

			// Adds value to query
			$query.= $sep . ':value_' . $counter;

			// Next
			$sep = ',' . $this->Ret() . $indent . $this->Tab();
		}

		// Ends statement
		$query.= $this->Ret() . $indent . ')';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the query
	//------------------------------------------------------------------------------------------------------------------
	public function Query(array &$bindings, string $indent = '') : string
	{
		$query    = '';
		$bindings = array();

		// If no record found :
		// Does nothing
		if(empty($this->Records()))
		{
			return '';
		}

		// Inits the bindings counter
		$this->ClearBindingsCounter();

		// If insertion is in "bulk mode" :
		// Generates a single global query
		if($this->IsBulk())
		{
			$this->FillInsert($query, $bindings, $indent);

			$sep = $this->Ret() . $indent;

			foreach($this->Records() as $v)
			{
				$query.= $sep;

				$this->FillValues($query, $bindings, $indent, $v);

				$sep = ',' . $this->Ret() . $indent;
			}
		}

		// Otherwise :
		// Generates a single query per record
		else
		{
			$sep = '';

			foreach($this->Records() as $v)
			{
				$query.= $sep;

				$this->FillInsert($query, $bindings, $indent);

				$query.= $this->Ret() . $indent;

				$this->FillValues($query, $vindings, $indent, array($v));

				$sep = ';' . $this->Ret() . $this->Ret();
			}
		}

		// Result
		return $query;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets raw results
	//------------------------------------------------------------------------------------------------------------------
	protected function RawResults() : array
	{
		return array($this->Database()->LastInsertId());
	}
}
