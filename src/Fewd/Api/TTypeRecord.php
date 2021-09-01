<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\TCore;


class TTypeRecord extends AType
{
	// Summary
	private $_Summary;
	public final function Summary() : string { return $this->_Summary; }

	// Description
	private $_Description;
	public final function Description() : string { return $this->_Description; }

	// Properties
	private $_Properties;
	public final function Properties()                 : array       { return $this->_Properties;                }
	public final function Property(      string $name) : AType       { return $this->_Properties[$name] ?? null; }
	public final function HasProperty(   string $name) : bool        { return isset($this->_Properties[$name]);  }

	// MandatoryProperties
	private $_MandatoryProperties;
	public final function MandatoryProperties()           : array { return $this->_MandatoryProperties;              }
	public final function IsMandatoryProperty(string $id) : bool  { return isset($this->_MandatoryProperties[$id]);  }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore  $core,
		TApi   $api,
		string $name,
		string $summary,
		string $description,
		array  $properties,
		array  $sample,
		array  $default)
	{
		parent::__construct($core, $api, $name, $sample, $default);

		$this->_Summary     = $summary;
		$this->_Description = $description;
		$this->_Properties  = $properties;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Summary             = $this->DefineSummary();
		$this->_Description         = $this->DefineDescription();
		$this->_MandatoryProperties = $this->DefineMandatoryProperties();
		$this->_Properties          = $this->DefineProperties();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Default
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDefault() : mixed
	{
		$default    = $this->Default();
		$properties = $this->DefineProperties();

		$res = array();

		foreach($properties as $k => $v)
		{
			if(isset($default[$k]))
			{
				$res[$k] = $default[$k];
			}
			else
			{
				$res[$k] = $v->Default();
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Sample
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineSample() : mixed
	{
		$sample     = $this->Sample();
		$properties = $this->DefineProperties();

		$res = array();

		foreach($properties as $k => $v)
		{
			if(isset($sample[$k]))
			{
				$res[$k] = $sample[$k];
			}
			else
			{
				$res[$k] = $v->Sample();
			}
		}

		return $res;
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
	// Define : MandatoryProperties
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMandatoryProperties() : array
	{
		$res        = array();
		$properties = $this->Properties();

		foreach($properties as $k => $v)
		{
			if(substr($k, -1) === '*')
			{
				$k = substr($k, 0, -1);
				$res[$k] = $k;
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : properties
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineProperties() : array
	{
		$res        = array();
		$properties = $this->Properties();

		foreach($properties as $k => $v)
		{
			if(!is_string($v) && !($v instanceof AType))
			{
				continue;
			}

			if(substr($k, -1) === '*')
			{
				$k = substr($k, 0, -1);
			}

			if(is_string($v))
			{
				$v = $this->Api()->Type($v);
			}

			$res[$k] = $v;
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Raw converts a given value to the current type (without any control)
	//------------------------------------------------------------------------------------------------------------------
	protected function RawConvert(mixed $value) : mixed
	{
		$res = array();

		foreach($this->Properties() as $k => $v)
		{
			// If an attribute corresponds to current property,
			// And if this property is not of the expected type :
			// Error
			if(isset($value[$k]))
			{
				$res[$k] = $v->Convert($value[$k]);
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given value complies with the current type (returns an error message if not)
	//------------------------------------------------------------------------------------------------------------------
	public function Check(mixed $value, int $level = self::CHECK_LEVEL_MANDATORY) : string
	{
		// If value is not an object nor an array :
		// Error
		if(!is_object($value) && !is_array($value))
		{
			$res = TApi::ERROR_RECORD;
			$res = str_replace('{{TYPE}}', $this->Name(), $res);

			return $res;
		}

		// For each property :
		foreach($this->Properties() as $k => $v)
		{
			// If an attribute corresponds to current property,
			// And if this property is not of the expected type :
			// Error
			if(isset($value[$k]))
			{
				$res = $v->Check($value[$k], $level);
				if($res !== '')
				{
					return $res;
				}
			}

			// If no attribute corresponds to current property :
			else
			{
				// If any mandatory property must be present :
				// Error
				if($this->IsMandatoryProperty($k) && ($level === self::CHECK_LEVEL_MANDATORY))
				{
					$res = TApi::ERROR_MANDATORY_PROPERTY;
					$res = str_replace('{{PROPERTY}}', $k, $res);

					return $res;
				}

				// If any property must be present :
				// Error
				if($level === self::CHECK_LEVEL_ALL)
				{
					$res = TApi::ERROR_PROPERTY;
					$res = str_replace('{{PROPERTY}}', $k, $res);

					return $res;
				}
			}
		}

		// OK result
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adapts a given value
	//------------------------------------------------------------------------------------------------------------------
	public function Adapt(mixed &$value)
	{
		if(is_array($value))
		{
			foreach($this->Properties() as $k => &$v)
			{
				if(isset($value[$k]))
				{
					$v->Adapt($value[$k]);
				}
			}
		}

		return $value;
	}
}
