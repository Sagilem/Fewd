<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Bootstrap;

use Fewd\Bootstrap\TBootstrap;
use Fewd\Core\TCore;
use Fewd\Html\THtml;
use Fewd\Html\AComponent;


class TMenu extends AComponent
{
	// Bootstrap
	private $_Bootstrap;
	public final function Bootstrap() : TBootstrap { return $this->_Bootstrap; }

	// Items
	private $_Items = array();
	public final function Items()             : array { return $this->_Items; }
	public final function Item(   string $id) : array { return $this->_Items[$id] ?? array(); }
	public final function HasItem(string $id) : bool  { return isset($this->_Items[$id]); }


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
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Items[''] = array('items' => array());

		$this->InitItems();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init items
	//------------------------------------------------------------------------------------------------------------------
	protected function InitItems()
	{
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if it is a submenu
	//------------------------------------------------------------------------------------------------------------------
	protected function IsSubmenu(string $id) : bool
	{
		return !empty($this->_Items[$id]['items']);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds an item
	//------------------------------------------------------------------------------------------------------------------
	public function AddItem(
		string $title,
		string $url,
		string $icon        = '',
		string $toggle      = '',
		string $target      = '',
		string $class       = '',
		string $description = '',
		string $id          = '') : string
	{
		return $this->AddSubItem(
			'',
			$title,
			$url,
			$icon,
			$toggle,
			$target,
			$class,
			$description,
			$id);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a subitem
	//------------------------------------------------------------------------------------------------------------------
	public function AddSubItem(
		string $parent,
		string $title,
		string $url,
		string $icon        = '',
		string $toggle      = '',
		string $target      = '',
		string $class       = '',
		string $description = '',
		string $id          = '') : string
	{
		if($id === '')
		{
			$id = $this->Core()->Ticket();
		}

		$this->_Items[$id] = array(
			'id'          => $id,
			'parent'      => $parent,
			'title'       => $title,
			'url'         => $url,
			'icon'        => $icon,
			'toggle'      => $toggle,
			'target'      => $target,
			'class'       => $class,
			'description' => $description,
			'items'       => array());

		$this->_Items[$parent]['items'][$id] = $id;

		return $id;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a submenu to root menu
	//------------------------------------------------------------------------------------------------------------------
	public function AddSubmenu(
		string $title,
		string $icon        = '',
		string $class       = '',
		string $description = '',
		string $id          = '') : string
	{
		return $this->AddSubSubmenu('', $title, $icon, $class, $description, $id);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a submenu to a parent menu
	//------------------------------------------------------------------------------------------------------------------
	public function AddSubSubmenu(
		string $parent,
		string $title,
		string $icon        = '',
		string $class       = '',
		string $description = '',
		string $id          = '') : string
	{
		return $this->AddSubItem($parent, $title, '', $icon, '', '', $class, $description, $id);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Removes an item
	//------------------------------------------------------------------------------------------------------------------
	public function RemoveItem(string $id)
	{
		$this->RemoveSubItem('', $id);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Removes a sub item
	//------------------------------------------------------------------------------------------------------------------
	public function RemoveSubItem(string $parent, string $id)
	{
		foreach($this->_Items[$id]['items'] as $k => $v)
		{
			$this->RemoveSubItem($id, $k);
		}

		unset($this->_Items[$parent]['items'][$id]);
		unset($this->_Items[$id]);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Item tag
	//------------------------------------------------------------------------------------------------------------------
	protected function ItemTag(string $id) : string
	{
		// Gets item
		$item = $this->Item($id);

		// Gets complete id
		$id = $this->Id() . '__' . $id;

		// Leaf item data
		if(empty($item['items']))
		{
			$url       = $item['url'];
			$toggle    = $item['toggle'];
			$target    = $item['target'];
			$isSubmenu = false;
			$isActive  = $this->Bootstrap()->IsActiveUrl($url);
		}

		// Submenu item data
		else
		{
			$url       = '#';
			$toggle    = 'collapse';
			$target    = $id;
			$isSubmenu = true;
			$isActive  = false;
		}

		// Opening tag
		$res = '<li';

		if(!$isSubmenu)
		{
			$res.= ' id="' . $id . '"';
		}

		// Class
		$res.= ' class="nav-item';

		if($item['class'] !== '')
		{
			$res.= ' ' . $item['class'];
		}

		if($isActive)
		{
			$res.= ' active';
		}

		$res.= '">';

		// Inner link
		$res.= '<a class="nav-link collapsed"';
		$res.= ' href="' . $url . '"';

		$description = $item['description'];
		if($description === '')
		{
			$description = $item['title'];
		}

		$res.= $this->Html()->Attribute('title', $description);

		if($toggle !== '')
		{
			$res.= ' data-toggle="' . $toggle . '"';
		}

		if($target !== '')
		{
			$res.= ' data-target="#' . $target . '"';
		}

		$res.= '>';

		// Icon
		if($item['icon'] !== '')
		{
			$res.= '<i class="' . $item['icon'] . '"></i>';
		}

		// Title
		$res.= $item['title'];

		// If it is a submenu :
		if($isSubmenu)
		{
			// Adds opened/closed caret
			$res.= '<b class="' . $this->Bootstrap()->CaretClass() . '"></b>';

			// Closes inner link
			$res.= '</a>';

			// Opens sub menu
			$res.= '<ul class="' . $this->Bootstrap()->SubMenuClass() . ' list-unstyled flex-column collapse"';
			$res.= ' id="' . $id . '" aria-expanded="false">';

			// Adds sub items
			foreach($item['items'] as $k => $v)
			{
				$res.= $this->ItemTag($k);
			}
		}

		// Closes tag
		if($isSubmenu)
		{
			$res.= '</ul></li>';
		}
		else
		{
			$res.= '</a></li>';
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Opening tag
	//------------------------------------------------------------------------------------------------------------------
	protected function OpeningTag() : string
	{
		return '<ul class="nav menu">';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Closing Tag
	//------------------------------------------------------------------------------------------------------------------
	protected function ClosingTag() : string
	{
		return '</ul>';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Content
	//------------------------------------------------------------------------------------------------------------------
	protected function Content() : string
	{
		$res  = '';
		$root = $this->Item('');

		foreach($root['items'] as $k => $v)
		{
			$res.= $this->ItemTag($k);
		}

		return $res;
	}
}