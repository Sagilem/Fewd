<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\AThing;
use Fewd\Core\TCore;


class TParameter extends AThing
{
	// Api
	private $_Api;
	public final function Api() : TApi { return $this->_Api; }

	// Name
	private $_Name;
	public final function Name() : string { return $this->_Name; }

	// Type
	private $_Type;
	public final function Type() : AType { return $this->_Type; }

	// Indicates if parameter is mandatory
	private $_IsMandatory;
	public final function IsMandatory() : bool { return $this->_IsMandatory; }

	// Default value
	private $_Default;
	public final function Default() : mixed { return $this->_Default; }

	// Summary
	private $_Summary;
	public final function Summary() : string { return $this->_Summary; }

	// Description
	private $_Description;
	public final function Description() : string { return $this->_Description; }

	// Sample value
	private $_Sample;
	public final function Sample() : mixed { return $this->_Sample; }

	// Indicates if deprecated
	private $_IsDeprecated;
	public final function IsDeprecated() : bool { return $this->_IsDeprecated; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TApi       $api,
		string     $name,
		AType      $type,
		bool       $isMandatory,
		mixed      $default,
		string     $summary,
		string     $description,
		mixed      $sample,
		bool       $isDeprecated)
	{
		parent::__construct($core);

		$this->_Api          = $api;
		$this->_Name         = $name;
		$this->_Type         = $type;
		$this->_IsMandatory  = $isMandatory;
		$this->_Default      = $default;
		$this->_Summary      = $summary;
		$this->_Description  = $description;
		$this->_Sample       = $sample;
		$this->_IsDeprecated = $isDeprecated;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Name         = $this->DefineName();
		$this->_Type         = $this->DefineType();
		$this->_IsMandatory  = $this->DefineIsMandatory();
		$this->_Default      = $this->DefineDefault();
		$this->_Summary      = $this->DefineSummary();
		$this->_Description  = $this->DefineDescription();
		$this->_Sample       = $this->DefineSample();
		$this->_IsDeprecated = $this->DefineIsDeprecated();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Name
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineName() : string
	{
		return $this->Name();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Type
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineType() : AType
	{
		return $this->Type();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : IsMandatory
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineIsMandatory() : bool
	{
		return $this->IsMandatory();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Default value
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDefault() : mixed
	{
		return $this->Default();
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
	// Define : Sample
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineSample() : mixed
	{
		return $this->Sample();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : IsDeprecated
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineIsDeprecated() : bool
	{
		return $this->IsDeprecated();
	}
}
