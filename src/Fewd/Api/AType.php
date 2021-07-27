<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\AThing;
use Fewd\Core\TCore;


abstract class AType extends AThing
{
	// Api
	private $_Api;
	public final function Api() : TApi { return $this->_Api; }

	// Name
	private $_Name;
	public final function Name() : string { return $this->_Name; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore  $core,
		TApi   $api,
		string $name)
	{
		parent::__construct($core);

		$this->_Api  = $api;
		$this->_Name = $name;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Name = $this->DefineName();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Name
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineName() : string
	{
		return $this->Name();
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
		if($this->Check($value) === '')
		{
			return $this->RawConvert($value);
		}

		return null;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given value complies with the current type (returns an error message if not)
	//------------------------------------------------------------------------------------------------------------------
	public function Check(mixed $value) : string
	{
		$this->Nop($value);

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given value complies with the current type
	//------------------------------------------------------------------------------------------------------------------
	public final function IsValid(mixed $value) : bool
	{
		return ($this->Check($value) === '');
	}
}
