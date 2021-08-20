<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Wordpress;


use Fewd\Core\TCore;
use Fewd\Core\AModule;


class TWordpress extends AModule
{
	// Domain
	private $_Domain;
	public final function Domain() : string { return $this->_Domain; }

	// Wordpress global Api route
	private $_ApiRoute;
	public final function ApiRoute() : string { return $this->_ApiRoute; }

	// ToolRoutes
	private $_ToolRoutes;
	public final function ToolRoutes()                : array        { return $this->_ToolRoutes;              }
	public final function ToolRoute(      string $id) : string       { return $this->_ToolRoutes[$id] ?? null; }
	public final function HasToolRoute(   string $id) : bool         { return isset($this->_ToolRoutes[$id]);  }
	public       function AddToolRoute(   string $id, string $value) { $this->_ToolRoutes[$id] = $value;       }
	public       function RemoveToolRoute(string $id)                { unset($this->_ToolRoutes[$id]);         }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(TCore $core, string $domain, string $apiRoute)
	{
		parent::__construct($core);

		$this->_Domain   = $domain;
		$this->_ApiRoute = $apiRoute;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Domain     = $this->DefineDomain();
		$this->_ApiRoute   = $this->DefineApiRoute();
		$this->_ToolRoutes = $this->DefineToolRoutes();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Domain
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDomain() : string
	{
		return $this->Domain();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : ApiRoute
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineApiRoute() : string
	{
		if($this->ApiRoute() === '')
		{
			return 'wp-json';
		}

		return $this->ApiRoute();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : ToolRoutes
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineToolRoutes() : array
	{
		$res = array();

		$this->AddToolRoute('wordpress'  , 'wp/v2');
		$this->AddToolRoute('woocommerce', 'wc/v3');

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates the complete Url to Wordpress Api
	//------------------------------------------------------------------------------------------------------------------
	public function Url(string $tool, string $endpoint, string $id = '') : string
    {
		// Gets tool route
		$toolRoute = $this->ToolRoute($tool);

		if($toolRoute === '')
		{
			$toolRoute = 'wp/v2';
		}

		// Builds the complete Url
		$res = $this->Core()->Join($this->Domain(), $this->ApiRoute(), $toolRoute, $endpoint, $id);

		// Result
		return $res;
    }


	//------------------------------------------------------------------------------------------------------------------
	// Gets a JWT token
	//------------------------------------------------------------------------------------------------------------------
	public function GetToken(string $url, string $login, string $password) : string|null
	{
		// Inits the get token call
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $this->Core()->Join($url, 'jwt-auth/v1/token'));
		curl_setopt($curl, CURLOPT_POST, 1);

		curl_setopt($curl, CURLOPT_POSTFIELDS, 'username=' . $login . '&password=' . $password);

		// Gets server response
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$output = curl_exec($curl);

		if($output === false)
		{
			return null;
		}

		$output = json_decode($output, true);

		if(($output === null) && (json_last_error() !== JSON_ERROR_NONE))
		{
			return null;
		}

		// Gets token
		$res = $output->token;

		if(empty($res))
		{
			return null;
		}

		curl_close($curl);

		return $res;
	}
}
