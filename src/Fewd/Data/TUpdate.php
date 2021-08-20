<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\TCore;


class TUpdate extends AConditionSql
{
	// Values
	private $_Values;
	public final function Values()                : array       { return $this->_Values;              }
	public final function Value(      string $id) : mixed       { return $this->_Values[$id] ?? null; }
	public final function HasValue(   string $id) : bool        { return isset($this->_Values[$id]);  }
	public       function AddValue(   string $id, mixed $value) { $this->_Values[$id] = $value;       }
	public       function RemoveValue(string $id)               { unset($this->_Values[$id]);         }
	public       function ClearValues()                         { $this->_Values = array();           }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		TDatatable $datatable,
		array      $values,
		array      $conditions)
	{
		parent::__construct($core, $data, $datatable, '', $conditions);

		$this->_Values = $values;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		$values = $this->Values();

		parent::Init();

		foreach($values as $k => $v)
		{
			$this->AddValue($k, $v);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Clear
	//------------------------------------------------------------------------------------------------------------------
	public function Clear()
	{
		parent::Clear();

		$this->ClearValues();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with an UPDATE statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillUpdate(string &$query, array &$bindings, string $indent)
	{
		$data = $this->Data();

		// Adapts values
		$values = $this->Values();

		if($this->Datatable()->IsManaged())
		{
			unset($values[$data->FieldCreatedBy()  ]);
			unset($values[$data->FieldCreatedWhen()]);

			$values[$data->FieldUpdatedBy()  ] = $this->Database()->By();
			$values[$data->FieldUpdatedWhen()] = $this->Database()->When();
		}

		// Keys cannot be updated
		foreach($this->Datatable()->Keys() as $k => $v)
		{
			unset($values[$k]);
		}

		// Adds UPDATE statement
		$query.= $indent . 'UPDATE ' . $this->Database()->Quote($this->Datatable()->Name()) . ' SET';

		// For each datatable field :
		$sep = $this->Ret() . $indent . $this->Tab();

		$fields = $this->Datatable()->RealFields();

		foreach($fields as $k => $v)
		{
			// If a value is defined for current field :
			if(isset($values[$k]))
			{
				// Adds statement
				$counter = $this->BindingsCounter();

				$query.= $sep . $this->Database()->Quote($k) . ' = :value_' . $counter;

				// Adds value to bindings array
				$value = $this->Data()->Convert($values[$k], $v);

				$bindings['value_' . $counter] = $value;

				// Next
				$sep = ',' . $this->Ret() . $indent . $this->Tab();
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the query
	//------------------------------------------------------------------------------------------------------------------
	public function Query(array &$bindings, string $indent = '') : string
	{
		$query    = '';
		$bindings = array();

		// Inits the bindings counter
		$this->ClearBindingsCounter();

		// Adds statements
		$this->FillUpdate($query, $bindings, $indent);
		$this->FillWhere( $query, $bindings, $indent);

		// Result
		return $query;
	}
}
