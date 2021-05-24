<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Router;


use Fewd\Core\ATest;
use Fewd\Core\TCore;


class TRouterTest extends ATest
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

		$router->AddStrictRule('index.php'             , 'INDEX');
		$router->AddStrictRule('page.php'              , 'PAGE' );
		$router->AddRootRule(  'post-'                 , 'POST' );
		$router->AddShapeRule( 'post-'                 , '.php', 'POSTPHP');
		$router->AddShapeRule( 'faq-'                  , '.php', 'FAQPHP' );
		$router->AddRootRule(  'faq-'                  , 'FAQ' );
		$router->AddShapeRule( 'art-'                  , '.php', 'ARTPHP' , 'artid');
		$router->AddRootRule(  'art-'                  , 'ART' );
		$router->AddRegexpRule('^a([0-9]+).*[.]php$'   , 'APHP', 'aid');

		$this->Check($router->RouteId('page.php'    ), 'PAGE');
		$this->Check($router->RouteId('index'       ), '');
		$this->Check($router->RouteId('post-123.php'), 'POST');
		$this->Check($router->RouteId('faq-123.php' ), 'FAQPHP');
		$this->Check($router->RouteId('art-123.php' ), 'ARTPHP?artid=123');
		$this->Check($router->RouteId('a123.php'    ), 'APHP?aid=123');
	}
}
