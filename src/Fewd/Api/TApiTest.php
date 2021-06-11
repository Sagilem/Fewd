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
		$core = new TCore();
		$core->Init();

		$router = new TRouter($core);
		$router->Init();

		$api = new TApi(
			$core,
			$router,
			'apitest',
			'My title',
			'My description',
			'My termsOfService',
			'1.2.3',
			'',
			'My contactName',
			'My contactUrl',
			'My contactEmail');

		$api->Init();

		// TODO
	}
}
