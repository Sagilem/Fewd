<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\TCore;


class TTypeEntity extends AType
{
	// Summary
	private $_Summary;
	public final function Summary() : string { return $this->_Summary; }

	// Description
	private $_Description;
	public final function Description() : string { return $this->_Description; }

	// Properties
	private $_Properties;
	public final function Properties()                 : array            { return $this->_Properties;                }
	public final function Property(      string $name) : TParameter       { return $this->_Properties[$name] ?? null; }
	public final function HasProperty(   string $name) : bool             { return isset($this->_Properties[$name]);  }
	public       function AddProperty(   string $name, TParameter $value) { $this->_Properties[$name] = $value;       }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore  $core,
		TApi   $api,
		string $name,
		string $summary,
		string $description)
	{
		parent::__construct($core, $api, $name);

		$this->_Summary     = $summary;
		$this->_Description = $description;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Summary     = $this->DefineSummary();
		$this->_Description = $this->DefineDescription();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Summary
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineSummary() : string
	{
		return $this->Summary();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Description
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDescription() : string
	{
		return $this->Description();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Raw converts a given value to the current type (without any control)
	//------------------------------------------------------------------------------------------------------------------
	protected function RawConvert(mixed $value) : mixed
	{
		$res = array();

		foreach($this->Properties() as $v)
		{
			// If an attribute corresponds to current property,
			// And if this property is not of the expected type :
			// Error
			if(isset($value[$v->Name()]))
			{
				$res[$v->Name()] = $v->Type()->Convert($value[$v->Name()]);
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given value complies with the current type (returns an error message if not)
	//------------------------------------------------------------------------------------------------------------------
	public function Check(mixed $value) : string
	{
		// If value is not an object nor an array :
		// Error
		if(!is_object($value) && !is_array($value))
		{
			$res = TApi::ERROR_ENTITY;
			$res = str_replace('{{TYPE}}', $this->Name(), $res);

			return $res;
		}

		// For each property :
		foreach($this->Properties() as $v)
		{
			// If an attribute corresponds to current property,
			// And if this property is not of the expected type :
			// Error
			if(isset($value[$v->Name()]))
			{
				$res = $v->Type()->Check($value[$v->Name()]);
				if($res !== '')
				{
					return $res;
				}
			}

			// If no attribute corresponds to current property,
			// And if this property is mandatory :
			// Error
			elseif($v->IsMandatory())
			{
				$res = TApi::ERROR_MANDATORY_PROPERTY;
				$res = str_replace('{{PROPERTY}}', $v->Name(), $res);

				return $res;
			}
		}

		// OK result
		return '';
	}
}
