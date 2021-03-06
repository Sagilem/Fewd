<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\TCore;
use Fewd\Core\ATest;
use Fewd\Router\TRouter;


class TApiTest extends ATest
{
	//------------------------------------------------------------------------------------------------------------------
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		// Inits the Api
		$core = new TCore();
		$core->Init();

		$router = new TRouter($core);
		$router->Init();

		$api = new TApi($core, $router);
		$api->Init();

		// TODO
		// Difficult to test, since Api needs some context from the HTTP query (headers, arguments...)
	}
}
