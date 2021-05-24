<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Bootstrap;


use Fewd\Core\AModule;
use Fewd\Core\TCore;
use Fewd\Html\THtml;


class TBootstrap extends AModule
{
	// Html
	private $_Html;
	public function Html() : THtml { return $this->_Html; }

	// Sub menus internal counter
	protected $_SubMenusCounter;

 	// Element classes
	public function SubMenuClass()        : string { return 'fewd-sub-menu';   }
	public function CaretClass()          : string { return 'fewd-caret';      }
	public function OverlayClass()        : string { return 'fewd-overlay';    }
	public function SidebarClass()        : string { return 'fewd-sidebar';    }
	public function DragClass()           : string { return 'fewd-drag';       }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(TCore $core, THtml $html)
	{
		parent::__construct($core);

		$this->_Html = $html;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_SubMenusCounter = 1;

		$this->InitMoments();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Inits moments
	//------------------------------------------------------------------------------------------------------------------
	protected function InitMoments()
	{
		$dir = $this->Core()->RelativeLink(__DIR__);

		// Css resources
		$this->Html()->RecordCssLink('https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css',
			'', 'sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z', 'anonymous');

		$this->Html()->RecordCssLink($dir . '/Css/Bootstrap.css');

		$this->Html()->RecordCssLink($dir . '/Fonts/line-awesome/css/line-awesome.min.css');

		// Js resources
		$this->Html()->RecordLateJsLink('https://code.jquery.com/jquery-3.5.1.slim.min.js',
			'sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj', 'anonymous');

		$this->Html()->RecordLateJsLink(
			'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js',
			'sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN', 'anonymous');

		$this->Html()->RecordLateJsLink(
			'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js',
			'sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV', 'anonymous');

		$this->Html()->RecordLateJsLink($dir . '/Js/Bootstrap.js');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given url corresponds to current url
	//------------------------------------------------------------------------------------------------------------------
	public function IsActiveUrl(string $url) : bool
	{
		return false;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Card opening tag
	//------------------------------------------------------------------------------------------------------------------
	public function CardOpeningTag(
		string $class    = '',
		string $header   = '',
		string $imageUrl = '',
		string $title    = '',
		string $pill     = '') : string
	{
		// Opens card
		$res = '<div class="card' . ($class === '' ? '' : ' ' . $class) . '">';

		// If an image was provided :
		// Displays it
		if($imageUrl !== '')
		{
			$res.= '<img src="' . $imageUrl . '" class="card-img-top"';

			if($header !== '')
			{
				$res.= ' alt="' . str_replace($header, '"', '&quot;') . '"';
			}

			$res.= ' />';
		}

		// If an header was provided :
		// Displays it
		elseif($header !== '')
		{
			$res.= '<div class="card-header">';

			$res.= $header;

			if($pill !== '')
			{
				$res.= '<span class="badge badge-pill badge-primary">' . $pill . '</span>';
			}

			$res.= '</div>';
		}

		// Opens body
		$res.= '<div class="card-body">';

		// If a title was provided :
		// Displays it
		if($title !== '')
		{
			$res.= '<div class="card-title">' . $title . '</div>';
		}

		// Card text
		$res.= '<div class="card-text">';

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Card closing tag
	//------------------------------------------------------------------------------------------------------------------
	public function CardClosingTag(
		string $footer      = '',
		string $buttonTitle = '',
		string $buttonIcon  = '',
		string $buttonId    = '',
		string $buttonClass = '') : string
	{
		// Closes text
		$res = '</div>';

		// Closes body
		$res.= '</div>';

		// If a footer was provided :
		if($footer !== '')
		{
			// Opens card footer
			$res.= '<div class="card-footer">';

			// Displays footer
			$res.= $footer;

			// If a button was provided :
			if(($buttonTitle !== '') || ($buttonIcon !== ''))
			{
				// Opens button
				$res.= '<div class="fewd-buttons"><button class="btn btn-primary';

				if($buttonIcon !== '')
				{
					$res.= ' btn-icon';
				}

				if($buttonClass !== '')
				{
					$res.= ' ' . $buttonClass;
				}

				$res.= '"';

				if($buttonId !== '')
				{
					$res.= ' id="' . $buttonId . '"';
				}

				if($buttonTitle !== '')
				{
					$res.= ' title="' . str_replace($buttonTitle, '"', '&quot;') . '"';
				}

				$res.= '>';

				// If an icon was provided :
				// Displays it
				if($buttonIcon !== '')
				{
					$res.= '<i class="' . $buttonIcon . '"></i>';
				}

				// Otherwise :
				// Displays button title
				else
				{
					$res.= $buttonTitle;
				}

				// Closes button
				$res.= '</button></div>';
			}

			// Closes card footer
			$res.= '</div>';
		}

		// Closes card
		$res.= '</div>';

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Container
	//------------------------------------------------------------------------------------------------------------------
	public function MakeContainer(string $id) : TContainer
	{
		$res = new TContainer(
			$this,
			$this->Core(),
			$this->Html(),
			$id);

		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Overlay
	//------------------------------------------------------------------------------------------------------------------
	public function MakeOverlay(string $id) : TOverlay
	{
		$res = new TOverlay(
			$this,
			$this->Core(),
			$this->Html(),
			$id);

		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Sidebar
	//------------------------------------------------------------------------------------------------------------------
	public final function MakeSidebar(
		string $id,
		string $title,
		bool   $isResizable = true,
		?TOverlay $overlay  = null) : TSidebar
	{
		$res = new TSidebar(
			$this,
			$this->Core(),
			$this->Html(),
			$id,
			$title,
			$isResizable,
			$overlay
		);

		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Navbar
	//------------------------------------------------------------------------------------------------------------------
	public function MakeNavbar(
		string    $id,
		string    $homeUrl,
		string    $homeTitle,
		bool      $isDark   = false,
		?TSidebar $sidebar  = null) : TNavbar
	{
		$res = new TNavbar(
			$this,
			$this->Core(),
			$this->Html(),
			$id,
			$homeUrl,
			$homeTitle,
			$isDark,
			$sidebar);

		$res->Init();

		return $res;
	}

}
