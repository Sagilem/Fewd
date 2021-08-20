<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\TCore;


class TSelect extends AConditionSql
{
	// Fields
	private $_Fields;
	public final function Fields()                : array             { return $this->_Fields;              }
	public final function Field(      string $id) : string            { return $this->_Fields[$id] ?? null; }
	public final function HasField(   string $id) : bool              { return isset($this->_Fields[$id]);  }
	public       function AddField(   string $id, string $value = '') { $this->_Fields[$id] = $value;       }
	public       function RemoveField(string $id)                     { unset($this->_Fields[$id]);         }
	public       function ClearFields()                               { $this->_Fields = array();           }

	// Groups
	private $_Groups;
	public final function Groups()                : array             { return $this->_Groups;              }
	public final function Group(      string $id) : string            { return $this->_Groups[$id] ?? null; }
	public final function HasGroup(   string $id) : bool              { return isset($this->_Groups[$id]);  }
	public       function AddGroup(   string $id, string $value = '') { $this->_Groups[$id] = $value;       }
	public       function RemoveGroup(string $id)                     { unset($this->_Groups[$id]);         }
	public       function ClearGroups()                               { $this->_Groups = array();           }

	// Havings
	private $_Havings;
	public final function Havings()                : array              { return $this->_Havings;              }
	public final function Having(      string $id) : string|array       { return $this->_Havings[$id] ?? null; }
	public final function HasHaving(   string $id) : bool               { return isset($this->_Havings[$id]);  }
	public       function AddHaving(   string $id, string|array $value) { $this->_Havings[$id] = $value;       }
	public       function RemoveHaving(string $id)                      { unset($this->_Havings[$id]);         }
	public       function ClearHavings()                                { $this->_Havings = array();           }

	// Sorts
	private $_Sorts;
	public final function Sorts()                : array                { return $this->_Sorts;              }
	public final function Sort(      string $id) : string               { return $this->_Sorts[$id] ?? null; }
	public final function HasSort(   string $id) : bool                 { return isset($this->_Sorts[$id]);  }
	public       function AddSort(   string $id, string $value)         { $this->_Sorts[$id] = $value;       }
	public       function RemoveSort(string $id)                        { unset($this->_Sorts[$id]);         }
	public       function ClearSorts()                                  { $this->_Sorts = array();           }

	// Joins
	private $_Joins;
	public final function Joins()                : array       { return $this->_Joins;              }
	public final function Join(      string $id) : TJoin       { return $this->_Joins[$id] ?? null; }
	public final function HasJoin(   string $id) : bool        { return isset($this->_Joins[$id]);  }
	public final function AddJoin(   string $id, TJoin $value) { $this->_Joins[$id] = $value;       }
	public       function RemoveJoin(string $id)               { unset($this->_Joins[$id]);         }
	public       function ClearJoins()                         { $this->_Joins = array();           }

	// Indexes
	private $_Indexes;
	public final function Indexes()               : array        { return $this->_Indexes;              }
	public final function Index(      string $id) : string       { return $this->_Indexes[$id] ?? null; }
	public final function HasIndex(   string $id) : bool         { return isset($this->_Indexes[$id]);  }
	public       function AddIndex(   string $id)                { $this->_Indexes[$id] = $id;          }
	public       function RemoveIndex(string $id)                { unset($this->_Indexes[$id]);         }
	public       function ClearIndexes()                         { $this->_Indexes = array();           }

	// Page start index (SELECT query)
	private $_PageStart;
	public final function PageStart() : int        { return $this->_PageStart;           }
	public       function SetPageStart(int $value) { $this->_PageStart = max($value, 0); }

	// Page length (SELECT query)
	private $_PageLength;
	public final function PageLength() : int        { return $this->_PageLength;           }
	public       function SetPageLength(int $value) { $this->_PageLength = max($value, 0); }

	// Indicates if the SELECT query is, in fact, a SELECT DISTINCT query
	private $_IsDistinct;
	public final function IsDistinct() : bool { return $this->_IsDistinct;         }
	public final function DistinctOn()        { return $this->_IsDistinct = true;  }
	public final function DistinctOff()       { return $this->_IsDistinct = false; }

