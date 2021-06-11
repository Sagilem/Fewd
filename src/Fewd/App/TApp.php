<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\App;


use Fewd\Core\TCore;
use Fewd\Timer\TTimer;
use Fewd\Tracer\TTracer;
use Fewd\Router\TRouter;
use Fewd\Html\THtml;
use Fewd\Bootstrap\TBootstrap;
use Fewd\Translator\TTranslator;
use Fewd\Api\TApi;

use Fewd\Core\TCoreTest;
use Fewd\Timer\TTimerTest;
use Fewd\Tracer\TTracerTest;
use Fewd\Router\TRouterTest;
use Fewd\Html\THtmlTest;
use Fewd\Bootstrap\TBootstrapTest;
use Fewd\Translator\TTranslatorTest;
use Fewd\Api\TApiTest;


class TApp
{
	// Core
	private $_Core;
	public final function Core() : TCore { return $this->_Core; }

	// Timer
	private $_Timer;
	public final function Timer() : TTimer { return $this->_Timer; }

	// Tracer
	private $_Tracer;
	public final function Tracer() : TTracer { return $this->_Tracer; }

	// Router
	private $_Router;
	public final function Router() : TRouter { return $this->_Router; }

	// Html
	private $_Html;
	public final function Html() : THtml { return $this->_Html; }

	// Bootstrap
	private $_Bootstrap;
	public final function Bootstrap() : TBootstrap { return $this->_Bootstrap; }

	// Translator
	private $_Translator;
	public final function Translator() : TTranslator { return $this->_Translator; }

	// Api
	private $_Api;
	public final function Api() : TApi { return $this->_Api; }

	// Settings storage
	protected $_Settings = array();

	// Indicates if html pages are beautified
	private $_IsBeautified = true;
	public final function IsBeautified() : bool { return $this->_IsBeautified;  }
	public       function BeautifyOn()          { $this->_IsBeautified = true;  }
	public       function BeautifyOff()         { $this->_IsBeautified = false; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(array $settings = array())
	{
		$this->_Settings = $settings;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		$this->_Core         = $this->DefineCore();
		$this->_Timer        = $this->DefineTimer();
		$this->_Tracer       = $this->DefineTracer();
		$this->_Router       = $this->DefineRouter();
		$this->_Html         = $this->DefineHtml();
		$this->_Bootstrap    = $this->DefineBootstrap();
		$this->_Translator   = $this->DefineTranslator();
		$this->_Api          = $this->DefineApi();

		$this->InitMoments();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Inits moments
	//------------------------------------------------------------------------------------------------------------------
	protected function InitMoments()
	{
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Core
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineCore() : TCore
	{
		$res = new TCore();
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Timer
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineTimer() : TTimer
	{
		$res = new TTimer($this->Core());
		$res->Init();

		if($this->Core()->IsLocalhost())
		{
			$res->OutputOn();
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Tracer
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineTracer() : TTracer
	{
		$logDirname = $this->StringSetting('Fewd.Tracer.LogDirname', '');

		$res = new TTracer($this->Core(), $logDirname);
		$res->Init();

		if($this->Core()->IsLocalhost())
		{
			$res->DebugOn();
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Router
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineRouter() : TRouter
	{
		$res = new TRouter($this->Core());
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Html
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineHtml() : THtml
	{
		$res = new THtml($this->Core());
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Bootstrap
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineBootstrap() : TBootstrap
	{
		$res = new TBootstrap($this->Core(), $this->Html());
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Translator
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineTranslator() : TTranslator
	{
		$defaultCulture = $this->StringSetting('Fewd.Translator.DefaultCulture', '');

		$res = new TTranslator($this->Core(), $defaultCulture);
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Api
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineApi() : TApi
	{
		$root                  = $this->StringSetting('Fewd.Api.Root'                 , '');
		$title                 = $this->StringSetting('Fewd.Api.Title'                , '');
		$description           = $this->StringSetting('Fewd.Api.Description'          , '');
		$termsOfService        = $this->StringSetting('Fewd.Api.TermsOfService'       , '');
		$documentationVersion  = $this->StringSetting('Fewd.Api.DocumentationVersion' , '');
		$implementationVersion = $this->StringSetting('Fewd.Api.ImplementationVersion', '');
		$contactName           = $this->StringSetting('Fewd.Api.ContactName'          , '');
		$contactUrl            = $this->StringSetting('Fewd.Api.ContactUrl'           , '');
		$contactEmail          = $this->StringSetting('Fewd.Api.ContactEmail'         , '');

		$res = new TApi(
			$this->Core(),
			$this->Router(),
			$root,
			$title,
			$description,
			$termsOfService,
			$documentationVersion,
			$implementationVersion,
			$contactName,
			$contactUrl,
			$contactEmail);

		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a string setting
	//------------------------------------------------------------------------------------------------------------------
	public function StringSetting(string $id, string $default) : string
	{
		if(isset($this->_Settings[$id]))
		{
			return '' . $this->_Settings[$id];
		}

		return '' . $default;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets an integer setting
	//------------------------------------------------------------------------------------------------------------------
	public function IntegerSetting(string $id, int $default) : int
	{
		if(isset($this->_Settings[$id]))
		{
			return 1 * $this->_Settings[$id];
		}

		return 1 *  $default;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a float setting
	//------------------------------------------------------------------------------------------------------------------
	public function DoubleSetting(string $id, float $default) : float
	{
		if(isset($this->Settings[$id]))
		{
			return 1.0 * $this->_Settings[$id];
		}

		return 1.0 * $default;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a boolean setting
	//------------------------------------------------------------------------------------------------------------------
	public function BooleanSetting(string $id, bool $default) : bool
	{
		if(isset($this->_Settings[$id]))
		{
			return $this->_Settings[$id] ? true : false;
		}

		return $default ? true : false;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs unit tests
	//------------------------------------------------------------------------------------------------------------------
	public function Test()
	{
		// No test on production environment
		if(!$this->Core()->IsLocalhost())
		{
			return;
		}

		// Creates tests
		$coreTest       = new TCoreTest();
		$timerTest      = new TTimerTest();
		$tracerTest     = new TTracerTest();
		$routerTest     = new TRouterTest();
		$htmlTest       = new THtmlTest();
		$bootstrapTest  = new TBootstrapTest();
		$translatorTest = new TTranslatorTest();
		$apiTest        = new TApiTest();

		// Runs tests
		$coreTest       ->Run();
		$timerTest      ->Run();
		$tracerTest     ->Run();
		$routerTest     ->Run();
		$htmlTest       ->Run();
		$bootstrapTest  ->Run();
		$translatorTest ->Run();
		$apiTest        ->Run();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs app
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		$this->Router()->Run();
	}
}