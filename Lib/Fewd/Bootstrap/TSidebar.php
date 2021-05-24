<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Bootstrap;

use Fewd\Bootstrap\TBootstrap;
use Fewd\Core\TCore;
use Fewd\Html\THtml;


class TSidebar extends TMenu
{
	// Title
	private $_Title;
	public final function Title() : string { return $this->_Title; }

	// Overlay
	private $_Overlay;
	public final function Overlay() : ?TOverlay { return $this->_Overlay; }

	// Indicates if sidebar is resizable on its right
	private $_IsResizable;
	public final function IsResizable() : bool { return $this->_IsResizable; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TBootstrap $bootstrap,
		TCore      $core,
		THtml      $html,
		string     $id,
		string     $title,
		bool       $isResizable,
		?TOverlay  $overlay    = null)
	{
		parent::__construct($bootstrap, $core, $html, $id);

		$this->_Title       = $title;
		$this->_IsResizable = $isResizable;
		$this->_Overlay     = $overlay;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Opening tag
	//------------------------------------------------------------------------------------------------------------------
	protected function OpeningTag() : string
	{
		$res = '<aside id="' . $this->Id() . '" role="navigation" class="' . $this->Bootstrap()->SidebarClass() . '">';
		$res.= '<ul class="nav flex-column sticky-top">';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Closing Tag
	//------------------------------------------------------------------------------------------------------------------
	public function ClosingTag() : string
	{
		$res = '</ul>';

		if($this->IsResizable())
		{
			$res.= '<div class="' . $this->Bootstrap()->DragClass() . '"></div>';
		}

		$res.= '</aside>';

		return $res;
	}
}