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

		$router->AddStrictRule('index.php'          , 'INDEX');
		$router->AddStrictRule('page.php'           , 'PAGE' );
		$router->AddRootRule(  'post-'              , 'POST' );
		$router->AddShapeRule( 'post-'              , '.php', 'POSTPHP');
		$router->AddShapeRule( 'faq-'               , '.php', 'FAQPHP' );
		$router->AddRootRule(  'faq-'               , 'FAQ' );
		$router->AddShapeRule( 'art-'               , '.php', 'ARTPHP' , 'artid');
		$router->AddRootRule(  'art-'               , 'ART' );
		$router->AddRegexpRule('a([0-9]+)b([0-9]+)' , 'MULTI', array('aid', 'bid'));
		$router->AddRegexpRule('^a([0-9]+).*[.]php$', 'APHP', 'aid');

		$this->Check($router->Route('page.php'    , array()), 'PAGE');
		$this->Check($router->Route('index'       , array()), '');
		$this->Check($router->Route('post-123.php', array()), 'POST');
		$this->Check($router->Route('faq-123.php' , array()), 'FAQPHP');
		$this->Check($router->Route('art-123.php' , array()), 'ARTPHP?artid=123');
		$this->Check($router->Route('a123.php'    , array()), 'APHP?aid=123');
		$this->Check($router->Route('a12b34.php'  , array()), 'MULTI?aid=12&bid=34');
	}
}
