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
		?float     $maximum,
		float      $sample,
		float      $default)
	{
		parent::__construct($core, $api, $name, $sample, $default);

		$this->_Minimum      = $minimum;
		$this->_Maximum      = $maximum;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		$this->_Minimum  = $this->DefineMinimum();
		$this->_Maximum  = $this->DefineMaximum();

		parent::Init();
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
	// Define : Sample
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineSample() : mixed
	{
		$res = parent::DefineSample();

		if(($this->Minimum() !== null) && ($res < $this->Minimum()))
		{
			$res = $this->Minimum();
		}

		if(($this->Maximum() !== null) && ($res > $this->Maximum()))
		{
			$res = $this->Maximum();
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Default
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDefault() : mixed
	{
		$res = parent::DefineDefault();

		if(($this->Minimum() !== null) && ($res < $this->Minimum()))
		{
			$res = $this->Minimum();
		}

		if(($this->Maximum() !== null) && ($res > $this->Maximum()))
		{
			$res = $this->Maximum();
		}

		return $res;
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
	public function Check(mixed $value, int $level = self::CHECK_LEVEL_MANDATORY) : string
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