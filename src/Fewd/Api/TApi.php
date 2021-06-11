<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\AModule;
use Fewd\Core\TCore;
use Fewd\Router\TRouter;


class TApi extends AModule
{
	// Router
	private $_Router;
	public final function Router() : TRouter { return $this->_Router; }

	// Root
	private $_Root;
	public final function Root() : string { return $this->_Root; }

	// Api Url
	private $_Url;
	public final function Url() : string { return $this->_Url; }

	// Current path under Api
	private $_Path;
	public final function Path() : string { return $this->_Path; }

	// Indicates if on developer portal
	private $_IsPortal;
	public final function IsPortal() : bool { return $this->_IsPortal; }

	// Doc version
	private $_DocVersion;
	public final function DocVersion() : string { return $this->_DocVersion; }

	// Title
	private $_Title;
	public final function Title() : string { return $this->_Title; }

	// Description
	private $_Description;
	public final function Description() : string { return $this->_Description; }

	// Terms of service
	private $_TermsOfService;
	public final function TermsOfService() : string { return $this->_TermsOfService; }

	// Contact name
	private $_ContactName;
	public final function ContactName() : string { return $this->_ContactName; }

	// Contact url
	private $_ContactUrl;
	public final function ContactUrl() : string { return $this->_ContactUrl; }

	// Contact email
	private $_ContactEmail;
	public final function ContactEmail() : string { return $this->_ContactEmail; }

	// License name
	private $_LicenseName;
	public final function LicenseName() : string { return $this->_LicenseName; }

	// License url
	private $_LicenseUrl;
	public final function LicenseUrl() : string { return $this->_LicenseUrl; }

	// Default maximum records number that could be GET at once on any endpoint
	private $_DefaultMaximumLimit;
	public final function DefaultMaximumLimit() : int { return $this->_DefaultMaximumLimit; }

