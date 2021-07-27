<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Html;


use Fewd\Core\ATest;
use Fewd\Core\TCore;


class THtmlTest extends ATest
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

		$this->Check($html->OpeningTag('p', array('style' => 'background:red')), '<p style="background:red"');
		$this->Check($html->OpeningTag('', array('' => '')), '< =""');
	}
}
