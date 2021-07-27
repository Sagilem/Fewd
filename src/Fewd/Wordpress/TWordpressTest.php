<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Wordpress;


use Fewd\Core\ATest;
use Fewd\Core\TCore;


class TWordpressTest extends ATest
{
	//------------------------------------------------------------------------------------------------------------------
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		$core = new TCore();
		$core->Init();

		$wordpress = new TWordpress($core);
		$wordpress->Init();

		// No more test can be done at this stage, since usage needs a visual output
	}
}
