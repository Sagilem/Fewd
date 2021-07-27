<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\TCore;


class TOne extends TSelect
{
	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		TDatatable $datatable,
		string     $alias,
		array      $fields,
		array      $conditions)
	{
		parent::__construct(
			$core,
			$data,
			$datatable,
			$alias,
			$fields,
			$conditions,
			array(),
			array(),
			array(),
			0,
			1,
			false,
			false);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets raw results
	//------------------------------------------------------------------------------------------------------------------
	protected function RawResults() : array
	{
		foreach($this->Database()->LastResults() as $v)
		{
			return $v;
		}

		return array();
	}
}
