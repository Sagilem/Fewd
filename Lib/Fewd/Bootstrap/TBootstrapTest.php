<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Bootstrap;


use Fewd\Core\ATest;
use Fewd\Core\TCore;
use Fewd\Html\THtml;


class TBootstrapTest extends ATest
{
	//------------------------------------------------------------------------------------------------------------------
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		$core = new TCore();
		$core->Init();

		$html = new THtml($core);
		$html->Init();

		$bootstrap = new TBootstrap($core, $html);
		$bootstrap->Init();

		// TODO
	}
}
