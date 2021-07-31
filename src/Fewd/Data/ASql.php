<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\AThing;
use Fewd\Core\TCore;


abstract class ASql extends AThing
{
	// Data
	private $_Data;
	public final function Data() : TData { return $this->_Data; }

	// Database
	private $_Database;
	public final function Database() : ADatabase { return $this->_Database; }

	// Datatable
	private $_Datatable;
	public final function Datatable() : TDatatable        { return $this->_Datatable;   }
	public       function SetDatatable(TDatatable $value)
	{
		$this->_Datatable = $value;
		$this->_Database  = $value->Database();
	}

	// Main datatable alias
	private $_Alias;
	public final function Alias() : string        { return $this->_Alias;   }
	public       function SetAlias(string $value) { $this->_Alias = $value; }

	// Bindings counter
	private $_BindingsCounter;
	public final function BindingsCounter() : int { return $this->_BindingsCounter++; }

	// Carriage return
	private $_Ret;
	public final function Ret() : string { return $this->_Ret; }

	// Tab
	private $_Tab;
	public final function Tab() : string { return $this->_Tab; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		TDatatable $datatable,
		string     $alias)
	{
		parent::__construct($core);

		$this->_Data      = $data;
		$this->_Datatable = $datatable;
		$this->_Alias     = $alias;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Database  = $this->Datatable()->Database();

		$this->_Ret       = $this->DefineRet();
		$this->_Tab       = $this->DefineTab();

		$this->SetAlias($this->Alias());

		$this->Clear();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Ret
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineRet() : string
	{
		return "\n";
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Tab
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineTab() : string
	{
		return "\t";
	}


	//------------------------------------------------------------------------------------------------------------------
	// Clears the bindings counter
	//------------------------------------------------------------------------------------------------------------------
	public function ClearBindingsCounter()
	{
		$this->_BindingsCounter = 1;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Clear
	//------------------------------------------------------------------------------------------------------------------
	public function Clear()
	{
		$this->ClearBindingsCounter();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the alias followed by a dot
	//------------------------------------------------------------------------------------------------------------------
	protected function DottedAlias() : string
	{
		if($this->Alias() === '')
		{
			return '';
		}

		return $this->Alias() . '.';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the query
	//------------------------------------------------------------------------------------------------------------------
	public function Query(array &$bindings, string $indent = '') : string
	{
		$this->Nop($bindings);
		$this->Nop($indent  );

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the query (without bindings)
	//------------------------------------------------------------------------------------------------------------------
	public function DirectQuery(string $indent = '') : string
	{
		$labels   = array();
		$values   = array();
		$bindings = array();

		// Gets PDO query
		$res = $this->Query($bindings, $indent);

		// Reverses the bindings array
		// (to avoid having ":value_10" replaced by ":value_1")
		$bindings = array_reverse($bindings);

		// Prepares the replacements
		foreach($bindings as $k => $v)
		{
			$labels[] = ':' . $k;
			$values[] = $this->Database()->StringQuote($v);
		}

		// Applies the replacements
		$res = str_replace($labels, $values, $res);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Raw runs the query
	//------------------------------------------------------------------------------------------------------------------
	protected function RawRun(string $query, array $bindings) : string
	{
		return $this->Database()->Run($query, $bindings);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets raw results
	//------------------------------------------------------------------------------------------------------------------
	protected function RawResults() : array
	{
		return $this->Database()->LastResults();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs the query
	//------------------------------------------------------------------------------------------------------------------
	public function Run() : array|string
	{
		$bindings = array();
		$query    = $this->Query($bindings, '');

		$res = $this->RawRun($query, $bindings);

		if($res === '')
		{
			return $this->RawResults();
		}

		return $res;
	}
}
