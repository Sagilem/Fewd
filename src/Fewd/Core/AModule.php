<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Core;


abstract class AModule extends AThing
{
	// Internal storage for name
	protected $_Name = '';

	// Internal storage for version number
	protected $_Version = '';


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->Core()->AddModule(get_class($this), $this);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Name
	//------------------------------------------------------------------------------------------------------------------
	public function Name() : string
	{
		if($this->_Name === '')
		{
			$this->_Name = basename(dirname($this->ClassFilename()));
		}

		return $this->_Name;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Version number
	//------------------------------------------------------------------------------------------------------------------
	public function Version() : string
	{
		if(empty($this->_Version))
		{
			$time = $this->Core()->LastModifiedDate(dirname($this->ClassFilename()));

			$major    = 1 * Date('y', $time);
			$minor    = 1 * Date('m', $time);
			$revision = 1 * Date('d', $time);
			$build    = 1 * Date('Hi', $time);

			if($major > 20)
			{
				$major -= 20;
			}
			else
			{
				$major = 1;
			}

			$this->_Version = $major . '.' . $minor . '.' . $revision . '.' . $build;
		}

		return $this->_Version;
	}
}