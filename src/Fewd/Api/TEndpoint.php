<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\AThing;
use Fewd\Core\TCore;



class TEndpoint extends AThing
{
	// Api
	private $_Api;
	public final function Api() : TApi { return $this->_Api; }

	// Path (example : "pet/{id}")
	private $_Path;
	public final function Path() : string { return $this->_Path; }

	// Wildcards in path
	private $_Wildcards;
	public final function Wildcards()             : array { return $this->_Wildcards;             }
	public       function HasWildcard(string $id) : bool  { return isset($this->_Wildcards[$id]); }

	// Maximum limit (i.e. maximum records number that could be GET at once)
	private $_MaximumLimit;
	public final function MaximumLimit() : int { return $this->_MaximumLimit; }

	// Operations associated to verbs
	private $_Operations = array();
	public final function Operations() : array                              { return $this->_Operations;               }
	public       function Operation(   string $verb) : TOperation           { return $this->_Operations[$verb] ?? null;}
	public       function HasOperation(string $verb) : bool                 { return isset($this->_Operations[$verb]); }
	public       function AddOperation(string $verb, TOperation $operation) { $this->_Operations[$verb] = $operation;  }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(TCore $core, TApi $api, string $path, int $maximumLimit)
	{
		parent::__construct($core);

		$this->_Api          = $api;
		$this->_Path         = $path;
		$this->_MaximumLimit = $maximumLimit;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Path         = $this->DefinePath();
		$this->_Wildcards    = $this->DefineWildcards();
		$this->_MaximumLimit = $this->DefineMaximumLimit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Path
	//------------------------------------------------------------------------------------------------------------------
	protected function DefinePath() : string
	{
		// Path is case insensitive
		$res = $this->Core()->ToLower($this->Path());

		// No query string in path
		$pos = strpos($res, '?');
		if($pos !== false)
		{
			$res = substr($res, 0, $pos);
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Wildcards
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineWildcards() : array
	{
		// Gets wildcards from endpoint's path
		$res     = array();
		$matches = array();

		$nb = preg_match_all('/([{].*[}])/U', $this->Path(), $matches);
		if(is_int($nb) && ($nb > 0))
		{
			foreach($matches[0] as $v)
			{
				$key       = substr($v, 1, -1);
				$res[$key] = $key;
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Maximum records number
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMaximumLimit() : int
	{
		$res = $this->MaximumLimit();

		if($res < 1)
		{
			$res = $this->Api()->DefaultMaximumLimit();
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limits a given response to a given subset
	//------------------------------------------------------------------------------------------------------------------
	protected function LimitToSubset(array $response, string $subset) : array
	{
		// If response is null :
		// Nothing to limit
		if($response === null)
		{
			return null;
		}

		$res = $response;

		// Splits subset into parts
		$parts = explode('/', $subset);

		// For each subset part :
		foreach($parts as $v)
		{
			// Empty parts are ignored
			if($v === '')
			{
				continue;
			}

			// If part is not found :
			// Stops here
			if(!isset($res[$v]))
			{
				return null;
			}

			// Otherwise :
			// Fits result to the current part
			$res = $res[$v];
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limits a given response to a set of given fields
	//------------------------------------------------------------------------------------------------------------------
	protected function LimitToFields(array $response, string $fields) : array
	{
		// If response is null :
		// Nothing to limit
		if($response === null)
		{
			return null;
		}

		$res  = array();

		// For each field in fields expression :
		$data = explode(',', $fields);

		foreach($data as $v)
		{
			// Empty fields are ignored
			if($v === '')
			{
				continue;
			}

			// Keeps value for the current field
			if(isset($response[$v]))
			{
				$res[$v] = $response[$v];
			}
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limits a given response to a set of given filters
	//------------------------------------------------------------------------------------------------------------------
	public function LimitToFilters(array $response, array $args) : array
	{
		// TODO
		return $response;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Prepares a sort array from a given sort string (ex : 'description,name-,id+')
	//------------------------------------------------------------------------------------------------------------------
	protected function SortFields(string $sortString) : array
	{
		$res = array();

		if($sortString !== '')
		{
			$parts = explode(',', $sortString);

			foreach($parts as $v)
			{
				if($v === '')
				{
					continue;
				}

				$order = 'ASC';

				if(substr($v, -1) === '-')
				{
					$v     = substr($v, 0, -1);
					$order = 'DESC';
				}
				elseif(substr($v, -1) === '+')
				{
					$v     = substr($v, 0, -1);
				}

				$res[$v] = $order;
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Endpoint response
	//------------------------------------------------------------------------------------------------------------------
	public function Response(
		string $verb,
		array  $args,
		array  $body,
		string $subset,
		string $fields,
		string $sort,
		int    $offset,
		int    $limit) : array
	{
		// Prepares sorts
		$sorts = $this->SortFields($sort);

		// Gets response
		$operation = $this->Operation($verb);

		if($operation === null)
		{
			return array();
		}

		$res = call_user_func($operation->Callback(), $args, $body, $sorts, $offset, $limit);

		// If response was not defined in callback :
		// Returns an empty response
		if($res === null)
		{
			return array();
		}

		// If verb is GET :
		if($verb === 'GET')
		{
			// Limits response to a subset
			if($subset !== '')
			{
				$res = $this->LimitToSubset($res, $subset);
			}

			// Limits response to a set of fields
			if($fields !== '')
			{
				$res = $this->LimitToFields($res, $fields);
			}

			// Limits response to a set of filters
			$res = $this->LimitToFilters($res, $args);
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a list of allowed verbs for a preflight run (see CORS standard)
	//------------------------------------------------------------------------------------------------------------------
	public function AllowedVerbs() : string
	{
		$verbs = array();
		foreach($this->Operations() as $k => $v)
		{
			$verbs[$k] = $k;
		}

		$verbs['OPTIONS'] = 'OPTIONS';

		$res = implode(', ', $verbs);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the maximum age for a preflight run (see CORS standard)
	//------------------------------------------------------------------------------------------------------------------
	public function MaximumAge() : int
	{
		return 3600;
	}
}
