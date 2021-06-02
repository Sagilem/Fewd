<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Bootstrap;

use Fewd\Bootstrap\TBootstrap;
use Fewd\Core\TCore;
use Fewd\Html\THtml;


class TNavbar extends TMenu
{
	// Home url
	private $_HomeUrl;
	public final function HomeUrl() : string { return $this->_HomeUrl; }

	// Home title
	private $_HomeTitle;
	public final function HomeTitle() : string { return $this->_HomeTitle; }

	// Indicates if in dark mode
	private $_IsDark;
	public final function IsDark() : bool { return $this->_IsDark; }

	// Sidebar
	private $_Sidebar;
	public final function Sidebar() : ?TSidebar { return $this->_Sidebar; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TBootstrap $bootstrap,
		TCore      $core,
		THtml      $html,
		string     $id,
		string     $homeUrl,
		string     $homeTitle,
		bool       $isDark   = false,
		?TSidebar  $sidebar  = null)
	{
		parent::__construct($bootstrap, $core, $html, $id);

		$this->_HomeUrl   = $homeUrl;
		$this->_HomeTitle = $homeTitle;
		$this->_IsDark    = $isDark;
		$this->_Sidebar   = $sidebar;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Inits moments
	//------------------------------------------------------------------------------------------------------------------
	protected function InitMoments()
	{
		parent::InitMoments();

		$script = $this->AnimationScript();

		$this->Html()->RecordLateJsScript($script);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates the animation script
	//------------------------------------------------------------------------------------------------------------------
	protected function AnimationScript() : string
	{
		$ret  = "\n";
		$tab  = "\t";
		$tab2 = "\t\t";
		$tab3 = "\t\t\t";

		// Opens script
		$res = '$(document).ready(function() {' . $ret;

		// If a sidebar is linked to navbar :
		// Adds an animation
		if($this->Sidebar() !== null)
		{
			// Sidebar animation (via sidebar toggler) that generates a script like that :
			//
			// $('.fewd-navbar-toggler').click(function()
			// {
			//     $('#fewd-sidebar').toggleClass('toggle');
			//     $('#fewd-overlay').toggleClass('toggle');
			//
			//     $('#fewd-navbar-menu').collapse('hide');
			// });
			$res.= $tab . '$(\'#' . $this->Sidebar()->Id() . '__toggler\').click(function() {' . $ret;
			$res.= $tab2 . '$(\'#' . $this->Sidebar()->Id() . '\').toggleClass(\'toggle\');' . $ret;

			if($this->Sidebar()->Overlay() !== null)
			{
				$res.= $tab2 . '$(\'#' . $this->Sidebar()->Overlay()->Id() . '\').toggleClass(\'toggle\');' . $ret;
			}

			$res.= $tab2 . '$(\'#' . $this->Id() . '__menu\').collapse(\'hide\');' . $ret;
			$res.= $tab . '});' . $ret . $ret;

			// Menu animation (via menu toggler) that generates a script like that :
			//
			// $('#fewd-navbar-menu-toggler').click(function()
			// {
			//      if($('#fewd-sidebar').hasClass('toggle'))
			//      {
			//          $('#fewd-sidebar').toggleClass('toggle');
			//          $('#fewd-overlay').toggleClass('toggle');
			//      }
			// });
			$res.= $tab . '$(\'#' . $this->Id() . '__menu_toggler\').click(function() {' . $ret;

			$res.= $tab2 . 'if($(\'#' . $this->Sidebar()->Id() . '\').hasClass(\'toggle\')) {' . $ret;
			$res.= $tab3 . '$(\'#' . $this->Sidebar()->Id() . '\').toggleClass(\'toggle\');' . $ret;

			if($this->Sidebar()->Overlay() !== null)
			{
				$res.= $tab3 . '$(\'#' . $this->Sidebar()->Overlay()->Id() . '\').toggleClass(\'toggle\');' . $ret;
			}

			$res.= $tab2 . '}' . $ret;

			$res.= $tab . '});' . $ret;
		}

		// Menu item animation that generates a script like that :
		//
		// $('#fewd-navbar-menu .nav-link').click(function()
		// {
		//      $('#fewd-navbar-menu').collapse('hide');
		// });
		$res.= $tab . '$(\'#' . $this->Id() . '__menu .nav-link\').click(function() {' . $ret;
		$res.= $tab2 . '$(\'#' . $this->Id() . '\').collapse(\'hide\');' . $ret;
		$res.= $tab . '});' . $ret;

		// Closes script
		$res.= '});' . $ret;

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Opening tag
	//------------------------------------------------------------------------------------------------------------------
	protected function OpeningTag() : string
	{
		// Opens navbar
		$res = '<nav class="navbar fixed-top navbar-expand-lg' . ($this->IsDark() ? ' navbar-dark' : '') . '"';
		$res.= ' id="' . $this->Id() . '"';
		$res.= ' role="navigation">';

		// Opens logo section
		$res.= '<div class="flex-row d-flex">';

        // Title
		$res.= '<a class="navbar-brand"';
		$res.= ' href="'  . $this->HomeUrl()   . '"';
		$res.= ' title="' . $this->HomeTitle() . '">';
		$res.= '</a>';

		// Sidebar toggler
		if($this->Sidebar() !== null)
		{
			$res.= '<button type="button" class="navbar-toggler"';
			$res.= ' id="' . $this->Sidebar()->Id() . '__toggler"';
			$res.= ' title="' . $this->Sidebar()->Title() . '">';
			$res.= '<span class="navbar-toggler-icon"></span>';
    	    $res.= '</button>';
		}

        // Closes logo section
		$res.= '</div>';

		// Hamburger navbar menu button (shown only on small devices)
		$res.= '<button type="button"  class="navbar-toggler" id="' . $this->Id() . '__menu_toggler"';
		$res.= ' data-toggle="collapse" data-target="#' . $this->Id() . '__menu">';
		$res.= '<span class="navbar-toggler-icon"></span>';
		$res.= '</button>';

		// Opens user menu
		$res.= '<nav class="navbar-collapse collapse" id="' . $this->Id() . '__menu">';
		$res.= '<ul class="navbar-nav ml-auto">';

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Closing tag
	//------------------------------------------------------------------------------------------------------------------
	protected function ClosingTag() : string
	{
		return '</ul></nav></nav>';
	}
}