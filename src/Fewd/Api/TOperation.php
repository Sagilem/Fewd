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
	// Args   : TOperation $operation, array $args, array $body, array $sorts, int $startIndex, int $endIndex
	// Result : any
	private $_Callback;
	public final function Callback() : callable { return $this->_Callback; }

	// Summary
	private $_Summary;
	public final function Summary() : string { return $this->_Summary; }

	// Description
	private $_Description;
	public final function Description() : string { return $this->_Description; }

	// Code
	private $_Code;
	public final function Code() : string { return $this->_Code; }

	// Successful response type
	private $_ResponseType;
	public final function ResponseType() : ?AType { return $this->_ResponseType; }

	// External doc description
	private $_ExternalDocDescription;
	public final function ExternalDocDescription() : string { return $this->_ExternalDocDescription; }

	// External doc Url
	private $_ExternalDocUrl;
	public final function ExternalDocUrl() : string { return $this->_ExternalDocUrl; }

	// Indicates if operation is deprecated
	private $_IsDeprecated;
	public final function IsDeprecated() : bool { return $this->_IsDeprecated; }

	// Parameters
	private $_Parameters;
	public final function Parameters()             : array            { return $this->_Parameters;              }
	public final function Parameter(   string $id) : TParameter       { return $this->_Parameters[$id] ?? null; }
	public final function HasParameter(string $id) : bool             { return isset($this->_Parameters[$id]);  }
	public       function AddParameter(string $id, TParameter $value) { $this->_Parameters[$id] = $value;       }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore     $core,
		TApi      $api,
		TEndpoint $endpoint,
		string    $verb,
		callable  $callback,
		string    $summary                = '',
		string    $description            = '',
		string    $code                   = '',
		AType     $responseType           = null,
		string    $externalDocDescription = '',
		string    $externalDocUrl         = '',
		bool      $isDeprecated           = false)
	{
		parent::__construct($core);

		$this->_Api                    = $api;
		$this->_Endpoint               = $endpoint;
		$this->_Verb                   = $verb;
		$this->_Callback               = $callback;
		$this->_Summary                = $summary;
		$this->_Description            = $description;
		$this->_Code                   = $code;
		$this->_ResponseType           = $responseType;
		$this->_ExternalDocDescription = $externalDocDescription;
		$this->_ExternalDocUrl         = $externalDocUrl;
		$this->_IsDeprecated           = $isDeprecated;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Verb                   = $this->DefineVerb();
		$this->_Callback               = $this->DefineCallback();
		$this->_Summary                = $this->DefineSummary();
		$this->_Description            = $this->DefineDescription();
		$this->_Code                   = $this->DefineCode();
		$this->_ResponseType           = $this->DefineResponseType();
		$this->_ExternalDocDescription = $this->DefineExternalDocDescription();
		$this->_ExternalDocUrl         = $this->DefineExternalDocUrl();
		$this->_IsDeprecated           = $this->DefineIsDeprecated();
		$this->_Parameters             = $this->DefineParameters();
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
		// If a summary was provided :
		// Keeps it
		if($this->Summary() !== '')
		{
			return $this->Summary();
		}

		// Generates a summary, beginning with verb
		$res = ucfirst(strtolower($this->Verb()));

		// Summary contains path (without wildcards)
		$path = $this->Endpoint()->Path();

		foreach($this->Endpoint()->Wildcards() as $k => $v)
		{
			$path = str_replace('{' . $k . '}', '', $path);
		}

		$parts = explode('/', $path);

		foreach($parts as $v)
		{
			if($v !== '')
			{
				$res.= ' ' . $v;
			}
		}

		// Id ends with " by xxx and yyy" (where xxx and yyy are wildcards)
		$sep = ' by ';
		foreach($this->Endpoint()->Wildcards() as $k => $v)
		{
			$res.= $sep . $k;
			$sep = ' and ';
		}

		// Result
		return $res;
	}



	//------------------------------------------------------------------------------------------------------------------
	// Define : Description
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDescription() : string
	{
		return $this->Description();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Code
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineCode() : string
	{
		// If a code was already provided :
		// Keeps it
		if($this->Code() !== '')
		{
			return $this->Code();
		}

		// Generates a code, beginning with verb
		$res = strtolower($this->Verb());

		// Code contains path (without wildcards)
		$path = $this->Endpoint()->Path();

		foreach($this->Endpoint()->Wildcards() as $k => $v)
		{
			$path = str_replace('{' . $k . '}', '', $path);
		}

		$parts = explode('/', $path);

		foreach($parts as $v)
		{
			if($v !== '')
			{
				$res.= ucfirst($v);
			}
		}

		// Code ends with "ByXxxAndYyy" (where Xxx and Yyy are wildcards)
		$sep = 'By';
		foreach($this->Endpoint()->Wildcards() as $k => $v)
		{
			$res.= $sep . ucFirst($k);
			$sep = 'And';
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Reponse type
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineResponseType() : ?AType
	{
		return $this->ResponseType();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : External doc description
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineExternalDocDescription() : string
	{
		return $this->ExternalDocDescription();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : External doc url
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineExternalDocUrl() : string
	{
		return $this->ExternalDocUrl();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Parameters
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineParameters() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : IsDeprecated
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineIsDeprecated() : bool
	{
		return $this->IsDeprecated();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if all parameters are provided as arguments (returns a potential error message)
	//------------------------------------------------------------------------------------------------------------------
	public function CheckParameters(array $args) : string
	{
		// For each parameter :
		foreach($this->Parameters() as $v)
		{
			$name = $v->Name();

			// Mandatory parameter check
			if($v->IsMandatory() && !isset($args[$name]))
			{
				$res = TApi::ERROR_MANDATORY_PARAMETER;
				$res = str_replace('{{PARAMETER}}', $name, $res);

				return $res;
			}

			// Value check
			if(isset($args[$name]))
			{
				$message = $v->Type()->Check($args[$name]);

				if($message !== '')
				{
					$res = TApi::ERROR_INCORRECT;
					$res = str_replace('{{PARAMETER}}', $name   , $res);
					$res = str_replace('{{MESSAGE}}'  , $message, $res);

					return $res;
				}
			}
		}

		// No error found
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a response is correct (returns a potential error message)
	//------------------------------------------------------------------------------------------------------------------
	public function CheckResponse(mixed $response) : string
	{
		if($this->ResponseType() === null)
		{
			return '';
		}

		return $this->ResponseType()->Check($response);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limits a given response to a given subset
	//------------------------------------------------------------------------------------------------------------------
	protected function LimitToSubset(mixed $response, string $subset) : mixed
	{
		// If response is not an array :
		// Does nothing
		if(!is_array($response))
		{
			return $response;
		}

		$res = $response;

		// Splits subset into parts
		$parts = explode(',', $subset);

		// For each subset part :
		foreach($parts as $v)
		{
			$v = trim($v);

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
	protected function LimitToFields(mixed $response, string $fields) : mixed
	{
		// If response is not an array :
		// Does nothing
		if(!is_array($response))
		{
			return $response;
		}

		$res = array();

		// For each field in fields expression :
		$data = explode(',', $fields);

		foreach($data as $v)
		{
			$v = trim($v);

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
	protected function LimitToFilters(mixed $response, array $filters) : mixed
	{
		// If no filter provided :
		// Does nothing
		if(empty($filters))
		{
			return $response;
		}

		// If response is not an array :
		// Does nothing
		if(!is_array($response))
		{
			return $response;
		}

		$res = array();

		// For each record in response :
		// Checks if it matches to filters
		foreach($response as $v)
		{
			if(!is_array($v) || $this->CheckFilters($v, $filters))
			{
				$res[] = $v;
			}
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a record fits with some filters
	//------------------------------------------------------------------------------------------------------------------
	protected function CheckFilters(array $record, array $filters) : bool
	{
		foreach($filters as $k => $v)
		{
			// Two-characters operator provided
			$operator = substr($k, -2);

			if(($operator === '!~') ||
			   ($operator === '!=') ||
			   ($operator === '>=') ||
			   ($operator === '<='))
			{
				if(!$this->CheckFilter($record, $operator, substr($k, 0, -2), $v))
				{
					return false;
				}

				continue;
			}

			// One-character operator provided
			$operator = substr($k, -1);

			if(($operator === '~') ||
			   ($operator === '=') ||
			   ($operator === '>') ||
			   ($operator === '<'))
			{
				if(!$this->CheckFilter($record, $operator, substr($k, 0, -1), $v))
				{
					return false;
				}

				continue;
			}

			// No operator provided
			if(!$this->CheckFilter($record, '=', $k, $v))
			{
				return false;
			}
		}

		// Everything OK
		return true;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a record fits with a given filter
	//------------------------------------------------------------------------------------------------------------------
	protected function CheckFilter(mixed $record, string $operator, string $key, string $value) : bool
	{
		// If key is empty :
		// Compares record and value
		$key = trim($key);
		if($key === '')
		{
			// Array cannot be compared
			if(is_array($record))
			{
				return false;
			}

			// If record is a number :
			// Transforms it to a string with a prefix to help comparison
			if(is_numeric($record))
			{
				$record = str_pad(strval($record), 30);
				$value  = str_pad(strval($value ), 30);
			}

			// Standard operators
			if($operator === '=' ) { return ($record == $value); }
			if($operator === '==') { return ($record != $value); }
			if($operator === '>' ) { return ($record >  $value); }
			if($operator === '>=') { return ($record >= $value); }
			if($operator === '<' ) { return ($record <  $value); }
			if($operator === '<=') { return ($record <= $value); }

			// Wildcard operators
			if(($operator === '~') || ($operator === '!~'))
			{
				$pattern  = '/' . str_replace('*', '.*', $value) . '/U';
				$is_match = preg_match($pattern, $record);

				if($operator === '~')
				{
					return $is_match;
				}

				return !$is_match;
			}

			// Unknown operator
			return true;
		}

		// Otherwise :
		// Gets first part from key
		$pos = strpos($key, ',');

		if($pos === false)
		{
			$first = trim($key);
			$rest  = '';
		}
		else
		{
			$first = trim(substr($key, 0, $pos));
			$rest  = substr($key, $pos + 1);
		}

		// If first part from key is a joker :
		// Iterates until the first case that matches
		if($first === '*')
		{
			if(!is_array($record))
			{
				return false;
			}

			foreach($record as $v)
			{
				if($this->CheckFilter($v, $operator, $rest, $value))
				{
					return true;
				}
			}

			return false;
		}

		// If first part from key is not found :
		// It does not match
		if(!isset($record[$first]))
		{
			return false;
		}

		// Otherwise :
		// Recursive call
		return $this->CheckFilter($record[$first], $operator, $rest, $value);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Transforms arguments into ready-to-use filters (aka conditions) for the Fewd/Data module
	//------------------------------------------------------------------------------------------------------------------
	protected function Filters(array $args) : array
	{
		$res = array();

		// For each argument :
		foreach($args as $k => $v)
		{
			// If no operator prefixed by ':' :
			// Keeps argument "as is"
			$pos = strrpos($k, ':');

			if($pos === false)
			{
				$res[$k] = $v;
				continue;
			}

			// Otherwise :
			// Separates key from operator
			$key      = substr($k, 0, $pos);
			$operator = substr($k, $pos + 1);

			// Converts operator into condition operator
			switch($operator)
			{
				case 'like'    : $operator = '~' ; break;
				case 'notlike' : $operator = '!~'; break;
				case 'eq'      : $operator = '=' ; break;
				case 'ne'      : $operator = '!='; break;
				case 'gt'      : $operator = '>' ; break;
				case 'ge'      : $operator = '>='; break;
				case 'lt'      : $operator = '<' ; break;
				case 'le'      : $operator = '<='; break;
				default        : $operator = ':' . $operator;
			}

			// If operator is LIKE or NOT LIKE :
			// Converts wildcard
			if(($operator === 'like') || ($operator === 'notlike'))
			{
				$v = str_replace('*', '%', $v);
			}

			// Stores the condition
			$res[$key . $operator] = $v;
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Prepares a sort fields array from a given sort string (ex : 'key,subkey,subsubkey')
	//------------------------------------------------------------------------------------------------------------------
	protected function SortFields(string $sortString) : array
	{
		$res = array();

		if($sortString !== '')
		{
			$parts = explode(',', $sortString);

			foreach($parts as $v)
			{
				$v = trim($v);

				if($v === '')
				{
					continue;
				}

				$res[$v] = $v;
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Sorts response into ascending order
	//------------------------------------------------------------------------------------------------------------------
	protected function Sort(mixed $response, array $sorts) : array
	{
		$order = array();

		// If no sort provided :
		// Does nothing
		if(empty($sorts))
		{
			return $response;
		}

		// If response is not an array :
		// Does nothing
		if(!is_array($response))
		{
			return $response;
		}

		// For each record in response :
		foreach($response as $k => $v)
		{
			// Gets record index
			$index = '';
			$cur   = $v;

			foreach($sorts as $vv)
			{
				if(!isset($cur[$vv]))
				{
					$index = '';
					break;
				}

				$index = $cur[$vv];
				$cur   = $index;
			}

			// If index is an array :
			// Unable to sort arrays between themselves
			if(is_array($index))
			{
				return $response;
			}

			// Adds some blanks before index, to ensure compatibility between number and string sort
			$index = str_pad($index, 20);

			// Stores order
			$order[$index] = $k;
		}

		// Sorts
		ksort($order);

		// Generates a sorted array
		$res = array();

		foreach($order as $v)
		{
			$res[] = $response[$v];
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Endpoint response
	//------------------------------------------------------------------------------------------------------------------
	public function Response(
		array  $args,
		array  $body,
		string $subset,
		string $fields,
		string $sort,
		int    $offset,
		int    $limit) : mixed
	{
		// Prepares sorts
		$sorts = $this->SortFields($sort);

		// Converts arguments into filters
		$args = $this->Filters($args);

		// Gets response
		$res = call_user_func($this->Callback(), $this, $args, $body, $sorts, $offset, $limit);

		// A null, callable or resource response is directly returned as "null"
		if(($res === null) || is_callable($res) || is_resource($res))
		{
			return null;
		}

		// An object response is converted into an array
		if(is_object($res))
		{
			$res = $this->Core()->ObjectToArray($res);
		}

		// If verb is GET :
		if($this->Verb() === 'GET')
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

		// Ensures that data is sorted
		$res = $this->Sort($res, $sorts);

		// Result
		return $res;
	}
}
