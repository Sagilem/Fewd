<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\TCore;


class TTypeString extends AType
{
	// Minimum length
	private $_Minimum;
	public final function Minimum() : ?int { return $this->_Minimum; }

	// Maximum length
	private $_Maximum;
	public final function Maximum() : ?int { return $this->_Maximum; }

	// Pattern
	private $_Pattern;
	public final function Pattern() : string { return $this->_Pattern; }

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
		string     $pattern,
		array      $enums,
		string     $sample,
		string     $default)
	{
		parent::__construct($core, $api, $name, $sample, $default);

		$this->_Minimum      = $minimum;
		$this->_Maximum      = $maximum;
		$this->_Pattern      = $pattern;
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
		$this->_Pattern  = $this->DefinePattern();
		$this->_Enums    = $this->DefineEnums();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Minimum
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMinimum() : ?int
	{
		if(($this->Minimum() !== null) && ($this->Minimum() < 0))
		{
			return 0;
		}

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
	// Define : Pattern
	//------------------------------------------------------------------------------------------------------------------
	protected function DefinePattern() : string
	{
		return $this->Pattern();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Enums
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineEnums() : array
	{
		$res = array();

		foreach($this->Enums() as $v)
		{
			if(is_numeric($v) || is_string($v))
			{
				$v = strval($v);
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
		return strval($value);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given value complies with the current type (returns an error message if not)
	//------------------------------------------------------------------------------------------------------------------
	public function Check(mixed $value, int $level = self::CHECK_LEVEL_MANDATORY) : string
	{
		// If value is not a string :
		// Error
		if(!is_numeric($value) && !is_string($value))
		{
			return TApi::ERROR_STRING;
		}

		$value = strval($value);

		// If value length is greater than minimum length :
		// Error
		$length = strlen($value);

		if(($this->Minimum() !== null) && ($length < $this->Minimum()))
		{
			$res = TApi::ERROR_MINIMUM_LENGTH;
			$res = str_replace('{{VALUE}}'  , $value          , $res);
			$res = str_replace('{{MINIMUM}}', $this->Minimum(), $res);

			return $res;
		}

		// If value is greater than maximum :
		// Error
		if(($this->Maximum() !== null) && ($value < $this->Maximum()))
		{
			$res = TApi::ERROR_MAXIMUM_LENGTH;
			$res = str_replace('{{VALUE}}'  , $value          , $res);
			$res = str_replace('{{MAXIMUM}}', $this->Maximum(), $res);

			return $res;
		}

		// Checks pattern
		$matches = array();
		if(($this->Pattern() !== '') && !preg_match('#' . $this->Pattern() . '#', $value, $matches))
		{
			$res = TApi::ERROR_PATTERN;
			$res = str_replace('{{PATTERN}}', $this->Pattern(), $res);

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