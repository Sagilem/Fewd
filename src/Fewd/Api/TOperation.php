<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\AThing;
use Fewd\Core\TCore;



class TOperation extends AThing
{
	// Api
	private $_Api;
	public final function Api() : TApi { return $this->_Api; }

	// Endpoint
	private $_Endpoint;
	public final function Endpoint() : TEndpoint { return $this->_Endpoint; }

	// Verb
	private $_Verb;
	public final function Verb() : string { return $this->_Verb; }

	// Callback
	// Args   : TEndpoint $endpoint, array $args, array $body, array $sorts, int $startIndex, int $endIndex
	// Result : array
	private $_Callback;
	public final function Callback() : callable { return $this->_Callback; }

	// Summary
	private $_Summary;
	public final function Summary() : string { return $this->_Summary; }

	// Description
	private $_Description;
	public final function Description() : string { return $this->_Description; }

	// Id
	private $_Id = '';
	public final function Id() : string { return $this->_Id; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore     $core,
		TApi      $api,
		TEndpoint $endpoint,
		string    $verb,
		callable  $callback,
		string    $summary     = '',
		string    $description = '',
		string    $id          = '')
	{
		parent::__construct($core);

		$this->_Api         = $api;
		$this->_Endpoint    = $endpoint;
		$this->_Verb        = $verb;
		$this->_Callback    = $callback;
		$this->_Summary     = $summary;
		$this->_Description = $description;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Verb        = $this->DefineVerb();
		$this->_Callback    = $this->DefineCallback();
		$this->_Summary     = $this->DefineSummary();
		$this->_Description = $this->DefineDescription();
		$this->_Id          = $this->DefineId();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Verb
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineVerb() : string
	{
		return $this->Verb();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Callback
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineCallback() : callable
	{
		return $this->Callback();
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
	// Define : Id
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineId() : string
	{
		// If an id was provided :
		// Returns it
		if($this->Id() !== '')
		{
			return $this->Id();
		}

		// Id begins with verb
		$res = strtolower($this->Verb());

		// Id contains path (without wildcards)
		$path = $this->Endpoint()->Path();

		foreach($this->Endpoint()->Wildcards() as $k => $v)
		{
			$path = str_replace($path, '{' . $k . '}', '');
		}

		$parts = explode('/', $path);

		foreach($parts as $v)
		{
			if($v !== '')
			{
				$res.= ucfirst($v);
			}
		}

		// Id ends with "ByXxxAndYyy" (where Xxx and Yyy are wildcards)
		$sep = 'By';
		foreach($this->Endpoint()->Wildcards() as $k => $v)
		{
			$res.= $sep . ucFirst($k);
			$sep = 'And';
		}

		// Result
		return $res;
	}
}