	// Indicates if the query will directly deliver its results (or through a handle if not)
	private $_IsHuge;
	public final function IsHuge() : bool { return $this->_IsHuge;  }
	public final function HugeOn()        { $this->_IsHuge = true;  }
	public final function HugeOff()       { $this->_IsHuge = false; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		TDatatable $datatable,
		string     $alias,
		array      $fields,
		array      $conditions,
		array      $groups,
		array      $havings,
		array      $sorts,
		int        $pageStart,
		int        $pageLength,
		bool       $isDistinct,
		bool       $isHuge)
	{
		parent::__construct($core, $data, $datatable, $alias, $conditions);

		$this->_Fields     = $fields;
		$this->_Groups     = $groups;
		$this->_Havings    = $havings;
		$this->_Sorts      = $sorts;
		$this->_PageStart  = $pageStart;
		$this->_PageLength = $pageLength;
		$this->_IsDistinct = $isDistinct;
		$this->_IsHuge     = $isHuge;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		$fields     = $this->Fields();
		$groups     = $this->Groups();
		$havings    = $this->Havings();
		$sorts      = $this->Sorts();
		$pageStart  = $this->PageStart();
		$pageLength = $this->PageLength();

		parent::Init();

		$this->InitFields( $fields );
		$this->InitGroups( $groups );
		$this->InitHavings($havings);
		$this->InitSorts(  $sorts  );
		$this->InitJoins();
		$this->InitIndexes();

		$this->SetPageStart( $pageStart );
		$this->SetPageLength($pageLength);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init fields
	//------------------------------------------------------------------------------------------------------------------
	protected function InitFields(array $fields)
	{
		foreach($fields as $k => $v)
		{
			$this->AddField($k, $v);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init groups
	//------------------------------------------------------------------------------------------------------------------
	protected function InitGroups(array $groups)
	{
		foreach($groups as $k => $v)
		{
			$this->AddGroup($k, $v);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init havings
	//------------------------------------------------------------------------------------------------------------------
	protected function InitHavings(array $havings)
	{
		foreach($havings as $k => $v)
		{
			$this->AddHaving($k, $v);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init sorts
	//------------------------------------------------------------------------------------------------------------------
	protected function InitSorts(array $sorts)
	{
		foreach($sorts as $k => $v)
		{
			$this->AddSort($k, $v);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Inits joins
	//------------------------------------------------------------------------------------------------------------------
	protected function InitJoins()
	{
		// To override
	}


	//------------------------------------------------------------------------------------------------------------------
	// Inits indexes
	//------------------------------------------------------------------------------------------------------------------
	protected function InitIndexes()
	{
		// Indexes are, by default, the datatable keys
		foreach($this->Datatable()->Keys() as $k => $v)
		{
			$this->Nop($v);

			$this->AddIndex($k, $k);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Clears page
	//------------------------------------------------------------------------------------------------------------------
	public function ClearPage()
	{
		$this->SetPageStart( 0);
		$this->SetPageLength(0);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Clear
	//------------------------------------------------------------------------------------------------------------------
	public function Clear()
	{
		parent::Clear();

		$this->ClearFields();
		$this->ClearIndexes();
		$this->ClearGroups();
		$this->ClearHavings();
		$this->ClearSorts();
		$this->ClearJoins();
		$this->ClearPage();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Declares a new join
	//------------------------------------------------------------------------------------------------------------------
	public function DeclareJoin(
		TDatatable|TSelect $source,
		array              $links,
		array              $fields     = array(),
		array              $conditions = array(),
		string             $jointype   = '',
		string             $alias      = '') : TJoin
	{
		$res = $this->Data()->MakeJoin($source, $links, $fields, $conditions, $jointype, $alias);

		$this->AddJoin($alias, $res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a SELECT statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillSelect(string &$query, array &$bindings, string $indent)
	{
		$this->Nop($bindings);

		$query.= $indent . 'SELECT';

		if($this->IsDistinct())
		{
			$query.= ' DISTINCT';
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a TOP statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillTop(string &$query, array &$bindings, string $indent)
	{
		$this->Nop($indent);

		if($this->PageLength() <= 0)
		{
			return;
		}

		$statement = $this->Database()->TopStatement(':page_start', ':page_length');

		if($statement !== '')
		{
			$query.= ' ' . $statement;

			$bindings['page_start' ] = $this->PageStart();
			$bindings['page_length'] = $this->PageLength();
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills a FIELD statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillField(
		string             &$query,
		array              &$bindings,
		string              $key,
		string              $value,
		TDatatable|TSelect  $source,
		string              $dottedAlias)
	{
		// Possible cases :
		// 'key'  => ''                   : alias.`key`
		// 'key'  => 'key'                : alias.`key`
		// 'key'  => 'value'              : alias.`value` AS `key`
		// 'key:' => 'value'              : :field_1260 AS `key`     + bindings['field_1260] = 'value'
		// 'key&' => 'value'              : 'value' AS `key`
		// 'key@' => 'value'              : value AS `key`
		// 'key'  => TData::AGGREGATION_X : X(alias.`key`) AS `key`
		$suffix = substr($key, -1);

		if($suffix === ':')
		{
			$counter = $this->BindingsCounter();

			$query.= ':field_' . $counter . ' AS ' . $this->Database()->Quote(substr($key, 0, -1));
			$bindings['field_' . $counter] = $value;
		}
		elseif($suffix === '&')
		{
			$query.= $this->Database()->StringQuote($value);
			$query.= ' AS ' . $this->Database()->Quote(substr($key, 0, -1));
		}
		elseif($suffix == '@')
		{
			$query.= $value . ' AS ' . $this->Database()->Quote(substr($key, 0, -1));
		}
		elseif(($value === '') || ($value === $key))
		{
			if($this->IsFieldIgnored($key, $source))
			{
				return;
			}

			$field = $this->Database()->Quote($key);

			$query.= $dottedAlias . $field . ' AS ' . $field;
		}
		elseif($this->Data()->IsAggregation($value))
		{
			if($this->IsFieldIgnored($key, $source))
			{
				return;
			}

			$field = $this->Database()->Quote($key);

			$query.= $this->Database()->AggregationStatement($value, $dottedAlias . $field);
			$query.= ' AS ' . $field;
		}
		else
		{
			if($this->IsFieldIgnored($value, $source))
			{
				return;
			}

			$field = $this->Database()->Quote($key);

			$query.= $dottedAlias . $this->Database()->Quote($value) . ' AS ' . $field;
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a FIELDS statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillFields(string &$query, array &$bindings, string $indent)
	{
		// Inits separator
		$sep = $this->Ret() . $indent;

		// If no field :
		// Returns a "*" selector instead
		if(empty($this->Fields()))
		{
			$query.= $sep . $this->DottedAlias() . '*';
		}

		// Otherwise,
		// For each field :
		else
		{
			foreach($this->Fields() as $k => $v)
			{
				$query.= $sep;

				$this->FillField($query, $bindings, $k, $v, $this->Datatable(), $this->DottedAlias());

				$sep = ',' . $this->Ret() . $indent;
			}
		}

		// Joins
		foreach($this->Joins() as $k => $v)
		{
			foreach($v->Fields() as $kk => $vv)
			{
				$query.= $sep;

				$dottedAlias = $k . '.';

				$this->FillField($query, $bindings, $kk, $vv, $v->Source(), $dottedAlias);

				$sep = ',' . $this->Ret() . $indent;
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a FROM statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillFrom(string &$query, array &$bindings, string $indent)
	{
		$this->Nop($bindings);

		// Adds the FROM statement
		$name = $this->Database()->Quote($this->Datatable()->Name());

		$query.= $this->Ret() . $indent . 'FROM';

		$query.= $this->Ret() . $this->Tab() . $indent;
		$query.= $name;

		if($this->Alias() !== '')
		{
			$query.= ' ' . $this->Alias();
		}

		// Joins
		foreach($this->Joins() as $k => $v)
		{
			// JOIN statement
			$jointype = $v->Jointype();

			$query.= $this->Ret() . $this->Ret() . $indent . $this->Tab();
			$query.= $this->Database()->JoinStatement($jointype);

			if($v->IsSourceDatatable())
			{
				$datatable = $v->Source();

				$joinedName = $this->Database()->Quote($datatable->Name());

				$query.= ' ' . $joinedName;
			}
			else
			{
				$select = $v->Source();

				$query.= $this->Ret() . $indent . $this->Tab() . '(' . $this->Ret();

				$select->FillSelect($query, $bindings, $indent . $this->Tab() . $this->Tab());

				$query.= $this->Ret() . $indent . $this->Tab() . ')';
			}

			// ALIAS statement
			$query.= ' ' . $k;

			$joinedAlias = $k . '.';

			// ON statement
			$sep = $this->Ret() . $indent . $this->Tab() . 'ON  ';

			foreach($v->Links() as $kk => $vv)
			{
				if($this->IsFieldIgnored($vv, $this->Datatable()) || $this->IsFieldIgnored($kk, $v->Source()))
				{
					continue;
				}

				$query.= $sep  . $joinedAlias         . $this->Database()->Quote($kk);
				$query.= ' = ' . $this->DottedAlias() . $this->Database()->Quote($vv);

				$sep = $this->Ret() . $indent . $this->Tab() . 'AND ';
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a WHERE statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillWhere(string &$query, array &$bindings, string $indent)
	{
		// Inherited
		$where = '';
		parent::FillWhere($where, $bindings, $indent);

		$query.= $where;

		// Inits separator
		if($where === '')
		{
			$sep = $this->Ret() . $indent . 'WHERE ';
		}
		else
		{
			$sep = $this->Ret() . $indent . 'AND   ';
		}

		// Adds join conditions
		foreach($this->Joins() as $k => $v)
		{
			foreach($v->Conditions() as $kk => $vv)
			{
				$query.= $sep;

				$this->FillCondition($query, $bindings, $v->Source(), $k, $kk, $vv);

				$sep = $this->Ret() . $indent . 'AND   ';
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a GROUP BY statement
	//------------------------------------------------------------------------------------------------------------------
	public function FillGroupBy(string &$query, array &$bindings, string $indent)
	{
		$this->Nop($bindings);

		$sep = $this->Ret() . $indent . 'GROUP BY' . $this->Ret() . $this->Tab() . $indent;

		foreach($this->Groups() as $k => $v)
		{
			// Possible cases :
			//
			// 'key'  => ''                : alias.`key`
			// 'key'  => 'key'             : alias.`key`
			// 'key'  => 'alias'           : alias.`key`
			// 'key'  => 'other_alias'     : other_alias.`key`
			// 'key@' => ''                : key
			if(substr($k, -1) === '@')
			{
				$query.= $sep . substr($k, 0, -1);
			}
			elseif(($v === '') || ($v === $k) || ($v === $this->Alias()))
			{
				if($this->IsFieldIgnored($k, $this->Datatable()))
				{
					continue;
				}

				$query.= $sep . $this->DottedAlias() . $this->Database()->Quote($k);
			}
			else
			{
				// If the "other alias" does not correspond to a join,
				// Or if field must be ignored :
				// Ignores it
				if(!$this->HasJoin($v) || $this->IsFieldIgnored($k, $this->Join($v)->Source()))
				{
					continue;
				}

				// Otherwise :
				// Adds it
				$query.= $sep . $v . '.' . $this->Database()->Quote($k);
			}

			$sep = ',' . $this->Ret() . $this->Tab() . $indent;
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a HAVING statement
	//------------------------------------------------------------------------------------------------------------------
	public function FillHaving(string &$query, array &$bindings, string $indent)
	{
		$sep = $this->Ret() . $indent . 'HAVING ';

		foreach($this->Havings() as $k => $v)
		{
			$query.= $sep;

			$this->FillCondition($query, $bindings, $this->Datatable(), $this->Alias(), $k, $v);

			$sep = $this->Ret() . $indent . 'AND    ';
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a ORDER BY statement
	//------------------------------------------------------------------------------------------------------------------
	public function FillOrderBy(string &$query, array &$bindings, string $indent)
	{
		$this->Nop($bindings);

		$sep = $this->Ret() . $indent . 'ORDER BY' . $this->Ret() . $this->Tab() . $indent;

		foreach($this->Sorts() as $k => $v)
		{
			// Possible cases :
			//
			// 'key'  => ''                : alias.`key`
			// 'key'  => 'key'             : alias.`key`
			// 'key'  => 'alias'           : alias.`key`
			// 'key'  => 'other_alias'     : other_alias.`key`
			// 'key@' => ''                : key
			//
			// Add a "-" just before key to sort it in descending order, for instance : '-key' => 'key'
			if(substr($k, 0, 1) === '-')
			{
				$k = substr($k, 1);
				$direction = ' DESC';
			}
			else
			{
				$direction = ' ASC';
			}

			if(substr($k, -1) === '@')
			{
				$query.= $sep . substr($k, 0, -1) . $direction;
			}
			elseif(($v === '') || ($v === $k) || ($v === $this->Alias()))
			{
				if($this->IsFieldIgnored($k, $this->Datatable()))
				{
					continue;
				}

				$query.= $sep . $this->DottedAlias() . $this->Database()->Quote($k) . $direction;
			}
			else
			{
				// If the "other alias" does not correspond to a join,
				// Or if field must be ignored :
				// Ignores it
				if(!$this->HasJoin($v) || $this->IsFieldIgnored($k, $this->Join($v)->Source()))
				{
					continue;
				}

				// Otherwise :
				// Adds it
				$query.= $sep . $v . '.' . $this->Database()->Quote($k) . $direction;
			}

			$sep = ',' . $this->Ret() . $this->Tab() . $indent;
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a LIMIT statement
	//------------------------------------------------------------------------------------------------------------------
	public function FillLimit(string &$query, array &$bindings, string $indent)
	{
		$this->Nop($indent);

		if($this->PageLength() <= 0)
		{
			return;
		}

		$statement = $this->Database()->LimitStatement(':page_start', ':page_length');

		if($statement !== '')
		{
			$query.= ' ' . $statement;

			$bindings['page_start' ] = $this->PageStart();
			$bindings['page_length'] = $this->PageLength();
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the query
	//------------------------------------------------------------------------------------------------------------------
	public function Query(array &$bindings, string $indent = '') : string
	{
		$query    = '';
		$bindings = array();

		// Inits the bindings counter
		$this->ClearBindingsCounter();

		// Adds statements
		$this->FillSelect( $query, $bindings, $indent);
		$this->FillTop(    $query, $bindings, $indent);
		$this->FillFields( $query, $bindings, $indent . $this->Tab());
		$this->FillFrom(   $query, $bindings, $indent);
		$this->FillWhere(  $query, $bindings, $indent);
		$this->FillGroupBy($query, $bindings, $indent);
		$this->FillHaving( $query, $bindings, $indent);
		$this->FillOrderBy($query, $bindings, $indent);
		$this->FillLimit(  $query, $bindings, $indent);

		// Result
		return $query;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Raw runs the query
	//------------------------------------------------------------------------------------------------------------------
	protected function RawRun(string $query, array $bindings) : string
	{
		// If index fields are renamed in the query output through "Fields" collection :
		// The new name must be used instead
		$indexes = $this->Indexes();

		foreach($this->Fields() as $k => $v)
		{
			if(isset($indexes[$v]))
			{
				// A renamed field has not the shape 'key:', 'key&', 'key@'
				$suffix = substr($k, -1);

				if(($suffix !== ':') && ($suffix !== '&') && ($suffix !== '@'))
				{
					$indexes[$v] = $k;
				}
			}
		}

		// Transformation is processed in two steps, because the indexes order must be maintained
		$renamedIndexes = array();

		foreach($indexes as $v)
		{
			$renamedIndexes[$v] = $v;
		}

		// Results
		return $this->Database()->Run($query, $bindings, $renamedIndexes, $this->IsHuge());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a COUNT query based on current SELECT
	//------------------------------------------------------------------------------------------------------------------
	public function Count() : int
	{
		// Stores fields
		$fields = $this->Fields();

		// Sets fields to a single COUNT field
		$this->ClearFields();
		$this->AddField('count@', 'COUNT(*)');

		// Runs query
		$res = $this->Run();

		// If query succeeded :
		// Gets count
		$count = 0;

		if(is_array($res))
		{
			foreach($res as $v)
			{
				$count = $v['count'];
				break;
			}
		}

		// Restores fields
		$this->ClearFields();
		foreach($fields as $k => $v)
		{
			$this->AddField($k, $v);
		}

		// Result
		return $count;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a TOTAL COUNT query based on current SELECT
	//------------------------------------------------------------------------------------------------------------------
	public function TotalCount() : int
	{
		// Stores page
		$pageStart  = $this->PageStart();
		$pageLength = $this->PageLength();

		// Counts without any page
		$this->ClearPage();

		$count = $this->Count();

		// Restores page
		$this->SetPageStart( $pageStart );
		$this->SetPageLength($pageLength);

		// Result
		return $count;
	}
}
