<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Timer;


use Fewd\Core\ATest;
use Fewd\Core\TCore;


class TTimerTest extends ATest
{
	//------------------------------------------------------------------------------------------------------------------
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		$core = new TCore();
		$core->Init();

		$timer = new TTimer($core);
		$timer->Init();
		$timer->OutputOff();

		// No test can be done at this stage, since usage highly depends from the context
	}
}
