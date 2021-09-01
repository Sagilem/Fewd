<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\AThing;
use Fewd\Core\TCore;


class TSwagger extends AThing
{
	// Api
	private $_Api;
	public final function Api() : TApi { return $this->_Api; }

	// Components
	protected $_Components = array();


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
	protected function Store(array &$array, string $key, int|float|bool|string|array $value, bool $isMandatory = false)
	{
		if($isMandatory || !empty($value) || ($value === 0))
		{
			if($key === '')
			{
				$array[] = $value;
			}
			else
			{
				$array[$key] = $value;
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc
	//------------------------------------------------------------------------------------------------------------------
	public function Doc() : array
	{
		$res = array();

		$this->_Components = array();

		$this->Store($res, 'openapi'     , '3.0.0'                    , true);
		$this->Store($res, 'info'        , $this->InfoDoc()           , true);
		$this->Store($res, 'servers'     , $this->ServersDoc()        );
		$this->Store($res, 'paths'       , $this->PathsDoc()          , true);
		$this->Store($res, 'components'  , $this->ComponentsDoc()     );
//		$this->Store($res, 'security'    , $this->SecurityDoc()       );
		$this->Store($res, 'tags'        , $this->TagsDoc()           );

		if($this->Api()->ExternalDocUrl() !== '')
		{
			$this->Store($res, 'externalDocs', $this->ApiExternalDocsDoc());
		}

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

		if($this->Api()->LicenseName() !== '')
		{
			$this->Store($res, 'license', $this->LicenseDoc());
		}

		$this->Store($res, 'version'       , $this->Api()->DocVersion()    , true);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Servers
	//------------------------------------------------------------------------------------------------------------------
	protected function ServersDoc() : array
	{
		$res = array();

		$this->Store($res, '', $this->ServerDoc($this->Api()->Url(), ''), true);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Server
	//------------------------------------------------------------------------------------------------------------------
	protected function ServerDoc(string $url, string $description) : array
	{
		$res = array();

		$this->Store($res, 'url'        , $url        , true);
		$this->Store($res, 'description', $description);

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

		$this->Store($res, 'summary'    , $endpoint->Summary()    );
		$this->Store($res, 'description', $endpoint->Description());

		foreach($endpoint->Operations() as $v)
		{
			$verb = strtolower($v->Verb());

			if($verb === 'getall')
			{
				$verb = 'get';
			}

			$res[$verb] = $this->OperationDoc($v);
		}

//		$this->Store($res, 'servers'...)
//		$this->Store($res, 'parameters'....)

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Get Doc : Operation
	//------------------------------------------------------------------------------------------------------------------
	protected function OperationDoc(TOperation $operation) : array
	{
		$res = array();

		$chapter = $operation->Endpoint()->Chapter();
		if($chapter !== null)
		{
			$this->Store($res, 'tags', array($chapter->Name()));
		}

		$this->Store($res, 'summary'    , $operation->Summary()             );
		$this->Store($res, 'description', $operation->Description()         );

		if($operation->ExternalDocUrl() !== '')
		{
			$this->Store($res, 'externalDocs', $this->OperationExternalDocsDoc($operation));
		}

		$this->Store($res, 'operationId', $operation->Code());

		$this->Store($res, 'parameters' , $this->ParametersDoc(          $operation));
		$this->Store($res, 'requestBody', $this->OperationRequestBodyDoc($operation));
		$this->Store($res, 'responses'  , $this->OperationResponsesDoc(  $operation), true);
		$this->Store($res, 'deprecated' , $operation->IsDeprecated());
//		$this->Store($res, 'security'   , $this->OperationSecurityDoc(   $operation));

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Operation request body
	//------------------------------------------------------------------------------------------------------------------
	protected function OperationRequestBodyDoc(TOperation $operation) : array
	{
		$res = array();

		// No request body for POST, PUT and PATCH
		$verb = $operation->Verb();

		if(($verb !== 'POST') && ($verb !== 'PUT') && ($verb !== 'PATCH'))
		{
			return $res;
		}

		// Body type documentation
		$bodyType = $operation->BodyType();

		if($bodyType !== null)
		{
			// Description
			$this->Store($res, 'description', $bodyType->Name());

			// Content (schema)
			$schema = $this->TypeDoc($bodyType);

			if(empty($schema))
			{
				$this->Store($res, 'content', array());
			}
			else
			{
				$this->Store($res, 'content', array('application/json' => array('schema' => $schema)));
			}

			// Required
			$this->Store($res, 'required', true);
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Operation responses
	//------------------------------------------------------------------------------------------------------------------
	protected function OperationResponsesDoc(TOperation $operation) : array
	{
		$responses = array();
		$router    = $this->Api()->Router();

		// Generates standard responses depending on verb
		$verb = $operation->Verb();

		$responses[] = array(400, TApi::ERROR_PARAMETERS);

		if($verb === 'GETALL')
		{
			$responses[] = array(206, $router->Message(206));
			$responses[] = array(200, $router->Message(200));
		}

		elseif($verb === 'GET')
		{
			$responses[] = array(404, $router->Message(404));
			$responses[] = array(200, $router->Message(200));
		}

		elseif($verb === 'POST')
		{
			$responses[] = array(409, $router->Message(409));
			$responses[] = array(201, $router->Message(201));
		}
		else
		{
			$responses[] = array(200, $router->Message(200));
		}

		ksort($responses);

		// Generates doc
		$res = array();

		foreach($responses as $v)
		{
			$this->Store($res, $v[0], $this->OperationResponseDoc($operation, $v[0], $v[1]));
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : a given operation response
	//------------------------------------------------------------------------------------------------------------------
	protected function OperationResponseDoc(TOperation $operation, int $code, string $message) : array
	{
		$res = array();

		$this->Store($res, 'description', $message);

		if($code >= 400)
		{
			$schema = array('$ref' => '#/components/schemas/Error');
		}
		elseif(($operation->ResponseType() === null) || (($code !== 200) && ($code !== 206)))
		{
			$schema = array();
		}
		else
		{
			$schema = $this->TypeDoc($operation->ResponseType());
		}

		if(empty($schema))
		{
			$this->Store($res, 'content', array());
		}
		else
		{
			$this->Store($res, 'content', array('application/json' => array('schema' => $schema)));
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Parameters
	//------------------------------------------------------------------------------------------------------------------
	protected function ParametersDoc(TOperation $operation) : array
	{
		$res = array();

		foreach($operation->Endpoint()->Wildcards() as $k => $v)
		{
			$this->Store($res, '', $this->WildcardDoc($operation, $k, $v));
		}

		foreach($operation->Parameters() as $v)
		{
			$this->Store($res, '', $this->ParameterDoc($operation, $v));
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Wildcard
	//------------------------------------------------------------------------------------------------------------------
	protected function WildcardDoc(TOperation $operation, string $name, ?AType $type) : array
	{
		$res = array();

		if($operation->HasParameter($name))
		{
			$parameter = $operation->Parameter($name);

			$description = $this->Core()->Concatenate(' : ', $parameter->Summary(), $parameter->Description());
		}
		else
		{
			$description = '';
		}

		$this->Store($res, 'name', $name , true);
		$this->Store($res, 'in'  , 'path', true);

		if($description !== '')
		{
			$this->Store($res, 'description', $description);
		}

		$this->Store($res, 'required', true);

		if($type !== null)
		{
			$this->Store($res, 'schema', $this->TypeDoc($type, null, $type->Default()), true);
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Parameter
	//------------------------------------------------------------------------------------------------------------------
	protected function ParameterDoc(TOperation $operation, TParameter $parameter) : array
	{
		$res = array();

		if(!$operation->Endpoint()->HasWildcard($parameter->Name()))
		{
			return $res;
		}

		$description = $this->Core()->Concatenate(' : ', $parameter->Summary(), $parameter->Description());

		$this->Store($res, 'name'           , $parameter->Name()       , true);
		$this->Store($res, 'in'             , 'query'                  , true);
		$this->Store($res, 'description'    , $description             );
		$this->Store($res, 'required'       , $parameter->IsMandatory());

		if($parameter->IsMandatory())
		{
			$this->Store($res, 'required', true);
		}

		if($parameter->IsDeprecated())
		{
			$this->Store($res, 'deprecated', true);
		}

		$this->Store($res, 'schema', $this->TypeDoc($parameter->Type(), null, $parameter->Default()), true);

		return $res;
	}



	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Type
	//------------------------------------------------------------------------------------------------------------------
	protected function TypeDoc(?AType $type, mixed $sample = null, mixed $default = null) : array
	{
		$res = array();

		// Null case
		if($type === null)
		{
			return $res;
		}

		// Boolean case
		if($type instanceof TTypeBoolean)
		{
			$this->Store($res, 'type', 'boolean', true);
		}

		// Integer case
		elseif($type instanceof TTypeInteger)
		{
			$this->Store($res, 'type'  , 'integer', true);
			$this->Store($res, 'format', 'int64'  );

			if($type->Minimum() !== null)
			{
				$this->Store($res, 'minimum', $type->Minimum());
			}

			if($type->Maximum() !== null)
			{
				$this->Store($res, 'maximum', $type->Maximum());
			}

			if(count($type->Enums()) > 0)
			{
				$this->Store($res, 'enum', array_keys($type->Enums()));
			}
		}

		// Float case
		elseif($type instanceof TTypeFloat)
		{
			$this->Store($res, 'type'  , 'number', true);
			$this->Store($res, 'format', 'double');

			if($type->Minimum() !== null)
			{
				$this->Store($res, 'minimum', $type->Minimum());
			}

			if($type->Maximum() !== null)
			{
				$this->Store($res, 'maximum', $type->Maximum());
			}
		}

		// String case
		elseif($type instanceof TTypeString)
		{
			$this->Store($res, 'type', 'string', true);

			switch($type->Name())
			{
				case 'email'    : $this->Store($res, 'format', 'email'    ); break;
				case 'uuid'     : $this->Store($res, 'format', 'uuid'     ); break;
				case 'binary'   : $this->Store($res, 'format', 'binary'   ); break;
				case 'byte'     : $this->Store($res, 'format', 'byte'     ); break;
				case 'date'     : $this->Store($res, 'format', 'date'     ); break;
				case 'datetime' : $this->Store($res, 'format', 'date-time'); break;
				case 'password' : $this->Store($res, 'format', 'password' ); break;
			}

			if($type->Minimum() !== null)
			{
				$this->Store($res, 'minLength', $type->Minimum());
			}

			if($type->Maximum() !== null)
			{
				$this->Store($res, 'maxLength', $type->Maximum());
			}

			if($type->Pattern() !== '')
			{
				$this->Store($res, 'pattern', $type->Pattern());
			}

			if(count($type->Enums()) > 0)
			{
				$this->Store($res, 'enum', array_keys($type->Enums()));
			}
		}

		// Collection case
		elseif($type instanceof TTypeCollection)
		{
			$this->Store($res, 'type', 'array', true);

			if($type->ItemsType() !== null)
			{
				$this->Store($res, 'items', $this->TypeDoc($type->ItemsType()));
			}
			else
			{
				$this->Store($res, 'items', array('type' => 'object'));
			}
		}

		// Record case
		elseif($type instanceof TTypeRecord)
		{
			$this->_Components[$type->Name()] = $type;

			$this->Store($res, '$ref', '#/components/schemas/' . $type->Name(), true);
		}

		// Other cases
		else
		{
			$this->Store($res, 'type', $type->Name(), true);
		}

		// Example value
		if($sample === null)
		{
			$this->Store($res, 'example', $type->Sample());
		}
		else
		{
			$this->Store($res, 'example', $sample);
		}

		// Default value
		if($default === null)
		{
			$this->Store($res, 'default', $type->Default());
		}
		else
		{
			$this->Store($res, 'default', $default);
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Components
	//------------------------------------------------------------------------------------------------------------------
	protected function ComponentsDoc() : array
	{
		$res = array('schemas' => $this->RecordsDoc());

		$res['schemas']['Error'] = array(
			'type' => 'object',
			'properties' => array(
				'error'             => array('type' => 'string'),
				'error_description' => array('type' => 'string'),
				'error_uri'         => array('type' => 'string')));

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Records
	//------------------------------------------------------------------------------------------------------------------
	protected function RecordsDoc() : array
	{
		$res = array();

		foreach($this->_Components as $v)
		{
			$this->Store($res, $v->Name(), $this->RecordDoc($v));
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Record
	//------------------------------------------------------------------------------------------------------------------
	protected function RecordDoc(TTypeRecord $record) : array
	{
		$res        = array();
		$properties = array();
		$required   = array();
		$sample     = $record->Sample();

		foreach($record->Properties() as $k => $v)
		{
			if($record->IsMandatoryProperty($k))
			{
				$required[] = $k;
			}

			$properties[$k] = $this->TypeDoc($v, $sample[$k]);
		}

		$this->Store($res, 'type', 'object', true);

		if(!empty($required))
		{
			$this->Store($res, 'required', $required);
		}

		$this->Store($res, 'properties', $properties);


		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Security
	//------------------------------------------------------------------------------------------------------------------
	protected function SecurityDoc() : array
	{
		$res = array();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Tags
	//------------------------------------------------------------------------------------------------------------------
	protected function TagsDoc() : array
	{
		$res = array();

		foreach($this->Api()->Chapters() as $v)
		{
			$res[] = $this->TagDoc($v);
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Tag
	//------------------------------------------------------------------------------------------------------------------
	protected function TagDoc(TChapter $chapter) : array
	{
		$res = array();

		$this->Store($res, 'name'        , $chapter->Name()       , true);
		$this->Store($res, 'description' , $chapter->Description());

		if($chapter->ExternalDocUrl() !== '')
		{
			$this->Store($res, 'externalDocs', $this->TagsExternalDocs($chapter));
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Tags external doc
	//------------------------------------------------------------------------------------------------------------------
	protected function TagsExternalDocs(TChapter $chapter) : array
	{
		$res = array();

		$this->Store($res, 'description', $chapter->ExternalDocDescription());
		$this->Store($res, 'url'        , $chapter->ExternalDocUrl()        );

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Api external docs
	//------------------------------------------------------------------------------------------------------------------
	protected function ApiExternalDocsDoc() : array
	{
		$res = array();

		$this->Store($res, 'description', $this->Api()->ExternalDocDescription());
		$this->Store($res, 'url'        , $this->Api()->ExternalDocUrl()        , true);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Contact
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


	//------------------------------------------------------------------------------------------------------------------
	// Gets Doc : Operation external docs
	//------------------------------------------------------------------------------------------------------------------
	protected function OperationExternalDocsDoc(TOperation $operation) : array
	{
		$res = array();

		$this->Store($res, 'description', $operation->ExternalDocDescription());
		$this->Store($res, 'url'        , $operation->ExternalDocUrl()        , true);

		return $res;
	}
}
