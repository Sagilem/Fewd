<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Tracer;


use Fewd\Core\ATest;
use Fewd\Core\TCore;


class TTracerTest extends ATest
{
	//------------------------------------------------------------------------------------------------------------------
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		$core = new TCore();
		$core->Init();

		$tracer = new TTracer($core, '');
		$tracer->Init();

		$this->Check($tracer->LogDirname(), 'Log');

		// No more test can be done at this stage, since usage needs a visual output
	}
}
