<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\TCore;


class TTypeCollection extends AType
{
	// Items type
	private $_ItemsType;
	public final function ItemsType() : ?AType { return $this->_ItemsType; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore   $core,
		TApi    $api,
		string  $name,
		?AType  $itemsType,
		array   $sample,
		array   $default)
	{
		parent::__construct($core, $api, $name, $sample, $default);

		$this->_ItemsType = $itemsType;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		$this->_ItemsType = $this->DefineItemsType();

		parent::Init();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Sample
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineSample() : mixed
	{
		$res = $this->Sample();

		if(empty($res) && ($this->ItemsType() !== null))
		{
			$res = array($this->ItemsType()->Sample());
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : ItemsType
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineItemsType() : ?AType
	{
		return $this->ItemsType();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given value complies with the current type (returns an error message if not)
	//------------------------------------------------------------------------------------------------------------------
	public function Check(mixed $value, int $level = self::CHECK_LEVEL_MANDATORY) : string
	{
		// If value is not an array :
		// Error
		if(!is_array($value))
		{
			return TApi::ERROR_COLLECTION;
		}

		// Checks if items are of the right type
		if($this->ItemsType() !== null)
		{
			foreach($value as $v)
			{
				if(!$this->ItemsType()->IsValid($v, $level))
				{
					$res = TApi::ERROR_COLLECTION_TYPE;
					$res = str_replace('{{TYPE}}', $this->ItemsType()->Name(), $res);

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
		if(is_array($value) && ($this->ItemsType() !== null))
		{
			foreach($value as &$v)
			{
				$this->ItemsType()->Adapt($v);
			}
		}
	}
}