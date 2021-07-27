<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\TCore;
use Fewd\Core\AThing;


class TJoin extends AThing
{
	// Data
	private $_Data;
	public final function Data() : TData { return $this->_Data; }

	// Source
	private $_Source;
	public final function Source() : TDatatable|TSelect        { return $this->_Source;   }
	public final function SetSource(TDatatable|TSelect $value) { $this->_Source = $value; }

	// Links
	private $_Links;
	public final function Links()                : array        { return $this->_Links;              }
	public final function Link(      string $id) : string       { return $this->_Links[$id] ?? null; }
	public final function HasLink(   string $id) : bool         { return isset($this->_Links[$id]);  }
	public       function AddLink(   string $id, string $value) { $this->_Links[$id] = $value;       }
	public       function RemoveLink(string $id)                { unset($this->_Links[$id]);         }
	public       function ClearLinks()                          { $this->_Links = array();           }

	// Fields
	private $_Fields;
	public final function Fields()                : array             { return $this->_Fields;              }
	public final function Field(      string $id) : string            { return $this->_Fields[$id] ?? null; }
	public final function HasField(   string $id) : bool              { return isset($this->_Fields[$id]);  }
	public       function AddField(   string $id, string $value = '') { $this->_Fields[$id] = $value;       }
	public       function RemoveField(string $id)                     { unset($this->_Fields[$id]);         }
	public       function ClearFields()                               { $this->_Fields = array();           }

	// Conditions
	private $_Conditions;
	public final function Conditions()                : array        { return $this->_Conditions;              }
	public final function Condition(      string $id) : string       { return $this->_Conditions[$id] ?? null; }
	public final function HasCondition(   string $id) : bool         { return isset($this->_Conditions[$id]);  }
	public       function AddCondition(   string $id, string $value) { $this->_Conditions[$id] = $value;       }
	public       function RemoveCondition(string $id)                { unset($this->_Conditions[$id]);         }
	public       function ClearConditions()                          { $this->_Conditions = array();           }

	// Join type
	private $_Jointype;
	public final function Jointype() : string        { return $this->_Jointype;                            }
	public       function SetJointype(string $value) { $this->_Jointype = $this->Data()->Jointype($value); }

	// Alias
	private $_Alias;
	public final function Alias() : string        { return $this->_Alias;   }
	public       function SetAlias(string $value) { $this->_Alias = $value; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore              $core,
		TData              $data,
		TDatatable|TSelect $source,
		array              $links,
		array              $fields,
		array              $conditions,
		string             $jointype,
		string             $alias)
	{
		parent::__construct($core);

		$this->_Data       = $data;
		$this->_Source     = $source;
		$this->_Links      = $links;
		$this->_Fields     = $fields;
		$this->_Conditions = $conditions;
		$this->_Jointype   = $jointype;
		$this->_Alias      = $alias;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$links      = $this->Links();
		$fields     = $this->Fields();
		$conditions = $this->Conditions();

		$this->Clear();

		foreach($fields as $k => $v)
		{
			$this->AddField($k, $v);
		}

		foreach($links as $k => $v)
		{
			$this->AddLink($k, $v);
		}

		foreach($conditions as $k => $v)
		{
			$this->AddCondition($k, $v);
		}

		$this->SetAlias(   $this->Alias()   );
		$this->SetJointype($this->Jointype());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Clear
	//------------------------------------------------------------------------------------------------------------------
	public function Clear()
	{
		$this->ClearLinks();
		$this->ClearFields();
		$this->ClearConditions();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if join source is a datatable
	//------------------------------------------------------------------------------------------------------------------
	public function IsSourceDatatable()
	{
		return ($this->Source() instanceof TDatatable);
	}
}
