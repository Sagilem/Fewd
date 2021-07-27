<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\TCore;


class TTypeFloat extends AType
{
	// Minimum
	private $_Minimum;
	public final function Minimum() : ?float { return $this->_Minimum; }

	// Maximum
	private $_Maximum;
	public final function Maximum() : ?float { return $this->_Maximum; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TApi       $api,
		string     $name,
		?float     $minimum,
		?float     $maximum)
	{
		parent::__construct($core, $api, $name);

		$this->_Minimum      = $minimum;
		$this->_Maximum      = $maximum;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Minimum  = $this->DefineMinimum();
		$this->_Maximum  = $this->DefineMaximum();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Minimum
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMinimum() : ?float
	{
		return $this->Minimum();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Maximum
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMaximum() : ?float
	{
		if(($this->Maximum() !== null) && ($this->Minimum() !== null) && ($this->Maximum() < $this->Minimum()))
		{
			return $this->Minimum();
		}

		return $this->Maximum();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Raw converts a given value to the current type (without any control)
	//------------------------------------------------------------------------------------------------------------------
	protected function RawConvert(mixed $value): mixed
	{
		return floatval($value);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given value complies with the current type (returns an error message if not)
	//------------------------------------------------------------------------------------------------------------------
	public function Check(mixed $value) : string
	{
		// If value is not numeric :
		// Error
		if(!is_numeric($value))
		{
			return TApi::ERROR_NUMERIC;
		}

		$value = floatval($value);

		// If value is greater than minimum :
		// Error
		if(($this->Minimum() !== null) && ($value < $this->Minimum()))
		{
			$res = TApi::ERROR_MINIMUM;
			$res = str_replace('{{VALUE}}'  , $value          , $res);
			$res = str_replace('{{MINIMUM}}', $this->Minimum(), $res);

			return $res;
		}

		// If value is greater than maximum :
		// Error
		if(($this->Maximum() !== null) && ($value < $this->Maximum()))
		{
			$res = TApi::ERROR_MAXIMUM;
			$res = str_replace('{{VALUE}}'  , $value          , $res);
			$res = str_replace('{{MAXIMUM}}', $this->Maximum(), $res);

			return $res;
		}

		// OK result
		return '';
	}
}