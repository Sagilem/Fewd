<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\TCore;


class TDelete extends AConditionSql
{
	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		TDatatable $datatable,
		array      $conditions)
	{
		parent::__construct($core, $data, $datatable, '', $conditions);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a DELETE statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillDelete(string &$query, array &$bindings, string $indent)
	{
		$this->Nop($bindings);

		$query = $indent . $this->Database()->DeleteStatement($this->Datatable()->Name()) . ' ' . $this->Alias();
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
		$this->FillDelete( $query, $bindings, $indent);
		$this->FillWhere(  $query, $bindings, $indent);

		// Result
		return $query;
	}
}
