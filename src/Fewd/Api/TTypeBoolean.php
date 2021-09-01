<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\TCore;


class TTypeBoolean extends AType
{
	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore  $core,
		TApi   $api,
		string $name,
		bool   $sample,
		bool   $default)
	{
		parent::__construct($core, $api, $name, $sample, $default);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Raw converts a given value to the current type (without any control)
	//------------------------------------------------------------------------------------------------------------------
	protected function RawConvert(mixed $value) : mixed
	{
		return !empty($value);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given value complies with the current type (returns an error message if not)
	//------------------------------------------------------------------------------------------------------------------
	public function Check(mixed $value, int $level = self::CHECK_LEVEL_MANDATORY) : string
	{
		// Anything can be converted into a boolean value
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adapts a given value
	//------------------------------------------------------------------------------------------------------------------
	public function Adapt(mixed &$value)
	{
		if(($value === false) || ($value === '-') || ($value === 'false') || ($value === 'no') || ($value === 'n'))
		{
			$value = false;
		}
		else
		{
			$value = true;
		}
	}
}