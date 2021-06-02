<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Core;


use ReflectionClass;


abstract class AThing
{
	// Core
	private $_Core;
	public final function Core() : Tcore { return $this->_Core; }

	// Internal storage for class filename
	private $_ClassFilename = '';


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct($core)
	{
		$this->_Core = $core;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
	}


	//------------------------------------------------------------------------------------------------------------------
	// "NOP" instruction
	// Useful to encapsulate unused method parameters, on parent classes
	//------------------------------------------------------------------------------------------------------------------
	protected function Nop($var)
	{
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the absolute filename where the class is defined
	//------------------------------------------------------------------------------------------------------------------
	protected function ClassFilename() : string
	{
		if($this->_ClassFilename === '')
		{
			$class = $this->MakeReflectionClass($this);

			$this->_ClassFilename = $class->getFileName();
		}

		return $this->_ClassFilename;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Reflection
	//------------------------------------------------------------------------------------------------------------------
	protected function MakeReflectionClass(object $object) : ReflectionClass
	{
		$res = new ReflectionClass($object);

		return $res;
	}
}