<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Bootstrap;

use Fewd\Bootstrap\TBootstrap;
use Fewd\Core\TCore;
use Fewd\Html\THtml;
use Fewd\Html\AComponent;


class TColumn extends AComponent
{
	// Bootstrap
	private $_Bootstrap;
	public final function Bootstrap() : TBootstrap { return $this->_Bootstrap; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TBootstrap $bootstrap,
		TCore      $core,
		THtml      $html,
		string     $id)
	{
		parent::__construct($core, $html, $id);

		$this->_Bootstrap = $bootstrap;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Opening tag
	//------------------------------------------------------------------------------------------------------------------
	protected function OpeningTag() : string
	{
		return '<div id="' . $this->Id() . '" class="col">';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Closing Tag
	//------------------------------------------------------------------------------------------------------------------
	protected function ClosingTag() : string
	{
		return '</div>';
	}
}