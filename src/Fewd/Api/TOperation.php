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
	private $_Callback;
	public final function Callback() : callable { return $this->_Callback; }

	// Body type
	private $_BodyType;
	public final function BodyType() : ?AType { return $this->_BodyType; }

	// Successful response type
	private $_ResponseType;
	public final function ResponseType() : ?AType { return $this->_ResponseType; }

	// Summary
	private $_Summary;
	public final function Summary() : string { return $this->_Summary; }

	// Description
	private $_Description;
	public final function Description() : string { return $this->_Description; }

	// Code
	private $_Code;
	public final function Code() : string { return $this->_Code; }

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

	// Gets partial response info
	protected $_Partial;


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore     $core,
		TApi      $api,
		TEndpoint $endpoint,
		string    $verb,
		callable  $callback,
		AType     $bodyType               = null,
		AType     $responseType           = null,
		string    $summary                = '',
		string    $description            = '',
		string    $code                   = '',
		string    $externalDocDescription = '',
		string    $externalDocUrl         = '',
		bool      $isDeprecated           = false)
	{
		parent::__construct($core);

		$this->_Api                    = $api;
		$this->_Endpoint               = $endpoint;
		$this->_Verb                   = $verb;
		$this->_Callback               = $callback;
		$this->_BodyType               = $bodyType;
		$this->_ResponseType           = $responseType;
		$this->_Summary                = $summary;
		$this->_Description            = $description;
		$this->_Code                   = $code;
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

		if($res === 'Getall')
		{
			$res = 'Get all';
		}

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

		if($res === 'getall')
		{
			$res = 'getAll';
		}

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
	// Gets a given string argument from a given arguments array, after removing it
	//------------------------------------------------------------------------------------------------------------------
	protected function StringArgumentValue(string $arg, array &$args) : string
	{
		$res = '';

		if(isset($args[$arg]))
		{
			$res = $args[$arg];

			unset($args[$arg]);
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a given int argument from a given arguments array, after removing it
	//------------------------------------------------------------------------------------------------------------------
	protected function IntArgumentValue(string $arg, array &$args) : string
	{
		$res = 0;

		if(isset($args[$arg]))
		{
			$res = $args[$arg];

			unset($args[$arg]);
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the subsets argument
	//------------------------------------------------------------------------------------------------------------------
	protected function SubsetsArgument(array &$args) : array
	{
		// Gets argument
		$argument = $this->StringArgumentValue($this->Api()->SubsetsArg(), $args);

		// For each field in argument :
		$parts = explode(',', $argument);
		$res   = array();

		foreach($parts as $v)
		{
			$v = trim($v);

			// Empty fields are ignored
			if($v === '')
			{
				continue;
			}

			// Stores current part
			$res[$v] = $v;
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the fields argument
	//------------------------------------------------------------------------------------------------------------------
	protected function FieldsArgument(array &$args) : array
	{
		// Gets argument
		$argument = $this->StringArgumentValue($this->Api()->FieldsArg(), $args);

		// For each field in argument :
		$parts = explode(',', $argument);
		$res   = array();

		foreach($parts as $v)
		{
			$v = trim($v);

			// Empty fields are ignored
			if($v === '')
			{
				continue;
			}

			// Stores current part
			$res[$v] = $v;
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the sorts argument
	//------------------------------------------------------------------------------------------------------------------
	protected function SortsArgument(array &$args) : array
	{
		// Gets argument
		$argument = $this->StringArgumentValue($this->Api()->SortsArg(), $args);

		// Detects sort fields in argument
		$res   = array();
		$parts = explode(',', $argument);

		foreach($parts as $v)
		{
			$v = trim($v);

			// Empty fields are ignored
			if(($v === '') || ($v === '-'))
			{
				continue;
			}

			// If descending order symbol has been put at end of field name :
			// Putts it at the beginning
			if(substr($v, -1) === '-')
			{
				$v = '-' . substr($v, 0, -1);
			}

			// Descending order case
			if(substr($v, 0, 1) === '-')
			{
				$res[$v] = substr($v, 1);
			}

			// Ascending order case
			else
			{
				$res[$v] = $v;
			}
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the offset argument
	//------------------------------------------------------------------------------------------------------------------
	protected function OffsetArgument(array &$args) : int
	{
		return max($this->IntArgumentValue($this->Api()->OffsetArg(), $args), 0);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the limit argument
	//------------------------------------------------------------------------------------------------------------------
	protected function LimitArgument(array &$args) : int
	{
		$res = $this->IntArgumentValue($this->Api()->LimitArg(), $args);
		if(($this->Endpoint()->MaximumLimit() > 0) && ($res > $this->Endpoint()->MaximumLimit()))
		{
			$res = $this->Endpoint()->MaximumLimit();
		}

		if($res < 0)
		{
			$res = 0;
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Transforms arguments into filters (aka conditions)
	//------------------------------------------------------------------------------------------------------------------
	protected function ArgumentsToFilters(array $args) : array
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
	// Prepares a default set of values for POST, PUT, PATCH
	//------------------------------------------------------------------------------------------------------------------
	public function DefaultBody() : mixed
	{
		if($this->BodyType() !== null)
		{
			return $this->BodyType()->Default();
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Filters a collection according to some filters
	//------------------------------------------------------------------------------------------------------------------
	protected function FilterCollection(array $collection, array $filters) : array
	{
		$res = array();

		foreach($collection as $v)
		{
			if(is_array($v) && $this->MatchesRecordWithFilters($v, $filters))
			{
				$res[] = $v;
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given record fits with some filters
	//------------------------------------------------------------------------------------------------------------------
	protected function MatchesRecordWithFilters(mixed $record, array $filters) : bool
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
				if(!$this->MatchesRecordWithFilter($record, $operator, substr($k, 0, -2), $v))
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
				if(!$this->MatchesRecordWithFilter($record, $operator, substr($k, 0, -1), $v))
				{
					return false;
				}

				continue;
			}

			// No operator provided
			if(!$this->MatchesRecordWithFilter($record, '=', $k, $v))
			{
				return false;
			}
		}

		// Everything OK
		return true;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given record fits with a given filter
	//------------------------------------------------------------------------------------------------------------------
	protected function MatchesRecordWithFilter(mixed $record, string $operator, string $key, string $value) : bool
	{
		// If record is not an array :
		// It cannot match
		if(!is_array($record))
		{
			return false;
		}

		// Key can be a path (for instance : "item,subitem,id")
		$rest = '';
		$pos  = strpos($key, ',');

		if($pos !== false)
		{
			$rest = substr($key, $pos + 1);
			$key  = substr($key, 0, $pos);
		}

		// If key does not exist :
		// Record fits by default
		if(!isset($record[$key]))
		{
			return true;
		}

		$data = $record[$key];

		// If key was a path :
		// Recursive call
		if($rest !== '')
		{
			if(is_array($data))
			{
				return $this->MatchesRecordWithFilter($data, $operator, $rest, $value);
			}

			return true;
		}

		// Otherwise :
		// Formats value to be compared with data
		if(is_int($data))
		{
			$value = intval($data);
		}
		elseif(is_float($data))
		{
			$value = floatval($data);
		}
		elseif(is_bool($data))
		{
			if(($value === false) || ($value === '-') || ($value === 'false') || ($value === 'no') || ($value === 'n'))
			{
				$value = false;
			}
			else
			{
				$value = true;
			}
		}
		elseif(!is_string($data))
		{
			return false;
		}

		// Standard operators
		if($operator === '=' ) { return ($data == $value); }
		if($operator === '!=') { return ($data != $value); }
		if($operator === '>' ) { return ($data >  $value); }
		if($operator === '>=') { return ($data >= $value); }
		if($operator === '<' ) { return ($data <  $value); }
		if($operator === '<=') { return ($data <= $value); }

		// Wildcard operators are only available with strings
		if((($operator === '~') || ($operator === '!~')) && is_string($value))
		{
			$pattern  = '/' . str_replace('*', '.*', $value) . '/U';
			$is_match = preg_match($pattern, $data);

			if($operator === '~')
			{
				return $is_match;
			}

			return !$is_match;
		}

		// Unknown operator :
		// Record does not match
		return false;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Sorts response
	//------------------------------------------------------------------------------------------------------------------
	protected function SortCollection(array &$collection, array $sorts)
	{
		// Prepares sorts
		$tb = array();
		foreach($sorts as $k => $v)
		{
			$factor = 1;

			if(substr($k, -1) === '-')
			{
				$k      = substr($k, 0, -1);
				$factor = -1;
			}
			elseif(substr($k, 0, 1) === '-')
			{
				$k      = substr($k, 1);
				$factor = -1;
			}

			$tb[$k] = $factor;
		}

		$sorts = $tb;

		// Sorts data
		usort($collection, function(mixed $a, mixed $b) use ($sorts) : int
		{
			// For each sort field :
			foreach($sorts as $k => $v)
			{
				// Sort fields that are not present in response are ignored
				if(!isset($a[$k]) || !isset($b[$k]))
				{
					continue;
				}

				// if both values for the current sort field are the same :
				// Goes on with next record
				if($a[$k] < $b[$k])
				{
					return -$v;
				}
				elseif($a[$k] > $b[$k])
				{
					return $v;
				}
			}
		});
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limits a collection response to subsets
	//------------------------------------------------------------------------------------------------------------------
	protected function LimitCollectionToSubsets(array &$collection, array $subsets)
	{
		foreach($collection as &$v)
		{
			$this->LimitRecordToSubsets($v, $subsets);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limits a record to subsets
	//------------------------------------------------------------------------------------------------------------------
	protected function LimitRecordToSubsets(mixed &$record, array $subsets)
	{
		foreach($subsets as $k => $v)
		{
			if(isset($record[$k]))
			{
				$record = $record[$k];
			}
			else
			{
				$record = null;

				return;
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limits a collection response to fields
	//------------------------------------------------------------------------------------------------------------------
	protected function LimitCollectionToFields(array &$collection, array $fields)
	{
		foreach($collection as &$v)
		{
			$this->LimitRecordToFields($v, $fields);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limits a record to fields
	//------------------------------------------------------------------------------------------------------------------
	protected function LimitRecordToFields(mixed &$record, array $fields)
	{
		if(is_array($record))
		{
			$res = array();

			foreach($fields as $k => $v)
			{
				if(isset($record[$k]))
				{
					$res[$k] = $record[$k];
				}
			}

			$record = $res;
		}
		else
		{
			$record = null;
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adapts endpoint wildcards
	//------------------------------------------------------------------------------------------------------------------
	protected function AdaptWildcards(array &$args)
	{
		// For each wildcard :
		foreach($this->Endpoint()->Wildcards() as $k => $v)
		{
			if($v === null)
			{
				continue;
			}

			// If wildcard value was not declared :
			// Applies the wildcard default value
			if(!isset($args[$k]))
			{
				$args[$k] = $v->Default();
			}

			// Adapts wildcard
			$v->Adapt($args[$k]);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if all endpoint wildcards are provided as arguments
	//------------------------------------------------------------------------------------------------------------------
	protected function CheckWildcards(array &$args)
	{
		// For each wildcard :
		foreach($this->Endpoint()->Wildcards() as $k => $v)
		{
			if($v === null)
			{
				continue;
			}

			// Mandatory parameter check
			if(!isset($args[$k]))
			{
				$description = str_replace('{{PARAMETER}}', $k, TApi::ERROR_MANDATORY_PARAMETER);

				$this->ExitError(TApi::ERROR_PARAMETERS, $description);
			}

			// Value check
			if(isset($args[$k]))
			{
				$message = $v->Check($args[$k], AType::CHECK_LEVEL_MANDATORY);

				if($message !== '')
				{
					$description = str_replace('{{PARAMETER}}', $k      , TApi::ERROR_INCORRECT);
					$description = str_replace('{{MESSAGE}}'  , $message, $description);

					$this->ExitError(TApi::ERROR_PARAMETERS, $description);
				}
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adapts parameter values
	//------------------------------------------------------------------------------------------------------------------
	protected function AdaptParameters(array &$args)
	{
		// For each parameter :
		foreach($this->Parameters() as $k => $v)
		{
			// If parameter value was not declared :
			// Applies the parameter default value
			if(!isset($args[$k]))
			{
				$args[$k] = $v->Default();
			}

			// Adapts parameter
			$v->Type()->Adapt($args[$k]);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if all parameters are provided as arguments
	//------------------------------------------------------------------------------------------------------------------
	protected function CheckParameters(array &$args)
	{
		// For each parameter :
		foreach($this->Parameters() as $v)
		{
			$name = $v->Name();

			// Mandatory parameter check
			if($v->IsMandatory() && !isset($args[$name]))
			{
				$description = str_replace('{{PARAMETER}}', $name, TApi::ERROR_MANDATORY_PARAMETER);

				$this->ExitError(TApi::ERROR_PARAMETERS, $description);
			}

			// Value check
			if(isset($args[$name]))
			{
				$message = $v->Type()->Check($args[$name], AType::CHECK_LEVEL_MANDATORY);

				if($message !== '')
				{
					$description = str_replace('{{PARAMETER}}', $name   , TApi::ERROR_INCORRECT);
					$description = str_replace('{{MESSAGE}}'  , $message, $description);

					$this->ExitError(TApi::ERROR_PARAMETERS, $description);
				}
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates the body check level
	//------------------------------------------------------------------------------------------------------------------
	protected function BodyCheckLevel() : int
	{
		return AType::CHECK_LEVEL_MANDATORY;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given response is correct
	//------------------------------------------------------------------------------------------------------------------
	protected function CheckBody(mixed $body)
	{

		if($this->BodyType() === null)
		{
			return;
		}

		$level = AType::CHECK_LEVEL_MANDATORY;

		if($this->Verb() === 'PATCH')
		{
			$level = AType::CHECK_LEVEL_NONE;
		}

		$description = $this->BodyType()->Check($body, $level);

		if($description !== '')
		{
			$this->ExitError(TApi::ERROR_BODY, $description);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adapts response
	//------------------------------------------------------------------------------------------------------------------
	protected function AdaptResponse(mixed &$response)
	{
		if($this->ResponseType() !== null)
		{
			$this->ResponseType()->Adapt($response);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exists because expected resource was not found
	//------------------------------------------------------------------------------------------------------------------
	public function ExitNotFound()
	{
		$this->Api()->Exit404($this->Endpoint());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exists because a conflict exists
	//------------------------------------------------------------------------------------------------------------------
	public function ExitConflict()
	{
		$this->Api()->Exit409($this->Endpoint());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with an error
	//------------------------------------------------------------------------------------------------------------------
	public function ExitError(string $message = '', string $description = '', string $url = '')
	{
		$this->Api()->Exit400($this->Endpoint(), $message, $description, $url);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Response for a GETALL
	//------------------------------------------------------------------------------------------------------------------
	protected function ResponseGetall(array $args) : array
	{
		// Gets special arguments
		$subsets = $this->SubsetsArgument($args);
		$fields  = $this->FieldsArgument( $args);
		$sorts   = $this->SortsArgument(  $args);
		$offset  = $this->OffsetArgument( $args);
		$limit   = $this->LimitArgument(  $args);

		// If an offset was provided through special arguments, but no limit :
		// Limit is automatically set to 1
		if(($offset > 0) && ($limit === 0))
		{
			$limit = 1;
		}

		// Converts other arguments into filters
		$args = $this->ArgumentsToFilters($args);

		// Calls the callback
		$res = call_user_func(
			$this->Callback(),
			$this,
			$args,
			array(),
			$subsets,
			$fields,
			$sorts,
			$offset,
			$limit);

		// Response must be an array
		if(!is_array($res))
		{
			$this->ExitError(TApi::ERROR_COLLECTION_RESPONSE);
		}

		// If a partial response was delivered :
		// Checks if it is not, in fact, a complete response
		$this->_Partial = array();

		if($this->Api()->IsPartialResponse($res))
		{
			$totalCount = $this->Api()->PartialTotalCount($res);
			$res        = $this->Api()->PartialData(      $res);

			if(($offset > 0) || (count($res) < $totalCount))
			{
				$this->_Partial['limit'     ] = $limit;
				$this->_Partial['offset'    ] = $offset;
				$this->_Partial['totalCount'] = $totalCount;
			}
		}

		// If a complete response was delivered :
		// Checks if it is not, in fact, a partial response
		elseif(($limit > 0) && (($offset > 0) || (count($res) >= $limit)))
		{
			$this->_Partial['limit'     ] = $limit;
			$this->_Partial['offset'    ] = $offset;
			$this->_Partial['totalCount'] = 0;

			$res = array_slice($res, 0, $limit);
		}

		// Gets arguments from callback
		$callbackArguments = $this->Core()->FunctionArguments($this->Callback());

		// If callback did not manage a "filters" argument :
		// Response has not been filtered => does it now
		if(!empty($args) && !isset($callbackArguments['filters']))
		{
			$this->FilterCollection($res, $args);
		}

		// If callback did not manage a "subsets" argument :
		// Response has not been limited to subsets => does it now
		if(!empty($subsets) && !isset($callbackArguments['subsets']))
		{
			$this->LimitCollectionToSubsets($res, $subsets);
		}

		// If callback did not manage a "fields" argument :
		// Response has not been limited to fields => does it now
		if(!empty($fields) && !isset($callbackArguments['fields']))
		{
			$this->LimitCollectionToFields($res, $fields);
		}

		// If callback did not manage a "sorts" argument :
		// Response has not been sorted => does it now
		if(!empty($sorts) && !isset($callbackArguments['sorts']))
		{
			$this->SortCollection($res, $sorts);
		}

		// Ensures that result is not indexed
		// (can be a bug : must json encode as a collection, not an object)
		$res = array_values($res);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Response for a GET
	//------------------------------------------------------------------------------------------------------------------
	protected function ResponseGet(array $args) : mixed
	{
		// Gets special arguments
		$subsets = $this->SubsetsArgument($args);
		$fields  = $this->FieldsArgument( $args);

		// Converts other arguments into filters
		$args = $this->ArgumentsToFilters($args);

		// Calls the callback
		$res = call_user_func(
			$this->Callback(),
			$this,
			$args,
			array(),
			$subsets,
			$fields);

		// Gets arguments from callback
		$callbackArguments = $this->Core()->FunctionArguments($this->Callback());

		// If callback did not manage a "subsets" argument :
		// Response has not been limited to subsets => does it now
		if(!empty($subsets) && !isset($callbackArguments['subsets']))
		{
			$this->LimitRecordToSubsets($res, $subsets);
		}

		// If callback did not manage a "fields" argument :
		// Response has not been limited to fields => does it now
		if(!empty($fields) && !isset($callbackArguments['fields']))
		{
			$this->LimitRecordToFields($res, $fields);
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Response for a POST
	//------------------------------------------------------------------------------------------------------------------
	protected function ResponsePost(array $args, array $body) : mixed
	{
		// Calls the callback
		$res = call_user_func(
			$this->Callback(),
			$this,
			$args,
			$body);

		// A POST usually returns the inserted id
		if((is_string($res) && ($res !== '')) || is_int($res))
		{
			return $res;
		}

		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Response for a PUT
	//------------------------------------------------------------------------------------------------------------------
	protected function ResponsePut(array $args, array $body) : mixed
	{
		// Adds default values to fields that were not provided in body
		$defaultBody = array();

		if($this->BodyType() !== null)
		{
			$defaultBody = $this->BodyType()->Default();
		}
		else
		{
			$defaultBody = array();
		}

		foreach($body as $k => $v)
		{
			$defaultBody[$k] = $v;
		}

		$body = $defaultBody;

		// Calls the callback
		$res = call_user_func(
			$this->Callback(),
			$this,
			$args,
			$body);

		// A PUT usually returns the number of affected rows
		if(is_int($res))
		{
			return $res;
		}

		return 0;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Response for a PATCH
	//------------------------------------------------------------------------------------------------------------------
	protected function ResponsePatch(array $args, array $body) : mixed
	{
		// Calls the callback
		$res = call_user_func(
			$this->Callback(),
			$this,
			$args,
			$body);

		// A PATCH usually returns the number of affected rows
		if(is_int($res))
		{
			return $res;
		}

		return 0;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Response for a DELETE
	//------------------------------------------------------------------------------------------------------------------
	protected function ResponseDelete(array $args, array $body) : mixed
	{
		// Calls the callback
		$res = call_user_func(
			$this->Callback(),
			$this,
			$args,
			$body);

		// A DELETE usually returns the number of affected rows
		if(is_int($res))
		{
			return $res;
		}

		return 0;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Response
	//------------------------------------------------------------------------------------------------------------------
	protected function Response(array $args, mixed $body) : mixed
	{
		// Depending on verb :
		switch($this->Verb())
		{
			case 'GETALL' : return $this->ResponseGetall($args);
			case 'GET'    : return $this->ResponseGet(   $args);
			case 'POST'   : return $this->ResponsePost(  $args, $body);
			case 'PUT'    : return $this->ResponsePut(   $args, $body);
			case 'PATCH'  : return $this->ResponsePatch( $args, $body);
			case 'DELETE' : return $this->ResponseDelete($args, $body);
		}

		// Default case
		return call_user_func($this->Callback(), $this, $args, $body);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Output
	//------------------------------------------------------------------------------------------------------------------
	protected function Output(mixed $response)
	{
		// POST case
		if($this->Verb() === 'POST')
		{
			$id = (is_string($response) || is_int($response)) ? $response : '';

			$this->Api()->Exit201($this->Endpoint(), $id);
		}

		// GETALL partial case
		if(!empty($this->_Partial))
		{
			$offset     = $this->_Partial['offset'    ];
			$limit      = $this->_Partial['limit'     ];
			$totalCount = $this->_Partial['totalCount'];

			$this->Api()->Exit206($this->Endpoint(), $response, $offset, $limit, $totalCount);
		}

		// Default case
		$this->Api()->Exit200($this->Endpoint(), $response);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Run
	//------------------------------------------------------------------------------------------------------------------
	public function Run(array $args, mixed $body)
	{
		// Adapts wildcards
		$this->AdaptWildcards($args);

		// Checks wildcards
		$this->CheckWildcards($args);

		// Adapts parameters
		$this->AdaptParameters($args);

		// Checks parameters
		$this->CheckParameters($args);

		// Checks body
		$this->CheckBody($body);

		// Gets response
		$response = $this->Response($args, $body);

		// Adapts response
		$this->AdaptResponse($response);

		// Outputs response
		$this->Output($response);
	}
}