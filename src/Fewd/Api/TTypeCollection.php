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
		?AType  $itemsType)
	{
		parent::__construct($core, $api, $name);

		$this->_ItemsType = $itemsType;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_ItemsType = $this->DefineItemsType();
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
	public function Check(mixed $value) : string
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
				if(!$this->ItemsType()->IsValid($v))
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
}