	// Endpoints
	private $_Endpoints = array();
	public final function Endpoints() : array                            { return $this->_Endpoints;                }
	public       function Endpoint(   string $path) : TEndpoint          { return $this->_Endpoints[$path] ?? null; }
	public       function HasEndpoint(string $path) : bool               { return isset($this->_Endpoints[$path]);  }
	protected    function AddEndpoint(string $path, TEndpoint $endpoint) { $this->_Endpoints[$path] = $endpoint;    }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore   $core,
		TRouter $router,
		string  $root,
		string  $DocVersion,
		string  $title               = '',
		string  $description         = '',
		string  $termsOfService      = '',
		string  $contactName         = '',
		string  $contactUrl          = '',
		string  $contactEmail        = '',
		string  $licenseName         = '',
		string  $licenseUrl          = '',
		int     $defaultMaximumLimit = 0)
	{
		parent::__construct($core);

		$this->_Router               = $router;
		$this->_Root                 = $root;
		$this->_DocVersion           = $DocVersion;
		$this->_Title                = $title;
		$this->_Description          = $description;
		$this->_TermsOfService       = $termsOfService;
		$this->_ContactName          = $contactName;
		$this->_ContactUrl           = $contactUrl;
		$this->_ContactEmail         = $contactEmail;
		$this->_LicenseName          = $licenseName;
		$this->_LicenseUrl           = $licenseUrl;
		$this->_DefaultMaximumLimit  = $defaultMaximumLimit;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Root                 = $this->DefineRoot();
		$this->_Url                  = $this->DefineUrl();
		$this->_Path                 = $this->DefinePath();
		$this->_IsPortal             = $this->DefineIsPortal();
		$this->_DocVersion           = $this->DefineDocVersion();
		$this->_Title                = $this->DefineTitle();
		$this->_Description          = $this->DefineDescription();
		$this->_TermsOfService       = $this->DefineTermsOfService();
		$this->_ContactName          = $this->DefineContactName();
		$this->_ContactUrl           = $this->DefineContactUrl();
		$this->_ContactEmail         = $this->DefineContactEmail();
		$this->_LicenseName          = $this->DefineLicenseName();
		$this->_LicenseUrl           = $this->DefineLicenseUrl();
		$this->_DefaultMaximumLimit  = $this->DefineDefaultMaximumLimit();

		$this->InitRouter();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Root
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineRoot() : string
	{
		if($this->Root() === '')
		{
			return 'api';
		}

		return $this->Root();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Url
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineUrl() : string
	{
		return $this->Core()->Protocol() . $this->Core()->Domain() . '/' . $this->Root();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : IsPortal
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineIsPortal() : bool
	{
		$currentPath = $this->Core()->CurrentPath();

		return (($currentPath === $this->Root()) || ($currentPath === $this->Root() . '/'));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Path
	//------------------------------------------------------------------------------------------------------------------
	protected function DefinePath() : string
	{
		$currentPath = $this->Core()->CurrentPath();

		if($this->Core()->StartsWith($currentPath, $this->Root() . '/'))
		{
			$res = substr($currentPath, strlen($this->Root()) + 1);
		}
		else
		{
			$res = '';
		}

		while(substr($res, -1) === '/')
		{
			$res = substr($res, 0, -1);
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Doc version
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDocVersion() : string
	{
		if($this->DocVersion() === '')
		{
			return '1.0.0';
		}

		return $this->DocVersion();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Title
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineTitle() : string
	{
		if($this->Title() === '')
		{
			return $this->Root();
		}

		return $this->Title();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Description
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDescription() : string
	{
		return $this->Description();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Terms of service
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineTermsOfService() : string
	{
		return $this->TermsOfService();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Contact name
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineContactName() : string
	{
		return $this->ContactName();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Contact url
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineContactUrl() : string
	{
		return $this->ContactUrl();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Contact email
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineContactEmail() : string
	{
		return $this->ContactEmail();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : License name
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineLicenseName() : string
	{
		return $this->LicenseName();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : License url
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineLicenseUrl() : string
	{
		return $this->LicenseUrl();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : DefaultMaximumLimit
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDefaultMaximumLimit() : int
	{
		$res = $this->DefaultMaximumLimit();

		if($res < 1)
		{
			$res = 100;
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Inits router
	//------------------------------------------------------------------------------------------------------------------
	protected function InitRouter()
	{
		// Api portal
		$this->Router()->AddStrictRule($this->Root()      , $this->PortalRouteId());
		$this->Router()->AddStrictRule($this->Root() . '/', $this->PortalRouteId());

		$this->Router()->AddAction($this->PortalRouteId(), $this->PortalAction());

		// Api endpoints
		$this->Router()->AddRegexpRule('^' . $this->Root() . '/(.*)$', $this->Root(), $this->PathArg());

		$api = $this;
		$callback = function(array $args, string $verb) use($api)
		{
			return $api->Run($args, $verb);
		};

		$this->Router()->AddAction($this->Root(), $callback);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Portal route id
	//------------------------------------------------------------------------------------------------------------------
	protected function PortalRouteId() : string
	{
		return '__portal';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Portal action
	//------------------------------------------------------------------------------------------------------------------
	protected function PortalAction() : callable
	{
		$api = $this;

		$res = function(array $args, string $verb) use($api)
		{
			$api->Nop($args);
			$api->Nop($verb);

			echo 'PORTAL';
			exit();
		};

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Path argument (used to get the endpoint path from the complete path, via the router)
	//------------------------------------------------------------------------------------------------------------------
	protected function PathArg() : string
	{
		return '__path';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Subset argument
	//------------------------------------------------------------------------------------------------------------------
	public function SubsetArg() : string
	{
		return 'subset';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fields argument
	//------------------------------------------------------------------------------------------------------------------
	public function FieldsArg() : string
	{
		return 'fields';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Sort argument
	//------------------------------------------------------------------------------------------------------------------
	public function SortArg() : string
	{
		return 'sort';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Offset argument
	//------------------------------------------------------------------------------------------------------------------
	public function OffsetArg() : string
	{
		return 'offset';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limit argument
	//------------------------------------------------------------------------------------------------------------------
	public function LimitArg() : string
	{
		return 'limit';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a given string argument from a given arguments array, after removing it
	//------------------------------------------------------------------------------------------------------------------
	protected function StringArgValue(string $arg, array &$args) : string
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
	protected function IntArgValue(string $arg, array &$args) : string
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
	// Indicates if a given path with potential wildcards corresponds to current path
	//------------------------------------------------------------------------------------------------------------------
	protected function IsCurrentPath(string $path) : bool
	{
		if(strpos($path, '{') !== false)
		{
			$regexp = preg_replace('#{.*}#', '([^/]+)', $path);

			return (preg_match('#^' . $regexp . '(/.*){0,1}$#', $this->Path(), $matches) === 1);
		}

		return (($this->Path() === $path) || $this->Core()->StartsWith($this->Path(), $path . '/'));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new endpoint, only if the path matches, or if on portal (with OpenApi doc generation)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareEndpoint(string $path, int $maximumLimit = 0) : ?TEndpoint
	{
		if($this->IsPortal() || $this->IsCurrentPath($path))
		{
			return $this->MakeEndpoint($path, $maximumLimit);
		}

		return null;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new operation on a endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareOperation(
		?TEndpoint $endpoint,
		string     $verb,
		callable   $callback,
		string     $summary     = '',
		string     $description = '',
		string     $id          = '') : ?TOperation
	{
		if($endpoint === null)
		{
			return null;
		}

		return $this->MakeOperation($endpoint, $verb, $callback, $summary, $description, $id);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Builds an Url in the API to a given path
	//------------------------------------------------------------------------------------------------------------------
	public function BuildUrl(string $path)
	{
		return $this->Core()->Protocol() . $this->Core()->Domain . '/' . $this->Root() . '/' . $path;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given endpoint without wildcards corresponds to a given Url path and args
	//------------------------------------------------------------------------------------------------------------------
	// Example : "/car"
	//------------------------------------------------------------------------------------------------------------------
	protected function CheckStandardEndpoint(TEndpoint $endpoint, string $path, array &$args) : bool
	{
		// If the given path is exactly the endpoint's path :
		// Indicates that the endpoint matches
		if($endpoint->Path() === $path)
		{
			return true;
		}

		// If the given path does not start with the endpoint's path :
		// Indicates that the endpoint does not match
		if(!$this->Core()->StartsWith($path, $endpoint->Path() . '/'))
		{
			return false;
		}

		// Otherwise :
		// Gets the subset and indicates that the endpoint matches
		$args[$this->SubsetArg()] = substr($path, strlen($endpoint->Path()) + 1);

		return true;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given endpoint with wildcards corresponds to a given Url path and args
	//------------------------------------------------------------------------------------------------------------------
	// Example : "/car/{carId}"
	//------------------------------------------------------------------------------------------------------------------
	protected function CheckWildcardEndpoint(TEndpoint $endpoint, string $path, array &$args) : bool
	{
		// Transforms path into a regexp
		$regexp    = $endpoint->Path();
		$wildcards = $endpoint->Wildcards();

		foreach($wildcards as $k => $v)
		{
			$regexp = str_replace('{' . $v . '}', '([^/]+)', $regexp);
		}

		// Adds subset management
		$wildcards[] = $this->SubsetArg();
		$regexp     .= '(/.*){0,1}';

		// If path matches :
		if(preg_match('#' . $regexp . '#', $path, $matches) === 1)
		{
			// Stores wildcard values as new args
			foreach($wildcards as $k => $v)
			{
				if(isset($matches[$k + 1]))
				{
					$args[$v] = $matches[$k + 1];
				}
			}

			// Indicates that the endpoint matches
			return true;
		}

		// Otherwise :
		// The endpoint does not match
		return false;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the endpoint + args corresponding to a given Url path
	//------------------------------------------------------------------------------------------------------------------
	protected function UrlToEndpoint(string $path, array &$args) : TEndpoint|null
	{
		// Gets endpoints in reverse order
		// (to proceed with the most detailled paths first)
		$endpoints = $this->Endpoints();
		array_reverse($endpoints);

		// For each declared endpoint :
		foreach($endpoints as $v)
		{
			$hasWildcard = (strpos($v->Path(), '{') !== false);

			if($hasWildcard)
			{
				if($this->CheckWildcardEndpoint($v, $path, $args))
				{
					return $v;
				}
			}
			else
			{
				if($this->CheckStandardEndpoint($v, $path, $args))
				{
					return $v;
				}
			}
		}

		// No endpoint found
		return null;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if the given response is counted (i.e. array('count' => <int>, 'data' => <array>))
	//------------------------------------------------------------------------------------------------------------------
	protected function IsCountedResponse(array $response) : bool
	{
		return (isset(   $response['count']) &&
		        isset(   $response['data' ]) &&
				is_int(  $response['count']) &&
		        is_array($response['data' ]) &&
				(count($response) === 2));
 	}


	 //------------------------------------------------------------------------------------------------------------------
	 // Indicates if the given response is a collection (i.e. a set of objects)
	 //------------------------------------------------------------------------------------------------------------------
	 public function IsCollectionResponse(array $response) : bool
	 {
		 foreach($response as $k => $v)
		 {
			 if(is_int($k))
			 {
				 return true;
			 }

			 return false;
		 }

		 return false;
	 }


	 //------------------------------------------------------------------------------------------------------------------
	 // Indicates if the given response is an object (i.e. a set of attributes/values)
	 //------------------------------------------------------------------------------------------------------------------
	 public function IsObjectResponse(array $response) : bool
	 {
		return !$this->IsCollectionResponse($response) && !$this->IsCountedResponse($response);
	 }


	 //------------------------------------------------------------------------------------------------------------------
	// Outputs a response header
	//------------------------------------------------------------------------------------------------------------------
	protected function Header(TEndpoint $endpoint = null)
	{
		// The "*" value for allowed verbs is not implemented on all browsers,
		// so it must be replaced by a list of major methods
		if($endpoint === null)
		{
			$allowedVerbs = 'OPTIONS';
		}
		else
		{
			$allowedVerbs = $endpoint->AllowedVerbs();
		}

		// Default maximum age
		if($endpoint === null)
		{
			$maximumAge = 3600;
		}
		else
		{
			$maximumAge = $endpoint->MaximumAge();
		}

		// Outputs headers
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: ' . $allowedVerbs);
		header('Access-Control-Max-Age: '       . $maximumAge  );
		header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');

		header('Content-Type: application/json; charset=UTF-8');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a given Http code
	//------------------------------------------------------------------------------------------------------------------
	protected function Exit(
		int       $code,
		TEndpoint $endpoint    = null,
		string    $message     = '',
		string    $description = '',
		string    $url         = '')
	{
		// Prepares result
		if($message === '')
		{
			$message = $this->Router()->CodeMessage($code);
		}

		if($code >= 400)
		{
			$data = array('error' => $message);

			if($description !== '')
			{
				$data['error_description'] = $description;
			}

			if($url !== '')
			{
				$data['error_uri'] = $url;
			}
		}
		else
		{
			$data = array('message' => $message);
		}

		// Formats into Json format
		$json = json_encode($data, JSON_PRETTY_PRINT);

		// Outputs result
		$this->Header($endpoint);
		http_response_code($code);
		echo $json;
		exit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 200 code + response
	//------------------------------------------------------------------------------------------------------------------
	protected function Exit200(array $response, TEndpoint $endpoint)
	{
		// Prepares result into Json format
		$json = json_encode($response, JSON_PRETTY_PRINT);

		// Outputs result
		$this->Header($endpoint);
		http_response_code(200);
		echo $json;
		exit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 201 code
	//------------------------------------------------------------------------------------------------------------------
	// 201 corresponds to a successful POST. The newly created id must be returned via a "location" header
	//------------------------------------------------------------------------------------------------------------------
	protected function Exit201(array $response, string $path, TEndpoint $endpoint)
	{
		// Prepares the location to the newly created element
		// (Id is, by convention, the first element provided in response data)
		$id       = '';
		$location = '';
		foreach($response as $v)
		{
			$id       = $v;
			$location = $this->BuildUrl($path . '/' . $id);
			break;
		}

		// Prepares result into Json format
		$data = array('message' => $this->Router()->CodeMessage(201));

		if($id !== '')
		{
			$data['id'      ] = $id;
			$data['location'] = $location;
		}

		$json = json_encode($data, JSON_PRETTY_PRINT);

		// Outputs result
		$this->Header($endpoint);
		header('Location: ' . $location);
		http_response_code(201);
		echo $json;
		exit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 206 code
	//------------------------------------------------------------------------------------------------------------------
	// 206 corresponds to a partial GET
	//------------------------------------------------------------------------------------------------------------------
	protected function Exit206(array $response, int $offset, int $limit, int $count, TEndpoint $endpoint)
	{
		// Prepares result into Json format
		$json = json_encode($response, JSON_PRETTY_PRINT);

		// Outputs result
		$this->Header($endpoint);
		header('Content-Range: ' . $offset . '-' . ($offset + $limit - 1) . '/' . ($count < 0 ? '*' : $count));
		header('Accept-Ranges: bytes');
		http_response_code(206);
		echo $json;
		exit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a GET
	//------------------------------------------------------------------------------------------------------------------
	protected function RunGet(array $response, string $subset, int $offset, int $limit, TEndpoint $endpoint)
	{
		// If no response :
		// This is an error
		if($response === null)
		{
			$this->Exit(404, $endpoint);
		}

		// If a subset was provided,
		// And if response is not an object :
		// This is an error
		if(($subset !== '') && !$this->IsCollectionResponse($response))
		{
			$this->Exit(404, $endpoint);
		}

		// If response is counted :
		// Outputs a partial response if it is not complete
		if($this->IsCountedResponse($response))
		{
			$count    = $response['count'];
			$response = $response['data' ];

			if(($offset !== 0) || ($count > $limit))
			{
				$this->Exit206($response, $offset, $limit, $count, $endpoint);
			}
		}

		// Otherwise,
		// If response is a collection :
		// Outputs a partial response if it is not complete
		elseif($this->IsCollectionResponse($response))
		{
			if(($offset !== 0) || count($response) >= $limit)
			{
				$this->Exit206($response, $offset, $limit, -1, $endpoint);
			}
		}

		// Outputs a standard response in any other case
		$this->Exit200($response, $endpoint);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a POST
	//------------------------------------------------------------------------------------------------------------------
	protected function RunPost(array $response, string $path, TEndpoint $endpoint)
	{
		if($response === null)
		{
			$this->Exit(503, $endpoint);
		}

		$this->Exit201($response, $path, $endpoint);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs any verb (other than GET or POST)
	//------------------------------------------------------------------------------------------------------------------
	protected function RunAny(array $response, TEndpoint $endpoint)
	{
		if($response === null)
		{
			$this->Exit(503, $endpoint);
		}

		$this->Exit(200, $endpoint);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs the Api
	//------------------------------------------------------------------------------------------------------------------
	protected function Run(array $args, string $verb)
	{
		// If no path found :
		// Nothing to run
		if(!isset($args[$this->PathArg()]))
		{
			$this->Exit(404);
		}

		// If no corresponding endpoint :
		// Nothing to run
		$path     = $this->StringArgValue($this->PathArg(), $args);
		$endpoint = $this->UrlToEndpoint($path, $args);

		if($endpoint === null)
		{
			$this->Exit(404);
		}

		// If verb is OPTIONS :
		// Returns options
		if($verb === 'OPTIONS')
		{
			$this->Exit200(array(), $endpoint);
		}

		// If verb is not allowed for this endpoint :
		// Nothing to run
		if(!$endpoint->HasOperation($verb))
		{
			$this->Exit(405, $endpoint);
		}

		// Gets special arguments
		$subset =     $this->StringArgValue($this->SubsetArg(), $args);
		$fields =     $this->StringArgValue($this->FieldsArg(), $args);
		$sort   =     $this->StringArgValue($this->SortArg()  , $args);
		$offset = max($this->IntArgValue(   $this->OffsetArg(), $args), 0);
		$limit  = max($this->IntArgValue(   $this->LimitArg() , $args), $endpoint->MaximumLimit());

		// If a subset was provided for a verb other than GET :
		// This is an error (the endpoint must be explicitely declared)
		if(($verb !== 'GET') && ($subset !== ''))
		{
			$this->Exit(400, $endpoint);
		}

		// Gets the body from API call
		$body = json_decode(file_get_contents("php://input"));
		if($body === null)
		{
			$body = array();
		}

		// Gets response data
		$response = $endpoint->Response($verb, $args, $body, $subset, $fields, $sort, $offset, $limit);

		// Outputs the response, depending on the verb
		if($verb === 'GET')
		{
			$this->RunGet($response, $subset, $offset, $limit, $endpoint);
		}
		elseif($verb === 'POST')
		{
			$this->RunPost($response, $path, $endpoint);
		}
		else
		{
			$this->RunAny($response, $endpoint);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function MakeEndpoint(string $path, int $maximumLimit = 0) : TEndpoint
	{
		$res = new TEndpoint($this->Core(), $this, $path, $maximumLimit);
		$res->Init();

		$this->AddEndpoint($res->Path(), $res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Operation
	//------------------------------------------------------------------------------------------------------------------
	public function MakeOperation(
		TEndpoint $endpoint,
		string    $verb,
		callable  $callback,
		string    $summary     = '',
		string    $description = '',
		string    $id          = '') : TOperation
	{
		$res = new TOperation($this->Core(), $this, $endpoint, $verb, $callback, $summary, $description, $id);
		$res->Init();

		$endpoint->AddOperation($verb, $res);

		return $res;
	}
}