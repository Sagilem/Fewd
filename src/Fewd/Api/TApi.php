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
	public const ERROR_MANDATORY_PARAMETER = 'Mandatory parameter \'{{PARAMETER}}\' is missing';
	public const ERROR_MANDATORY_PROPERTY  = 'Mandatory property \'{{PROPERTY}}\' is missing';
	public const ERROR_PROPERTY            = 'Property \'{{PROPERTY}}\' is missing';
	public const ERROR_INCORRECT           = 'Incorrect value for parameter \'{{PARAMETER}}\' : {{MESSAGE}}';
	public const ERROR_PARAMETERS          = 'Wrong parameters';
	public const ERROR_BODY                = 'Wrong body';
	public const ERROR_COLLECTION_RESPONSE = 'Wrong response (a collection was expected)';
	public const ERROR_NUMERIC             = 'value must be numeric';
	public const ERROR_MINIMUM             = 'value {{VALUE}} is out of range (lesser than {{MINIMUM}})';
	public const ERROR_MAXIMUM             = 'value {{VALUE}} is out of range (greater than {{MAXIMUM}})';
	public const ERROR_ENUMS               = 'value {{VALUE}} does not belong to enumerated values ({{ENUMS}})';
	public const ERROR_STRING              = 'value must be a string (or a numeric value convertible to string)';
	public const ERROR_MINIMUM_LENGTH      = 'value ({{VALUE}}) is shorter than {{MINIMUM}} characters';
	public const ERROR_MAXIMUM_LENGTH      = 'value ({{VALUE}}) is longer than {{MAXIMUM}} characters';
	public const ERROR_PATTERN             = 'value does not match with the expected pattern ({{PATTERN}})';
	public const ERROR_COLLECTION          = 'value must be a collection';
	public const ERROR_COLLECTION_TYPE     = 'value must be a collection of \'{{TYPE}}\' items';
	public const ERROR_RECORD              = 'value is not a \'{{TYPE}}\' record';

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

	// Chapters
	private $_Chapters;
	public final function Chapters()             : array          { return $this->_Chapters;              }
	public final function Chapter(   string $id) : TChapter       { return $this->_Chapters[$id] ?? null; }
	public final function HasChapter(string $id) : bool           { return isset($this->_Chapters[$id]);  }
	public       function AddChapter(string $id, TChapter $value) { $this->_Chapters[$id] = $value;       }

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
	public final function Resources()             : array       { return $this->_Resources;              }
	public final function Resource(   string $id) : mixed       { return $this->_Resources[$id] ?? null; }
	public final function HasResource(string $id) : bool        { return isset($this->_Resources[$id]);  }
	public       function AddResource(string $id, mixed $value) { $this->_Resources[$id] = $value;       }

	// Last error code encountered in an Api call
	protected $_LastErrorCode;
	public final function LastErrorCode() : int { return $this->_LastErrorCode; }

	// Types
	private $_Types;
	public final function Types() : array               { return $this->_Types;                                     }
	public final function Type(   string $name) : AType { return $this->_Types[$name] ?? $this->CreateType($name);  }
	public final function HasType(string $name) : bool  { return isset($this->_Types[$name]);                       }
	protected    function AddType(AType $type)          { $this->_Types[$type->Name()] = $type;                     }

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
		}
		;

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

		// Hides select inputs, when not in tryout
		$res.= '.parameters-col_description select[disabled] { display: none }';

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




	//==================================================================================================================
	//
	// EXTERNAL API CALL
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Gets all data through a given endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function Getall(
		string $url,
		string $token   = '',
		array  $options = array()) : bool|int|float|string|array|null
	{
		return $this->Call($url, 'GET', '', array(), $token, $options);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets data through a given endpoint
	//------------------------------------------------------------------------------------------------------------------
	public function Get(
		string $url,
		string $id,
		string $token   = '',
		array  $options = array()) : bool|int|float|string|array|null
	{
		return $this->Call($url, 'GET', $id, array(), $token, $options);
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

		return ($res !== null);
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

		return ($res !== null);
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
		array  $options = array()) : bool|int|float|string|array|null
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
			return null;
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
	// Path argument (used to get the endpoint path from the complete path, via the router)
	//------------------------------------------------------------------------------------------------------------------
	protected function PathArg() : string
	{
		return '__path';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Sorts argument
	//------------------------------------------------------------------------------------------------------------------
	public function SortsArg() : string
	{
		return '__sorts';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Offset argument
	//------------------------------------------------------------------------------------------------------------------
	public function OffsetArg() : string
	{
		return '__offset';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Limit argument
	//------------------------------------------------------------------------------------------------------------------
	public function LimitArg() : string
	{
		return '__limit';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Subsets argument
	//------------------------------------------------------------------------------------------------------------------
	public function SubsetsArg() : string
	{
		return '__subsets';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fields argument
	//------------------------------------------------------------------------------------------------------------------
	public function FieldsArg() : string
	{
		return '__fields';
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
	// Creates a type after its name
	//------------------------------------------------------------------------------------------------------------------
	protected function CreateType(string $typeName) : AType
	{
		// If type does not exist,
		// And has a well-known name :
		// Cretes it
		if($typeName === 'bool'      ) { return $this->MakeTypeBoolean(   $typeName   ); }
		if($typeName === 'boolean'   ) { return $this->MakeTypeBoolean(   $typeName   ); }
		if($typeName === 'int'       ) { return $this->MakeTypeInteger(   $typeName   ); }
		if($typeName === 'integer'   ) { return $this->MakeTypeInteger(   $typeName   ); }
		if($typeName === 'uint'      ) { return $this->MakeTypeInteger(   $typeName, 0); }
		if($typeName === 'uinteger'  ) { return $this->MakeTypeInteger(   $typeName, 0); }
		if($typeName === 'zint'      ) { return $this->MakeTypeInteger(   $typeName, 1); }
		if($typeName === 'zinteger'  ) { return $this->MakeTypeInteger(   $typeName, 1); }
		if($typeName === 'float'     ) { return $this->MakeTypeFloat(     $typeName   ); }
		if($typeName === 'double'    ) { return $this->MakeTypeFloat(     $typeName   ); }
		if($typeName === 'string'    ) { return $this->MakeTypeString(    $typeName   ); }
		if($typeName === 'text'      ) { return $this->MakeTypeString(    $typeName   ); }
		if($typeName === 'zstring'   ) { return $this->MakeTypeString(    $typeName, 1); }
		if($typeName === 'ztext'     ) { return $this->MakeTypeString(    $typeName, 1); }
		if($typeName === 'array'     ) { return $this->MakeTypeCollection($typeName   ); }
		if($typeName === 'collection') { return $this->MakeTypeCollection($typeName   ); }

		// If type is in the form "xyz[]" (aka an array of "xyz" type) :
		// Creates a collection type
		if(substr($typeName, -2) === '[]')
		{
			$itemsType = $this->CreateType(substr($typeName, 0, -2));

			return $this->MakeTypeCollection($typeName, $itemsType);
		}

		// Otherwise :
		// Creates as a string type
		return $this->MakeTypeString($typeName);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new chapter
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareChapter(
		string $name,
		string $description            = '',
		string $externalDocDescription = '',
		string $externalDocUrl         = '') : TChapter
	{
		$res = $this->MakeChapter($name, $description, $externalDocDescription, $externalDocUrl);

		$this->AddChapter($name, $res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new endpoint (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareEndpoint(
		string   $path,
		string   $summary      = '',
		string   $description  = '',
		TChapter $chapter      = null,
		int      $maximumLimit = 0,
		int      $maximumAge   = 0) : ?TEndpoint
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

		$res = $this->MakeEndpoint($path, $summary, $description, $chapter, $maximumLimit, $maximumAge);

		$this->AddEndpoint($path, $res);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new GETALL operation on a given endpoint (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareGetall(
		?TEndpoint   $endpoint,
		callable     $callback,
		AType|string $responseItemsType      = null,
		string       $summary                = '',
		string       $description            = '',
		string       $code                   = '',
		string       $externalDocDescription = '',
		string       $externalDocUrl         = '',
		bool         $isDeprecated           = false) : ?TOperation
	{
		// If endpoint is not declared,
		// Or if current HTTP verb is not the one of current endpoint (except on Api portal) :
		// Does nothing
		if(($endpoint === null) || (!$this->IsPortal() && ($this->Core()->CurrentVerb() !== 'GET')))
		{
			return null;
		}

		// Defines response type as a collection of items type
		if(is_string($responseItemsType))
		{
			$responseItemsType = $this->Type($responseItemsType);
		}

		$name = 'Collection';
		if($responseItemsType !== null)
		{
			$name.= ucfirst($responseItemsType->Name());
		}

		$responseType = $this->MakeTypeCollection($name, $responseItemsType);

		// Builds the operation
		$res = $this->MakeOperation(
			$endpoint,
			'GETALL',
			$callback,
			null,
			$responseType,
			$summary,
			$description,
			$code,
			$externalDocDescription,
			$externalDocUrl,
			$isDeprecated);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new GET operation on a given endpoint (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareGet(
		?TEndpoint   $endpoint,
		callable     $callback,
		AType|string $responseType           = null,
		string       $summary                = '',
		string       $description            = '',
		string       $code                   = '',
		string       $externalDocDescription = '',
		string       $externalDocUrl         = '',
		bool         $isDeprecated           = false) : ?TOperation
	{
		// If endpoint is not declared,
		// Or if current HTTP verb is not the one of current endpoint (except on Api portal) :
		// Does nothing
		if(($endpoint === null) || (!$this->IsPortal() && ($this->Core()->CurrentVerb() !== 'GET')))
		{
			return null;
		}

		// Defines response type
		if(is_string($responseType))
		{
			$responseType = $this->Type($responseType);
		}

		// Builds the operation
		$res = $this->MakeOperation(
			$endpoint,
			'GET',
			$callback,
			null,
			$responseType,
			$summary,
			$description,
			$code,
			$externalDocDescription,
			$externalDocUrl,
			$isDeprecated);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new POST operation on a given endpoint (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclarePost(
		?TEndpoint   $endpoint,
		callable     $callback,
		AType|string $bodyType               = null,
		string       $summary                = '',
		string       $description            = '',
		string       $code                   = '',
		string       $externalDocDescription = '',
		string       $externalDocUrl         = '',
		bool         $isDeprecated           = false) : ?TOperation
	{
		// If endpoint is not declared,
		// Or if current HTTP verb is not the one of current endpoint (except on Api portal) :
		// Does nothing
		if(($endpoint === null) || (!$this->IsPortal() && ($this->Core()->CurrentVerb() !== 'POST')))
		{
			return null;
		}

		// Defines body type
		if(is_string($bodyType))
		{
			$bodyType = $this->Type($bodyType);
		}

		// Builds the operation
		$res = $this->MakeOperation(
			$endpoint,
			'POST',
			$callback,
			$bodyType,
			null,
			$summary,
			$description,
			$code,
			$externalDocDescription,
			$externalDocUrl,
			$isDeprecated);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new PUT operation on a given endpoint (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclarePut(
		?TEndpoint   $endpoint,
		callable     $callback,
		AType|string $bodyType               = null,
		string       $summary                = '',
		string       $description            = '',
		string       $code                   = '',
		string       $externalDocDescription = '',
		string       $externalDocUrl         = '',
		bool         $isDeprecated           = false) : ?TOperation
	{
		// If endpoint is not declared,
		// Or if current HTTP verb is not the one of current endpoint (except on Api portal) :
		// Does nothing
		if(($endpoint === null) || (!$this->IsPortal() && ($this->Core()->CurrentVerb() !== 'PUT')))
		{
			return null;
		}

		// Defines body type
		if(is_string($bodyType))
		{
			$bodyType = $this->Type($bodyType);
		}

		// Builds the operation
		$res = $this->MakeOperation(
			$endpoint,
			'PUT',
			$callback,
			$bodyType,
			null,
			$summary,
			$description,
			$code,
			$externalDocDescription,
			$externalDocUrl,
			$isDeprecated);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new PATCH operation on a given endpoint (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclarePatch(
		?TEndpoint   $endpoint,
		callable     $callback,
		AType|string $bodyType               = null,
		string       $summary                = '',
		string       $description            = '',
		string       $code                   = '',
		string       $externalDocDescription = '',
		string       $externalDocUrl         = '',
		bool         $isDeprecated           = false) : ?TOperation
	{
		// If endpoint is not declared,
		// Or if current HTTP verb is not the one of current endpoint (except on Api portal) :
		// Does nothing
		if(($endpoint === null) || (!$this->IsPortal() && ($this->Core()->CurrentVerb() !== 'PATCH')))
		{
			return null;
		}

		// Defines body type
		if(is_string($bodyType))
		{
			$bodyType = $this->Type($bodyType);
		}

		// Builds the operation
		$res = $this->MakeOperation(
			$endpoint,
			'PATCH',
			$callback,
			$bodyType,
			null,
			$summary,
			$description,
			$code,
			$externalDocDescription,
			$externalDocUrl,
			$isDeprecated);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new DELETE operation on a given endpoint (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareDelete(
		?TEndpoint   $endpoint,
		callable     $callback,
		string       $summary                = '',
		string       $description            = '',
		string       $code                   = '',
		string       $externalDocDescription = '',
		string       $externalDocUrl         = '',
		bool         $isDeprecated           = false) : ?TOperation
	{
		// If endpoint is not declared,
		// Or if current HTTP verb is not the one of current endpoint (except on Api portal) :
		// Does nothing
		if(($endpoint === null) || (!$this->IsPortal() && ($this->Core()->CurrentVerb() !== 'DELETE')))
		{
			return null;
		}

		// Builds the operation
		$res = $this->MakeOperation(
			$endpoint,
			'DELETE',
			$callback,
			null,
			null,
			$summary,
			$description,
			$code,
			$externalDocDescription,
			$externalDocUrl,
			$isDeprecated);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new parameter on an operation (only when needed, to gain some performance)
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareParameter(
		?TOperation  $operation,
		string       $name,
		AType|string $type         = '',
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
		if(is_string($type))
		{
			$type = $this->CreateType($type);
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
		return $this->Core()->Protocol() . $this->Core()->Domain() . '/' . $this->Root() . '/' . $path;
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
		// Gets the subsets and indicates that the endpoint matches
		$args[$this->SubsetsArg()] = substr($path, strlen($endpoint->Path()) + 1);

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
			$regexp = str_replace('{' . $k . '}', '([^/]+)', $regexp);
		}

		// Adds subsets management
		$wildcards[$this->SubsetsArg()] = null;
		$regexp.= '(/.*){0,1}';

		// If path matches :
		if(preg_match('#' . $regexp . '#', $path, $matches) === 1)
		{
			// Stores wildcard values as new args (only if wildcard type is respected)
			$i = 1;
			foreach($wildcards as $k => $v)
			{
				if(isset($matches[$i]))
				{
					$args[$k] = $matches[$i];
				}

				$i++;
			}

			// if a subsets arg exists :
			// Removes the leading "/"
			if(isset($args[$this->SubsetsArg()]) && (substr($args[$this->SubsetsArg()], 0, 1) === '/'))
			{
				$args[$this->SubsetsArg()] = substr($args[$this->SubsetsArg()], 1);
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
	protected function UrlToEndpoint(string $path, array &$args) : ?TEndpoint
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
	// Builds a partial response
	//------------------------------------------------------------------------------------------------------------------
	public function PartialResponse(mixed $data, int $totalCount) : mixed
	{
		if(!is_array($data))
		{
			return $data;
		}

		return array(
			'__data'       => $data,
			'__totalCount' => $totalCount);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a response is a partial response
	//------------------------------------------------------------------------------------------------------------------
	public function IsPartialResponse(mixed $response) : bool
	{
		return (is_array($response             ) &&
		        (count($response) === 2        ) &&
				isset($response['__data'      ]) &&
				isset($response['__totalCount']));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets data from partial response
	//------------------------------------------------------------------------------------------------------------------
	public function PartialData(mixed $response) : array
	{
		if(isset($response['__data']))
		{
			return $response['__data'];
		}

		return $response;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets total count from partial response
	//------------------------------------------------------------------------------------------------------------------
	public function PartialTotalCount(mixed $response) : int
	{
		if(isset($response['__totalCount']))
		{
			return $response['__totalCount'];
		}

		return 0;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Outputs a response header
	//------------------------------------------------------------------------------------------------------------------
	public function Header(TEndpoint $endpoint = null)
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

		// Maximum age
		if($endpoint === null)
		{
			$maximumAge = 3600;
		}
		else
		{
			$maximumAge = $endpoint->MaximumAge();
		}

		if($maximumAge > 0)
		{
			header('Access-Control-Max-Age: ' . $maximumAge);
		}

		// Outputs headers
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: ' . $allowedVerbs);
		header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');

		header('Content-Type: application/json; charset=UTF-8');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exists with a given Http code
	//------------------------------------------------------------------------------------------------------------------
	protected function Exit(
		TEndpoint $endpoint = null,
		int       $code     = 400,
		mixed     $data     = array())
	{
		// Formats into Json format
		$json = json_encode($data, JSON_PRETTY_PRINT);

		// Outputs result
		$this->Header($endpoint);
		http_response_code($code);
		echo $json;
		exit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a given Http code
	//------------------------------------------------------------------------------------------------------------------
	protected function ExitError(
		TEndpoint $endpoint    = null,
		int       $code        = 400,
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

		// Exit
		$this->Exit($endpoint, $code, $data);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits when the endpoint is undefined
	//------------------------------------------------------------------------------------------------------------------
	protected function ExitNoEndpoint()
	{
		$this->ExitError(null, 404);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 200 code
	//------------------------------------------------------------------------------------------------------------------
	// 200 corresponds to a standard success
	//------------------------------------------------------------------------------------------------------------------
	public function Exit200(TEndpoint $endpoint, mixed $data)
	{
		$this->Exit($endpoint, 200, $data);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 201 code
	//------------------------------------------------------------------------------------------------------------------
	// 201 corresponds to a successful POST
	//------------------------------------------------------------------------------------------------------------------
	public function Exit201(TEndpoint $endpoint, string|int $id)
	{
		// Prepares result into Json format
		$data = array('message' => $this->Router()->CodeMessage(201));

		// Adds location to new record
		if($id !== '')
		{
			$location = $this->BuildUrl($endpoint->Path() . '/' . $id);

			$data['id'      ] = $id;
			$data['location'] = $location;

			header('Location: ' . $location);
		}

		// Exit
		$this->Exit($endpoint, 201, $data);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 206 code
	//------------------------------------------------------------------------------------------------------------------
	// 206 corresponds to a partial GETALL
	//------------------------------------------------------------------------------------------------------------------
	public function Exit206(TEndpoint $endpoint, mixed $data, int $offset, int $limit, int $totalCount)
	{
		// Builds links to first / previous / next / last pages
		$rangeOffset = $offset . '-' . ($offset + $limit - 1);
		$rangeLimit  = ($totalCount < 0 ? '*' : $totalCount);

		header('Content-Range: ' . $rangeOffset . '/' . $rangeLimit);
		header('Accept-Ranges: bytes');

		// Prepares current url to be used for first, previous, next and last page links
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
		$previous       = max(0, $offset - $limit);
		$previousLength = min($offset, $limit);

		if($offset > 0)
		{
			$links.= '<' . $url . $this->OffsetArg() . '=' . $previous;
			$links.= '&' . $this->LimitArg() . '=' . $previousLength . '>; rel="prev",';
		}

		// Link to next page
		if((($totalCount === 0) && !empty($data)) || ($offset + $limit < $totalCount))
		{
			$links.= '<' . $url . $this->OffsetArg() . '=' . ($offset + $limit);
			$links.= '&' . $this->LimitArg() . '=' . $limit . '>; rel="next",';
		}

		// Link to last page
		if($totalCount > 0)
		{
			$last = $limit * floor(max(0, ($totalCount - 1)) / $limit);

			$links.= '<' . $url . $this->OffsetArg() . '=' . $last;
			$links.= '&' . $this->LimitArg() . '=' . $limit . '>; rel="last"';
		}

		// Adds to header
		header('Link: ' . $links);

		// Exit
		$this->Exit($endpoint, 206, $data);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 400 code
	//------------------------------------------------------------------------------------------------------------------
	// 400 corresponds to a generic error
	//------------------------------------------------------------------------------------------------------------------
	public function Exit400(
		TEndpoint $endpoint,
		string    $message,
		string    $description = '',
		string    $url         = '')
	{
		$this->ExitError($endpoint, 400, $message, $description, $url);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 404 error
	//------------------------------------------------------------------------------------------------------------------
	// 404 corresponds to an "unfound resource" error
	//------------------------------------------------------------------------------------------------------------------
	public function Exit404(TEndpoint $endpoint = null)
	{
		$this->ExitError($endpoint, 404);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 405 code
	//------------------------------------------------------------------------------------------------------------------
	public function Exit405(TEndpoint $endpoint)
	{
		$this->ExitError($endpoint, 405);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Exits with a 409 code
	//------------------------------------------------------------------------------------------------------------------
	public function Exit409(TEndpoint $endpoint)
	{
		$this->ExitError($endpoint, 409);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs the Api
	//------------------------------------------------------------------------------------------------------------------
	protected function Run(array $args, string $verb)
	{
		// Gets path that was derived by the router
		if(!isset($args[$this->PathArg()]))
		{
			$this->ExitNoEndpoint();
		}

		$path = $args[$this->PathArg()];
		unset($args[$this->PathArg()]);

		// Gets the endpoint corresponding to path
		$endpoint = $this->UrlToEndpoint($path, $args);

		if($endpoint === null)
		{
			$this->ExitNoEndpoint();
		}

		// If verb is OPTIONS :
		// Returns the options available for the current endpoint
		if($verb === 'OPTIONS')
		{
			$this->Exit200($endpoint, array());
		}

		// If verb is GET,
		// And operation has no operation with GET :
		// Tries with GETALL
		if(($verb === 'GET') && !$endpoint->HasOperation($verb))
		{
			$verb = 'GETALL';
		}

		// If verb is not allowed for the current endpoint :
		// Nothing to run
		if(!$endpoint->HasOperation($verb))
		{
			$this->Exit405($endpoint);
		}

		// Gets operation
		$operation = $endpoint->Operation($verb);

		// Gets the body from the Api call
		$body = json_decode(file_get_contents("php://input"), true);

		if($body === null)
		{
			$body = array();
		}


		// Runs the operation
		$operation->Run($args, $body);

		// Operation does the job and stops here
		exit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Chapter
	//------------------------------------------------------------------------------------------------------------------
	protected function MakeChapter(
		string $name,
		string $description            = '',
		string $externalDocDescription = '',
		string $externalDocUrl         = '') : TChapter
	{
		$res = new TChapter($this->Core(), $this, $name, $description, $externalDocDescription, $externalDocUrl);
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Endpoint
	//------------------------------------------------------------------------------------------------------------------
	protected function MakeEndpoint(
		string   $path,
		string   $summary      = '',
		string   $description  = '',
		TChapter $chapter      = null,
		int      $maximumLimit = 0,
		int      $maximumAge   = 0) : TEndpoint
	{
		$res = new TEndpoint(
			$this->Core(),
			$this,
			$path,
			$summary,
			$description,
			$chapter,
			$maximumLimit,
			$maximumAge);

		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Operation
	//------------------------------------------------------------------------------------------------------------------
	protected function MakeOperation(
		TEndpoint       $endpoint,
		string          $verb,
		callable        $callback,
		AType           $bodyType               = null,
		AType           $responseType           = null,
		string          $summary                = '',
		string          $description            = '',
		string          $code                   = '',
		string          $externalDocDescription = '',
		string          $externalDocUrl         = '',
		bool            $isDeprecated           = false) : TOperation
	{
		$res = new TOperation(
			$this->Core(),
			$this,
			$endpoint,
			$verb,
			$callback,
			$bodyType,
			$responseType,
			$summary,
			$description,
			$code,
			$externalDocDescription,
			$externalDocUrl,
			$isDeprecated);

		$res->Init();

		$endpoint->AddOperation($verb, $res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Parameter
	//------------------------------------------------------------------------------------------------------------------
	protected function MakeParameter(
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
	public function MakeTypeBoolean(string $name, bool $sample = false, bool $default = false) : TTypeBoolean
	{
		$res = new TTypeBoolean($this->Core(), $this, $name, $sample, $default);
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
		array  $enums   = array(),
		int    $sample  = 0,
		int    $default = 0) : TTypeInteger
	{
		$res = new TTypeInteger($this->Core(), $this, $name, $minimum, $maximum, $enums, $sample, $default);
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
		float  $maximum = null,
		float  $sample  = 0.0,
		float  $default = 0.0) : TTypeFloat
	{
		$res = new TTypeFloat($this->Core(), $this, $name, $minimum, $maximum, $sample, $default);
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
		array  $enums   = array(),
		string $sample  = '',
		string $default = '') : TTypeString
	{
		$res = new TTypeString($this->Core(), $this, $name, $minimum, $maximum, $pattern, $enums, $sample, $default);
		$res->Init();

		$this->AddType($res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Collection type
	//------------------------------------------------------------------------------------------------------------------
	public function MakeTypeCollection(
		string $name,
		AType  $itemsType = null,
		array  $sample    = array(),
		array  $default   = array()) : TTypeCollection
	{
		$res = new TTypeCollection($this->Core(), $this, $name, $itemsType, $sample, $default);
		$res->Init();

		$this->AddType($res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Record type
	//------------------------------------------------------------------------------------------------------------------
	public function MakeTypeRecord(
		string $name,
		string $summary     = '',
		string $description = '',
		array  $properties  = array(),
		array  $sample      = array(),
		array  $default     = array()) : TTypeRecord
	{
		$res = new TTypeRecord($this->Core(), $this, $name, $summary, $description, $properties, $sample, $default);
		$res->Init();

		$this->AddType($res);

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