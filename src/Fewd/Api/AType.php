<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\AThing;
use Fewd\Core\TCore;


abstract class AType extends AThing
{
	// Depth check levels
	public const CHECK_LEVEL_NONE      = 0;
	public const CHECK_LEVEL_MANDATORY = 1;
	public const CHECK_LEVEL_ALL       = 2;

	// Api
	private $_Api;
	public final function Api() : TApi { return $this->_Api; }

	// Name
	private $_Name;
	public final function Name() : string { return $this->_Name; }

	// Sample
	private $_Sample;
	public final function Sample() : mixed { return $this->_Sample; }

	// Default value
	private $_Default;
	public final function Default() : mixed { return $this->_Default; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore  $core,
		TApi   $api,
		string $name,
		mixed  $sample,
		mixed  $default)
	{
		parent::__construct($core);

		$this->_Api     = $api;
		$this->_Name    = $name;
		$this->_Sample  = $sample;
		$this->_Default = $default;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Name    = $this->DefineName();
		$this->_Sample  = $this->DefineSample();
		$this->_Default = $this->DefineDefault();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Name
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineName() : string
	{
		return $this->Name();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Sample
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineSample() : mixed
	{
		return $this->Sample();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Default value
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDefault() : mixed
	{
		return $this->Default();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adapts a given value
	//------------------------------------------------------------------------------------------------------------------
	public function Adapt(mixed &$value)
	{
		return $value;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Raw converts a given value to the current type (without any control)
	//------------------------------------------------------------------------------------------------------------------
	protected function RawConvert(mixed $value) : mixed
	{
		return $value;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Converts a given value to the current type (or empty when conversion is not possible)
	//------------------------------------------------------------------------------------------------------------------
	public final function Convert(mixed $value) : mixed
	{
		if($this->Check($value, true) === '')
		{
			return $this->RawConvert($value);
		}

		return null;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given value complies with the current type (returns an error message if not)
	//------------------------------------------------------------------------------------------------------------------
	public function Check(mixed $value, int $level = self::CHECK_LEVEL_MANDATORY) : string
	{
		$this->Nop($value);
		$this->Nop($level);

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given value complies with the current type
	//------------------------------------------------------------------------------------------------------------------
	public final function IsValid(mixed $value, int $level = self::CHECK_LEVEL_MANDATORY) : bool
	{
		return ($this->Check($value, $level) === '');
	}
}
