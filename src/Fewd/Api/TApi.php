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
	// Error constants
	public const ERROR_MANDATORY_PARAMETER = 'Mandatory parameter \'{{PARAMETER}}\' is missing.';
	public const ERROR_MANDATORY_PROPERTY  = 'Mandatory property \'{{PROPERTY}}\' is missing.';
	public const ERROR_INCORRECT           = 'Incorrect value for parameter \'{{PARAMETER}}\' : {{MESSAGE}}';
	public const ERROR_SUBSET              = 'Operation is not allowed on resource subset.';
	public const ERROR_PARAMETERS          = 'Wrong parameters.';
	public const ERROR_RESPONSE            = 'Wrong \'{{TYPE}}\' response.';
	public const ERROR_NUMERIC             = 'value must be numeric.';
	public const ERROR_MINIMUM             = 'value {{VALUE}} is out of range (lesser than {{MINIMUM}}).';
	public const ERROR_MAXIMUM             = 'value {{VALUE}} is out of range (greater than {{MAXIMUM}}).';
	public const ERROR_ENUMS               = 'value {{VALUE}} does not belong to enumerated values ({{ENUMS}}).';
	public const ERROR_STRING              = 'value must be a string (or a numeric value convertible to string).';
	public const ERROR_MINIMUM_LENGTH      = 'value ({{VALUE}}) is shorter than {{MINIMUM}} characters.';
	public const ERROR_MAXIMUM_LENGTH      = 'value ({{VALUE}}) is longer than {{MAXIMUM}} characters.';
	public const ERROR_PATTERN             = 'value does not match with the expected pattern ({{PATTERN}}).';
	public const ERROR_COLLECTION          = 'value must be a collection.';
	public const ERROR_COLLECTION_TYPE     = 'value must be a collection of \'{{TYPE}}\' items.';
	public const ERROR_ENTITY              = 'value is not a \'{{TYPE}}\' entity.';

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

	// Api logo Url
	private $_LogoUrl;
	public final function LogoUrl() : string { return $this->_LogoUrl; }

	// Api favicon Url
	private $_FaviconUrl;
	public final function FaviconUrl() : string { return $this->_FaviconUrl; }

	// Doc version
	private $_DocVersion;
	public final function DocVersion() : string { return $this->_DocVersion; }

	// Doc relative Url on current domain
	private $_DocUrl;
	public final function DocUrl() : string { return $this->_DocUrl; }

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

	// ExternalDocDescription
	private $_ExternalDocDescription;
	public final function ExternalDocDescription() : string { return $this->_ExternalDocDescription; }

	// ExternalDocUrl
	private $_ExternalDocUrl;
	public final function ExternalDocUrl() : string { return $this->_ExternalDocUrl; }

	// Endpoints
	private $_Endpoints;
	public    final function Endpoints() : array                            { return $this->_Endpoints;                }
	public    final function Endpoint(   string $path) : TEndpoint          { return $this->_Endpoints[$path] ?? null; }
	public    final function HasEndpoint(string $path) : bool               { return isset($this->_Endpoints[$path]);  }
	protected       function AddEndpoint(string $path, TEndpoint $endpoint) { $this->_Endpoints[$path] = $endpoint;    }

	// Indicates if on developer portal
	private $_IsPortal;
	public final function IsPortal() : bool { return $this->_IsPortal; }

	// Other resources to transfer to endpoint resolution (such as database, etc.)
	// Resources
	private $_Resources;
	public final function Resources()             : array        { return $this->_Resources;              }
	public final function Resource(   string $id) : object       { return $this->_Resources[$id] ?? null; }
	public final function HasResource(string $id) : bool         { return isset($this->_Resources[$id]);  }
	public       function AddResource(string $id, object $value) { $this->_Resources[$id] = $value;       }

	// Last error code encountered in an Api call
	protected $_LastErrorCode;
	public final function LastErrorCode() : int { return $this->_LastErrorCode; }

	// Entities
	private $_Entities;
	public final function Entities() : array                       { return $this->_Entities;                     }
	public final function Entity(   string $name) : TTypeEntity    { return $this->_Entities[$name] ?? null;      }
	public final function HasEntity(string $name) : bool           { return isset($this->_Entities[$name]);       }
	protected    function AddEntity(TTypeEntity $entity)           { $this->_Entities[$entity->Name()] = $entity; }

	// Types
	private $_Types;
	public final function Types() : array               { return $this->_Types;                 }
	public final function Type(   string $name) : AType { return $this->_Types[$name] ?? null;  }
	public final function HasType(string $name) : bool  { return isset($this->_Types[$name]);   }
	protected    function AddType(AType $type)          { $this->_Types[$type->Name()] = $type; }

	// Headers
	protected $_Headers = null;


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TRouter    $router,
		string     $root                   = '',
		string     $docVersion             = '',
		string     $docUrl                 = '',
		string     $logoUrl                = '',
		string     $faviconUrl             = '',
		string     $title                  = '',
		string     $description            = '',
		string     $termsOfService         = '',
		string     $contactName            = '',
		string     $contactUrl             = '',
		string     $contactEmail           = '',
		string     $licenseName            = '',
		string     $licenseUrl             = '',
		string     $externalDocDescription = '',
		string     $externalDocUrl         = '')
	{
		parent::__construct($core);

		$this->_Router                 = $router;
		$this->_Root                   = $root;
		$this->_DocVersion             = $docVersion;
		$this->_DocUrl                 = $docUrl;
		$this->_LogoUrl                = $logoUrl;
		$this->_FaviconUrl             = $faviconUrl;
		$this->_Title                  = $title;
		$this->_Description            = $description;
		$this->_TermsOfService         = $termsOfService;
		$this->_ContactName            = $contactName;
		$this->_ContactUrl             = $contactUrl;
		$this->_ContactEmail           = $contactEmail;
		$this->_LicenseName            = $licenseName;
		$this->_LicenseUrl             = $licenseUrl;
		$this->_ExternalDocDescription = $externalDocDescription;
		$this->_ExternalDocUrl         = $externalDocUrl;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Root                   = $this->DefineRoot();
		$this->_Url                    = $this->DefineUrl();
		$this->_Path                   = $this->DefinePath();
		$this->_DocVersion             = $this->DefineDocVersion();
		$this->_DocUrl                 = $this->DefineDocUrl();
		$this->_LogoUrl                = $this->DefineLogoUrl();
		$this->_FaviconUrl             = $this->DefineFaviconUrl();
		$this->_Title                  = $this->DefineTitle();
		$this->_Description            = $this->DefineDescription();
		$this->_TermsOfService         = $this->DefineTermsOfService();
		$this->_ContactName            = $this->DefineContactName();
		$this->_ContactUrl             = $this->DefineContactUrl();
		$this->_ContactEmail           = $this->DefineContactEmail();
		$this->_LicenseName            = $this->DefineLicenseName();
		$this->_LicenseUrl             = $this->DefineLicenseUrl();
		$this->_ExternalDocDescription = $this->DefineExternalDocDescription();
		$this->_ExternalDocUrl         = $this->DefineExternalDocUrl();
		$this->_Endpoints              = $this->DefineEndpoints();
		$this->_IsPortal               = $this->DefineIsPortal();
		$this->_Resources              = $this->DefineResources();
		$this->_LastErrorCode          = $this->DefineLastErrorCode();
		$this->_Entities               = $this->DefineEntities();
		$this->_Types                  = $this->DefineTypes();

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
	// Define : Doc Url
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDocUrl() : string
	{
		if($this->DocUrl() === '')
		{
			return 'swagger.json';
		}

		return $this->DocUrl();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Logo Url
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineLogoUrl() : string
	{
		return $this->LogoUrl();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Favicon Url
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineFaviconUrl() : string
	{
		return $this->FaviconUrl();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Title
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineTitle() : string
	{
		if($this->Title() === '')
		{
			return ucFirst($this->Root());
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
	// Define : ExternalDocDescription
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineExternalDocDescription() : string
	{
		return $this->ExternalDocDescription();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : ExternalDocUrl
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineExternalDocUrl() : string
	{
		return $this->ExternalDocUrl();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Endpoints
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineEndpoints() : array
	{
		return array();
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
	// Define : Resources
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineResources() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : LastErrorCode
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineLastErrorCode() : int
	{
		return 0;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Entities
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineEntities() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Types
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineTypes() : array
	{
		return array();
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

		$api      = $this;
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

			// Generates the json file describing the Api
			$swagger  = $this->MakeSwagger();
			$json     = json_encode($swagger->Doc(), JSON_PRETTY_PRINT);
			$filename = $this->Core()->AbsoluteFilename($this->DocUrl());

			file_put_contents($filename, $json);

			// Prepares Swagger UI
			$path     = $this->Core()->AbsoluteUrl(__DIR__ . '/vendor/swagger-api/swagger-ui/dist/');
			$filename = $this->Core()->AbsoluteUrl($filename);
			$html     = file_get_contents(__DIR__ . '/vendor/swagger-api/swagger-ui/dist/index.html');

			// Applies the Api banner instead of the standard Swagger UI banner
			$html = str_replace('layout: "StandaloneLayout"', 'showCommonExtensions: true', $html);
			$html = str_replace('<body>', '<body>' . $this->PortalBanner(), $html);

			// Applies the Api favicon
			if($this->FaviconUrl() !== '')
			{
				$favicon = $this->Core()->AbsoluteUrl($this->FaviconUrl() . '/favicon');
				$html = str_replace('./favicon', $favicon, $html);
			}

			// Applies the additional styles
			$html = str_replace('</style>', $this->PortalStyles() . '</style>', $html);

			// Applies the Api title
			$html = str_replace('<title>Swagger UI</title>', '<title>' . $this->Title() . '</title>', $html);

			// Makes paths independant from the Swagger UI location on the web server
			$html = str_replace('href="./', 'href="' . $path, $html);
			$html = str_replace('src="./' , 'src="'  . $path, $html);

			// Applies the Api documentation
			$html = str_replace('"https://petstore.swagger.io/v2/swagger.json"', '"' . $filename . '"', $html);

			// Outputs Swagger UI
			echo $html;
			exit();
		};

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Portal additional styles
	//------------------------------------------------------------------------------------------------------------------
	protected function PortalStyles() : string
	{
		// Parameter column must be larger
		$res = '.swagger-ui table tbody tr td:first-of-type { min-width: 10em; }';

		// "In" parameter
		$res.= '.swagger-ui .parameter__in { font-style: normal; color: orange; padding-bottom: 10px; }';

		// "Extension" (minimum, maximum...) parameter
		$res.= '.swagger-ui .parameter__extension { font-style: normal; font-weight: 400; color: violet }';

		// "Enum" parameter
		$res.= '.swagger-ui .parameter__enum { font-size: 0.8em; color: violet }';

		// "Default" parameter
		$res.= '.swagger-ui .parameter__default { font-size: 0.8em; color: violet }';

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Portal banner
	//------------------------------------------------------------------------------------------------------------------
	protected function PortalBanner() : string
	{
		$res = '';

		if($this->LogoUrl() !== '')
		{
			$logoUrl = $this->Core()->AbsoluteUrl($this->LogoUrl());

			$res = '<div id="fewd-logo">';
			$res.= '<img src="' . $logoUrl . '" />';
			$res.= '</div>';
		}

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




	//==================================================================================================================
	//
	// EXTERNAL API CALL
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Gets all records through a given endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function GetAll(
		string $url,
		string $token   = '',
		array  $options = array()) : bool|int|float|string|array|null
	{
		$records = $this->Call($url, 'GET', '', array(), $token, $options);

		if($records === false)
		{
			return null;
		}

		return $records;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a record by its id  through a given endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function Get(
		string $url,
		string $id,
		string $token   = '',
		array  $options = array()) : bool|int|float|string|array|null
	{
		$records = $this->Call($url, 'GET', $id, array(), $token, $options);

		if($records === false)
		{
			return null;
		}

		if(is_array($records))
		{
			foreach($records as $v)
			{
				return $v;
			}
		}

		return $records;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Posts a record through a given endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function Post(
		string $url,
		array  $data,
		string $token   = '',
		array  $options = array()) : bool
	{
		$res = $this->Call($url, 'POST', '', $data, $token, $options);

		return ($res !== false);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Puts a record through a given endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function Put(
		string $url,
		string $id,
		array  $data,
		string $token   = '',
		array  $options = array()) : bool
	{
		$res = $this->Call($url, 'PUT', $id, $data, $token, $options);

		return ($res !== false);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Patches a record through a given endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function Patch(
		string $url,
		string $id,
		array  $data,
		string $token   = '',
		array  $options = array()) : bool
	{
		$res = $this->Call($url, 'PATCH', $id, $data, $token, $options);

		return ($res !== false);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Deletes a record through a given endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function Delete(
		string $url,
		string $id,
		string $token   = '',
		array  $options = array()) : bool
	{
		$res = $this->Call($url, 'DELETE', $id, array(), $token, $options);

		return ($res !== false);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Calls an external Api
	//------------------------------------------------------------------------------------------------------------------
	protected function Call(
		string $url,
		string $method,
		string $id,
		array  $data,
		string $token,
		array  $options = array()) : bool|int|float|string|array
    {
		// Launches the curl session
		$curl = curl_init();

		// Adds id to url
		if($id !== '')
		{
			$url = $this->Core()->Join($url, $id);
		}

		// Inits headers
		$headers = array('Content-type: application/json');

		// If a token was provided :
		// Adds it to headers
		if($token !== '')
		{
			$headers[] = 'Authorization: Bearer ' . $token;

			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}

		// Sets curl options of the request
		curl_setopt($curl, CURLOPT_HTTPHEADER    , $headers);
		curl_setopt($curl, CURLOPT_URL           , $url    );
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true    );

		// Sets curl options depending on the method
		switch ($method)
		{
			case 'POST':
				curl_setopt($curl, CURLOPT_POST      , 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
                break;

			case 'PUT':
				curl_setopt($curl, CURLOPT_PUT       , 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
				break;

			case 'PATCH':
				curl_setopt($curl, CURLOPT_PUT       , 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
				break;

			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
        }

		// Adds options
		foreach($options as $k => $v)
		{
			curl_setopt($curl, $k, $v);
		}

		// Gets the response
		$json = curl_exec($curl);
		$res  = json_decode($json, true);

		// Gets the code of the response
		$this->_LastErrorCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);

		if(($this->LastErrorCode() < 200) || ($this->LastErrorCode() >= 300))
		{
			return false;
		}

		// Result
		return $res;
    }




	//==================================================================================================================
	//
	// INTERNAL API RESPONSE
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given header argument exists
	//------------------------------------------------------------------------------------------------------------------
	public function HasHeaderArgument(string $key) : bool
	{
		if($this->_Headers === null)
		{
			$this->_Headers = getallheaders();
		}

		return isset($this->_Headers[$key]);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a given header argument
	//------------------------------------------------------------------------------------------------------------------
	public function HeaderArgument(string $key) : string
	{
		if($this->_Headers === null)
		{
			$this->_Headers = getallheaders();
		}

		if(isset($this->_Headers[$key]))
		{
			return $this->_Headers[$key];
		}

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// HATEOAS links header
	//------------------------------------------------------------------------------------------------------------------
	protected function LinksHeader(array $links)
	{
		$header = '';
		$sep    = ',';

		foreach($links as $k => $v)
		{
			$header.= $sep . '<' . $v . '>; rel="' . $k . '"';
			$sep    = ',';
		}

		header('Link: ' . $header);
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
	// Creates or gets a type after its name
	//------------------------------------------------------------------------------------------------------------------
	protected function CreateType(string $typeName) : ?AType
	{
		// If type already exists :
		// Returns it
		if($this->HasType($typeName))
		{
			return $this->Type($typeName);
		}

		// If type does not exist,
		// And has a well-known name :
		// Cretes it
		if($typeName === 'bool'      ) { return $this->MakeTypeBoolean(   $typeName); }
		if($typeName === 'boolean'   ) { return $this->MakeTypeBoolean(   $typeName); }
		if($typeName === 'int'       ) { return $this->MakeTypeInteger(   $typeName); }
		if($typeName === 'integer'   ) { return $this->MakeTypeInteger(   $typeName); }
		if($typeName === 'float'     ) { return $this->MakeTypeFloat(     $typeName); }
		if($typeName === 'double'    ) { return $this->MakeTypeFloat(     $typeName); }
		if($typeName === 'string'    ) { return $this->MakeTypeString(    $typeName); }
		if($typeName === 'text'      ) { return $this->MakeTypeString(    $typeName); }
		if($typeName === 'array'     ) { return $this->MakeTypeCollection($typeName); }
		if($typeName === 'collection') { return $this->MakeTypeCollection($typeName); }

		// If type is in the form "xyz[]" (aka an array of "xyz" type) :
		// Creates a collection type
		if(substr($typeName, -2) === '[]')
		{
			$itemsType = $this->CreateType(substr($typeName, 0, -2));

			return $this->MakeTypeCollection($typeName, $itemsType);
		}

		// Otherwise :
		// Returns nothing
		return null;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new endpoint (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareEndpoint(
		string $path,
		string $summary      = '',
		string $description  = '',
		int    $maximumLimit = 0,
		int    $maximumAge   = 0) : ?TEndpoint
	{
		// Endpoints must all be declared on the portal
		// On endpoint calls, just the corresponding endpoint is needed only
		if(!$this->IsPortal() && !$this->IsCurrentPath($path))
		{
			return null;
		}

		// Builds the endpoint
		if($this->HasEndpoint($path))
		{
			return $this->Endpoint($path);
		}

		$res = $this->MakeEndpoint($path, $summary, $description, $maximumLimit, $maximumAge);

		$this->AddEndpoint($path, $res);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new operation on a given endpoint (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareOperation(
		?TEndpoint $endpoint,
		string     $verb,
		callable   $callback,
		string     $summary                = '',
		string     $description            = '',
		string     $code                   = '',
		string     $responseTypeName       = '',
		string     $externalDocDescription = '',
		string     $externalDocUrl         = '',
		bool       $isDeprecated           = false) : ?TOperation
	{
		// If endpoint is not declared :
		// Does nothing
		if($endpoint === null)
		{
			return null;
		}

		// Builds the operation
		$res = $this->MakeOperation(
			$endpoint,
			$verb,
			$callback,
			$summary,
			$description,
			$code,
			$this->CreateType($responseTypeName),
			$externalDocDescription,
			$externalDocUrl,
			$isDeprecated);

		$endpoint->AddOperation($verb, $res);

		// Auto-declare wildcard parameters
		foreach($endpoint->Wildcards() as $k => $v)
		{
			$this->DeclareParameter($res, $k, '', true, null, false);
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new parameter on an operation (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareParameter(
		?TOperation  $operation,
		string       $name,
		string       $typeName     = '',
		bool         $isMandatory  = false,
		mixed        $default      = null,
		string       $summary      = '',
		string       $description  = '',
		mixed        $sample       = '',
		bool         $isDeprecated = false) : ?TParameter
	{
		// If operation is not provided :
		// Does nothing
		if($operation === null)
		{
			return null;
		}

		// If parameter is a endpoint wildcard :
		// It becomes mandatory
		if($operation->Endpoint()->HasWildcard($name))
		{
			$isMandatory = true;
			$default     = null;
		}

		// Gets type
		$type = $this->CreateType($typeName);

		if($type === null)
		{
			return null;
		}

		// Makes parameter
		$res = $this->MakeParameter(
			$name,
			$type,
			$isMandatory,
			$default,
			$summary,
			$description,
			$sample,
			$isDeprecated);

		// Stores it on operation
		$operation->AddParameter($name, $res);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Builds an Url in the Api to a given path
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
			$i = 1;
			foreach($wildcards as $k => $v)
			{
				if(isset($matches[$i]))
				{
					$args[$v] = $matches[$i];
				}

				$i++;
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
		// Gets endpoints from the longest to the shortest one
		// (to proceed with the most detailled paths first, for instance 'items/{id}' is prioritary to 'items'))
		$endpoints = array();

		$i = 0;
		foreach($this->Endpoints() as $v)
		{
			$key = 1000 + strlen($v->Path());
			$endpoints[$key . '-' . $i++] = $v;
		}

		krsort($endpoints);

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
	// Gets internal structure labels
	//------------------------------------------------------------------------------------------------------------------
	public function ErrorCodeLabel()    : string { return '__code';    }
	public function ErrorMessageLabel() : string { return '__message'; }
	public function CountLabel()        : string { return '__count';   }
	public function DataLabel()         : string { return '__data';    }


	//------------------------------------------------------------------------------------------------------------------
	// Generates an error response
	//------------------------------------------------------------------------------------------------------------------
	public function ErrorResponse(int $code, string $message = '') : array
	{
		if($message === '')
		{
			$message = $this->Router()->CodeMessage($code);
		}

		return array(
			$this->ErrorCodeLabel()    => $code,
			$this->ErrorMessageLabel() => $message);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates a counted response
	//------------------------------------------------------------------------------------------------------------------
	public function CountedResponse(array $data, int $count) : array
	{
		return array(
			$this->CountLabel() => $count,
			$this->Datalabel()  => $data);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if the given response is an error response
	//------------------------------------------------------------------------------------------------------------------
	public function IsErrorResponse(mixed $response) : bool
	{
		return (isset($response[$this->ErrorCodeLabel()   ]) &&
		        isset($response[$this->ErrorMessagelabel()]) &&
		        (count($response) === 2));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if the given response is counted (i.e. array('count' => <int>, 'data' => <array>))
	//------------------------------------------------------------------------------------------------------------------
	protected function IsCountedResponse(mixed $response) : bool
	{
		return (isset(   $response[$this->CountLabel()]) &&
		        isset(   $response[$this->DataLabel() ]) &&
				(count($response) === 2));
 	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if the given response is a collection (i.e. a set of entities)
	//------------------------------------------------------------------------------------------------------------------
	public function IsCollectionResponse(mixed $response) : bool
	{
		if(is_array($response))
		{
			foreach($response as $k => $v)
			{
				if(is_int($k))
				{
					return true;
				}

				break;
			}
		}

		return false;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if the given response is an entity (i.e. a set of attributes / values)
	//------------------------------------------------------------------------------------------------------------------
	public function IsEntityResponse(mixed $response) : bool
	{
		return (is_array($response) &&
		        !$this->IsCollectionResponse($response) &&
		        !$this->IsErrorResponse(     $response) &&
		        !$this->IsCountedResponse(   $response));
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
	protected function Exit200(mixed $response, TEndpoint $endpoint)
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
	protected function Exit201(mixed $response, string $path, TEndpoint $endpoint)
	{
		// Prepares the location to the newly created element
		// (Id is, by convention, the first element provided in response data)
		$id       = '';
		$location = '';

		if(is_array($response))
		{
			foreach($response as $v)
			{
				$id       = $v;
				$location = $this->BuildUrl($path . '/' . $id);
				break;
			}
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
	protected function Exit206(mixed $response, int $offset, int $limit, int $count, TEndpoint $endpoint)
	{
		// Prepares result into Json format
		$json = json_encode($response, JSON_PRETTY_PRINT);

		// Outputs result
		$this->Header($endpoint);
		header('Content-Range: ' . $offset . '-' . ($offset + $limit - 1) . '/' . ($count < 0 ? '*' : $count));
		header('Accept-Ranges: bytes');
		http_response_code(206);

		// If global count is determined :
		if($count >= 0)
		{
			// Prepares current url
			$url = $this->Core()->CurrentAbsoluteUrl();
			$url = $this->Core()->RemoveArgumentFromUrl($url, $this->OffsetArg());
			$url = $this->Core()->RemoveArgumentFromUrl($url, $this->LimitArg() );

			if(strpos($url, '?') === false)
			{
				$url.= '?';
			}
			else
			{
				$url.= '&';
			}

			// Link to first page
			$links = '<' . $url . $this->OffsetArg() . '=0';
			$links.= '&' . $this->LimitArg() . '=' . $limit . '>; rel="first",';

			// Link to previous page
			$previous = max($offset - $limit, 0);

			$links.= '<' . $url . $this->OffsetArg() . '=' . $previous;
			$links.= '&' . $this->LimitArg() . '=' . $limit . '>; rel="prev",';

			// Link to next page
			$next = min($offset + $limit, $count);

			$links.= '<' . $url . $this->OffsetArg() . '=' . $next;
			$links.= '&' . $this->LimitArg() . '=' . $limit . '>; rel="next",';

			// Link to last page
			$last = $limit * floor(max(0, ($count - 1)) / $limit);

			$links.= '<' . $url . $this->OffsetArg() . '=' . $last;
			$links.= '&' . $this->LimitArg() . '=' . $limit . '>; rel="last"';

			// Adds to header
			header('Link: ' . $links);
		}

		// Outputs data
		echo $json;
		exit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exists on an error response
	//------------------------------------------------------------------------------------------------------------------
	protected function ExitError(array $errorResponse, TEndpoint $endpoint)
	{
		$errorCode    = $errorResponse[$this->ErrorCodeLabel()   ];
		$errorMessage = $errorResponse[$this->ErrorMessageLabel()];

		$this->Exit($errorCode, $endpoint, $this->Router()->Message($errorCode), $errorMessage);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a GET
	//------------------------------------------------------------------------------------------------------------------
	protected function RunGet(mixed $response, int $offset, int $limit, TEndpoint $endpoint)
	{
		// If no response :
		// This is a "not found" error
		if($response === null)
		{
			$this->Exit(404, $endpoint);
		}

		// If response is counted :
		// Outputs a partial response if it is not complete
		if($this->IsCountedResponse($response))
		{
			$count    = $response[$this->CountLabel()];
			$response = $response[$this->Datalabel() ];

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

		// Otherwise,
		// If response is an error :
		// OUtputs an error
		elseif($this->IsErrorResponse($response))
		{
			$this->ExitError($response, $endpoint);
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
			$this->Exit(409, $endpoint);
		}

		if($this->IsErrorResponse($response))
		{
			$this->ExitError($response, $endpoint);
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
			$this->Exit(409, $endpoint);
		}

		 if($this->IsErrorResponse($response))
		{
			$this->ExitError($response, $endpoint);
		}

		$this->Exit200($response, $endpoint);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs the Api
	//------------------------------------------------------------------------------------------------------------------
	protected function Run(array $args, string $verb)
	{
		// Gets path that was derived by the router
		if(!isset($args[$this->PathArg()]))
		{
			$this->Exit(404);
		}

		$path = $this->StringArgValue($this->PathArg(), $args);

		// Gets the endpoint corresponding to path
		$endpoint = $this->UrlToEndpoint($path, $args);

		if($endpoint === null)
		{
			$this->Exit(404);
		}

		// If verb is OPTIONS :
		// Returns the options available for the current endpoint
		if($verb === 'OPTIONS')
		{
			$this->Exit200(array(), $endpoint);
		}

		// If verb is not allowed for the current endpoint :
		// Nothing to run
		if(!$endpoint->HasOperation($verb))
		{
			$this->Exit(405, $endpoint);
		}

		$operation = $endpoint->Operation($verb);

		// Gets special arguments
		$subset =     $this->StringArgValue($this->SubsetArg(), $args);
		$fields =     $this->StringArgValue($this->FieldsArg(), $args);
		$sort   =     $this->StringArgValue($this->SortArg()  , $args);
		$offset = max($this->IntArgValue(   $this->OffsetArg(), $args), 0);
		$limit  = min($this->IntArgValue(   $this->LimitArg() , $args), $endpoint->MaximumLimit());

		if($limit <= 0)
		{
			$limit = 1;
		}

		// If a subset was provided for a verb other than GET :
		// This is an error (the endpoint must be explicitely declared)
		if(($verb !== 'GET') && ($subset !== ''))
		{
			$this->Exit(404, $endpoint, self::ERROR_SUBSET);
		}

		// Gets the body from the Api call (except from GET and DELETE : no body)
		$body = null;

		if(($verb !== 'GET') && ($verb !== 'DELETE'))
		{
			$body = json_decode(file_get_contents("php://input"));
		}

		if($body === null)
		{
			$body = array();
		}

		// Checks parameters
		$message = $operation->CheckParameters($args);
		if($message !== '')
		{
			$this->Exit(400, $endpoint, self::ERROR_PARAMETERS, $message);
		}

		// Gets response data
		$response = $operation->Response($args, $body, $subset, $fields, $sort, $offset, $limit);

		// Checks response
		$message = $operation->CheckResponse($response);

		if($message !== '')
		{
			$error = str_replace('{{TYPE}}', $operation->ResponseType()->Name(), self::ERROR_RESPONSE);

			$this->Exit(400, $endpoint, $error, $message);
		}

		// Outputs the response, depending on the verb
		if($verb === 'GET')
		{
			$this->RunGet($response, $offset, $limit, $endpoint);
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
	protected function MakeEndpoint(
		string $path,
		string $summary      = '',
		string $description  = '',
		int    $maximumLimit = 0,
		int    $maximumAge   = 0) : TEndpoint
	{
		$res = new TEndpoint($this->Core(), $this, $path, $summary, $description, $maximumLimit, $maximumAge);
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Operation
	//------------------------------------------------------------------------------------------------------------------
	protected function MakeOperation(
		TEndpoint $endpoint,
		string    $verb,
		callable  $callback,
		string    $summary                = '',
		string    $description            = '',
		string    $code                   = '',
		AType     $responseType           = null,
		string    $externalDocDescription = '',
		string    $externalDocUrl         = '',
		bool      $isDeprecated           = false) : TOperation
	{
		$res = new TOperation(
			$this->Core(),
			$this,
			$endpoint,
			$verb,
			$callback,
			$summary,
			$description,
			$code,
			$responseType,
			$externalDocDescription,
			$externalDocUrl,
			$isDeprecated);

		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Parameter
	//------------------------------------------------------------------------------------------------------------------
	public function MakeParameter(
		string $name,
		AType  $type,
		bool   $isMandatory  = false,
		mixed  $default      = null,
		string $summary      = '',
		string $description  = '',
		mixed  $sample       = '',
		bool   $isDeprecated = false) : TParameter
	{
		$res = new TParameter(
			$this->Core(),
			$this,
			$name,
			$type,
			$isMandatory,
			$default,
			$summary,
			$description,
			$sample,
			$isDeprecated);

		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Boolean type
	//------------------------------------------------------------------------------------------------------------------
	public function MakeTypeBoolean(string $name) : TTypeBoolean
	{
		$res = new TTypeBoolean($this->Core(), $this, $name);
		$res->Init();

		$this->AddType($res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Integer type
	//------------------------------------------------------------------------------------------------------------------
	public function MakeTypeInteger(
		string $name,
		int    $minimum = null,
		int    $maximum = null,
		array  $enums   = array()) : TTypeInteger
	{
		$res = new TTypeInteger($this->Core(), $this, $name, $minimum, $maximum, $enums);
		$res->Init();

		$this->AddType($res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Float type
	//------------------------------------------------------------------------------------------------------------------
	public function MakeTypeFloat(
		string $name,
		float  $minimum = null,
		float  $maximum = null) : TTypeFloat
	{
		$res = new TTypeFloat($this->Core(), $this, $name, $minimum, $maximum);
		$res->Init();

		$this->AddType($res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : String type
	//------------------------------------------------------------------------------------------------------------------
	public function MakeTypeString(
		string $name,
		int    $minimum = null,
		int    $maximum = null,
		string $pattern = '',
		array  $enums   = array()) : TTypeString
	{
		$res = new TTypeString($this->Core(), $this, $name, $minimum, $maximum, $pattern, $enums);
		$res->Init();

		$this->AddType($res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Collection type
	//------------------------------------------------------------------------------------------------------------------
	public function MakeTypeCollection(
		string $name,
		AType  $itemsType = null) : TTypeCollection
	{
		$res = new TTypeCollection($this->Core(), $this, $name, $itemsType);
		$res->Init();

		$this->AddType($res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Entity type
	//------------------------------------------------------------------------------------------------------------------
	public function MakeTypeEntity(
		string $name,
		string $summary     = '',
		string $description = '') : TTypeEntity
	{
		$res = new TTypeEntity($this->Core(), $this, $name, $summary, $description);
		$res->Init();

		$this->AddType(  $res);
		$this->AddEntity($res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Swagger
	//------------------------------------------------------------------------------------------------------------------
	public function MakeSwagger() : TSwagger
	{
		$res = new TSwagger($this->Core(), $this);
		$res->Init();

		return $res;
	}
}