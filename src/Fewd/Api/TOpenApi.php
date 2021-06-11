<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\AThing;
use Fewd\Core\TCore;



class TOpenApi extends AThing
{
	// Api
	private $_Api;
	public final function Api() : TApi { return $this->_Api; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(TCore $core, TApi $api)
	{
		parent::__construct($core);

		$this->_Api = $api;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a value to a given array if it is mandatory, or if it is not empty
	//------------------------------------------------------------------------------------------------------------------
	protected function Store(array &$array, string $key, string|array $value, bool $isMandatory = false)
	{
		if($isMandatory || !empty($value))
		{
			$array[$key] = $value;
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc
	//------------------------------------------------------------------------------------------------------------------
	public function Doc() : array
	{
		$res = array();

		$this->Store($res, 'openapi'     , '3.0.3'                 , true);
		$this->Store($res, 'info'        , $this->InfoDoc()        , true);
		$this->Store($res, 'servers'     , $this->ServersDoc()     );
		$this->Store($res, 'paths'       , $this->PathsDoc()       , true);
		$this->Store($res, 'components'  , $this->ComponentsDoc()  );
		$this->Store($res, 'security'    , $this->SecurityDoc()    );
		$this->Store($res, 'tags'        , $this->TagsDoc()        );
		$this->Store($res, 'externalDocs', $this->ExternalDocsDoc());

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Info
	//------------------------------------------------------------------------------------------------------------------
	protected function InfoDoc() : array
	{
		$res = array();

		$this->Store($res, 'title'         , $this->Api()->Title()         , true);
		$this->Store($res, 'description'   , $this->Api()->Description()   );
		$this->Store($res, 'termsOfService', $this->Api()->TermsOfService());
		$this->Store($res, 'contact'       , $this->ContactDoc()           );
		$this->Store($res, 'license'       , $this->LicenseDoc()           , ($this->Api()->LicenseName() !== ''));
		$this->Store($res, 'version'       , $this->Api()->DocVersion()    , true);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Servers
	//------------------------------------------------------------------------------------------------------------------
	protected function ServersDoc() : array
	{
		$res = array();

		$this->Store($res, 'url', $this->Api()->Url(), true);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Paths
	//------------------------------------------------------------------------------------------------------------------
	protected function PathsDoc() : array
	{
		$res = array();

		foreach($this->Api()->Endpoints() as $v)
		{
			$res['/' . $v->Path()] = $this->PathDoc($v);
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Path
	//------------------------------------------------------------------------------------------------------------------
	protected function PathDoc(TEndpoint $endpoint) : array
	{
		$res = array();

		foreach($endpoint->Works() as $k => $v)
		{
			$res[$k] = $this->OperationDoc($endpoint, $k);
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Get Doc : Operation
	//------------------------------------------------------------------------------------------------------------------
	protected function OperationDoc(TEndpoint $endpoint, string $verb) : array
	{
		$res = array();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Components
	//------------------------------------------------------------------------------------------------------------------
	protected function ComponentsDoc() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Security
	//------------------------------------------------------------------------------------------------------------------
	protected function SecurityDoc() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Tags
	//------------------------------------------------------------------------------------------------------------------
	protected function TagsDoc() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : ExternalDocs
	//------------------------------------------------------------------------------------------------------------------
	protected function ExternalDocsDoc() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Contat
	//------------------------------------------------------------------------------------------------------------------
	protected function ContactDoc() : array
	{
		$res = array();

		$this->Store($res, 'name' , $this->Api()->ContactName() );
		$this->Store($res, 'url'  , $this->Api()->ContactUrl()  );
		$this->Store($res, 'email', $this->Api()->ContactEmail());

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : License
	//------------------------------------------------------------------------------------------------------------------
	protected function LicenseDoc() : array
	{
		$res = array();

		$this->Store($res, 'name', $this->Api()->LicenseName(), true);
		$this->Store($res, 'url' , $this->Api()->LicenseUrl() );

		return $res;
	}
}
