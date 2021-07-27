<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\TCore;


class TTypeInteger extends AType
{
	// Minimum
	private $_Minimum;
	public final function Minimum() : ?int { return $this->_Minimum; }

	// Maximum
	private $_Maximum;
	public final function Maximum() : ?int { return $this->_Maximum; }

	// Enumerated values
	private $_Enums;
	public final function Enums()      : array { return $this->_Enums; }
	public final function HasEnum($id) : bool  { return isset($this->_Enums[$id]); }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TApi       $api,
		string     $name,
		?int       $minimum,
		?int       $maximum,
		array      $enums)
	{
		parent::__construct($core, $api, $name);

		$this->_Minimum      = $minimum;
		$this->_Maximum      = $maximum;
		$this->_Enums        = $enums;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Minimum  = $this->DefineMinimum();
		$this->_Maximum  = $this->DefineMaximum();
		$this->_Enums    = $this->DefineEnums();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Minimum
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMinimum() : ?int
	{
		return $this->Minimum();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Maximum
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMaximum() : ?int
	{
		if(($this->Maximum() !== null) && ($this->Minimum() !== null) && ($this->Maximum() < $this->Minimum()))
		{
			return $this->Minimum();
		}

		return $this->Maximum();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Enums
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineEnums() : array
	{
		$res = array();

		foreach($this->Enums() as $v)
		{
			if(is_numeric($v))
			{
				$v = intval($v);
				$res[$v] = $v;
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Raw converts a given value to the current type (without any control)
	//------------------------------------------------------------------------------------------------------------------
	protected function RawConvert(mixed $value) : mixed
	{
		return intval($value);
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

		$value = intval($value);

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

		// Checks enum
		if((count($this->Enums()) > 0) && !$this->HasEnum($value))
		{
			$enumsString = $this->Core()->ArrayToString($this->Enums(), 5, ', ', '...', '{{VALUE}}');

			$res = TApi::ERROR_ENUMS;
			$res = str_replace('{{VALUE}}', $value      , $res);
			$res = str_replace('{{ENUMS}}', $enumsString, $res);

			return $res;
		}

		// OK result
		return '';
	}
}