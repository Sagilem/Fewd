<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\TCore;


abstract class AConditionSql extends ASql
{
	// Conditions
	private $_Conditions;
	public final function Conditions()                : array              { return $this->_Conditions;              }
	public final function Condition(      string $id) : string|array       { return $this->_Conditions[$id] ?? null; }
	public final function HasCondition(   string $id) : bool               { return isset($this->_Conditions[$id]);  }
	public       function AddCondition(   string $id, string|array $value) { $this->_Conditions[$id] = $value;       }
	public       function RemoveCondition(string $id)                      { unset($this->_Conditions[$id]);         }
	public       function ClearConditions()                                { $this->_Conditions = array();           }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		TDatatable $datatable,
		string     $alias,
		array      $conditions)
	{
		parent::__construct($core, $data, $datatable, $alias);

		$this->_Conditions = $conditions;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		$conditions = $this->Conditions();

		parent::Init();

		foreach($conditions as $k => $v)
		{
			$this->AddCondition($k, $v);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Clear
	//------------------------------------------------------------------------------------------------------------------
	public function Clear()
	{
		parent::Clear();

		$this->ClearConditions();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a CONDITION statement
	//------------------------------------------------------------------------------------------------------------------
	// Possible cases :
	//
	// Without operator : "=" by default
	//
	// 'key'    => $value              : alias.`key` = :condition_1260    + bindings['condition_1260'] = $value
	// 'key&'   => $value              : alias.`key` = $value
	// '@key'   => $value              : key = :condition_1260            + bindings['condition_1260'] = $value
	// '@key&'  => $value              : key = $value
	//
	// With a single value operator :
	//
	// 'key>'   => $value              : alias.`key` > :condition_1260    + bindings['condition_1260'] = $value
	// 'key&>'  => $value              : alias.`key` > $value
	// '@key>'  => $value              : key > :condition_1260            + bindings['condition_1260'] = $value
	// '@key&>' => $value              : key > $value
	//
	// Other possible operators :
	//
	// = or ==    : equality
	// != or <>   : difference
	// > >= < <=  : comparison
	// ~          : LIKE
	// !~         : NOT LIKE
	// ?          : IS NULL
	// !?         : IS NOT NULL
	//
	// With a multiple values operator + an array of values :
	//
	// 'key{}'  => array($v1, $v2)     : alias.`key` IN (:condition_1261, :condition_1262)
	//                                   + bindings['condition_1261'] = $v1
	//                                   + bindings['condition_1262'] = $v2
	//
	// Other possible operators :
	//
	// {}         : IN          (with an array of values)
	// }{         : NOT IN      (with an array of values)
	// []         : BETWEEN     (with an array of two values)
	// ][         : NOT BETWEEN (with an array of two values)
	//------------------------------------------------------------------------------------------------------------------
	protected function FillCondition(string &$query, array &$bindings, string $alias, string $key, string|array $value)
	{
		// Gets operator from key
		$operator = substr($key, -2);

		if(($operator === '>=') ||
		   ($operator === '<=') ||
		   ($operator === '!=') ||
		   ($operator === '<>') ||
		   ($operator === '==') ||
		   ($operator === '{}') ||
		   ($operator === '}{') ||
		   ($operator === '[]') ||
		   ($operator === '][') ||
		   ($operator === '!~') ||
		   ($operator === '!?'))
		{
			$key = substr($key, 0, -2);
		}
		else
		{
			$operator = substr($key, -1);

			if(($operator === '=') ||
			   ($operator === '<') ||
			   ($operator === '>') ||
			   ($operator === '~') ||
			   ($operator === '?'))
			{
				$key = substr($key, 0, -1);
			}
			else
			{
				$operator = '=';
			}
		}

		// Detects NULL operator
		$isNullOperator = (($operator === '?') || ($operator === '!?'));

		// Transforms operator into an SQL operator
		if(    $operator === '==') { $operator = '=';           }
		elseif($operator === '!=') { $operator = '<>';          }
		elseif($operator === '{}') { $operator = 'IN';          }
		elseif($operator === '}{') { $operator = 'NOT IN';      }
		elseif($operator === '[]') { $operator = 'BETWEEN';     }
		elseif($operator === '][') { $operator = 'NOT BETWEEN'; }
		elseif($operator === '!~') { $operator = 'NOT LIKE';    }
		elseif($operator === '~' ) { $operator = 'LIKE';        }
		elseif($operator === '!?') { $operator = 'IS NOT NULL'; }
		elseif($operator === '?' ) { $operator = 'IS NULL';     }

		// Manages list operators (i.e. operators that need multiple values)
		$isListOperator = (($operator === 'IN'     ) || ($operator === 'NOT IN'     ) ||
		                   ($operator === 'BETWEEN') || ($operator === 'NOT BETWEEN'));

		if($isListOperator && !is_array($value))
		{
			if(($operator === 'IN'    ) || ($operator === 'BETWEEN'    )) { $operator = '=';  }
			if(($operator === 'NOT IN') || ($operator === 'NOT BETWEEN')) { $operator = '<>'; }
		}

		// Gets other format info from key
		if($isFixed = (substr($key, 0, 1) === '@'))
		{
			$key = substr($key, 1);
		}

		if($isDirect = (substr($key, -1) === '&'))
		{
			$key = substr($key, 0, -1);
		}

		// Determines the left part of condition (key and operator)
		if($isFixed)
		{
			$left = $key;
		}
		elseif($alias === '')
		{
			$left = $this->Database()->Quote($key);
		}
		else
		{
			$left = $alias . '.' . $this->Database()->Quote($key);
		}

		if($isNullOperator)
		{
			$query.= $left . ' ' . $operator;

			return;
		}
		else
		{
			$left.= ' ' . $operator . ' ';
		}

		// BETWEEN operators case
		if(($operator === 'BETWEEN') || ($operator === 'NOT BETWEEN'))
		{
			if(count($value) < 2)
			{
				return;
			}

			if($isDirect)
			{
				if(is_string($value[0])) { $value[0] = $this->Database()->StringQuote($value[0]); }
				if(is_string($value[1])) { $value[1] = $this->Database()->StringQuote($value[1]); }

				$query.= $left . $value[0] . ' AND ' . $value[1];
			}
			else
			{
				$counter1 = $this->BindingsCounter();
				$counter2 = $this->BindingsCounter();

				$query.= $left . ':condition_' . $counter1 . ' AND :condition_' . $counter2;
				$bindings['condition_' . $counter1] = $value[0];
				$bindings['condition_' . $counter2] = $value[1];
			}
		}

		// IN operators case
		elseif(($operator === 'IN') || ($operator === 'NOT IN'))
		{
			$query.= $left . '(';
			$sep   = '';

			foreach($value as $v)
			{
				if($isDirect)
				{
					$query.= $sep . (is_string($v) ? $this->Database()->StringQuote($v) : $v);
				}
				else
				{
					$counter = $this->BindingsCounter();

					$query.= $sep . ':condition_' . $counter;
					$bindings['condition_' . $counter] = $v;
				}

				$sep = ', ';
			}

			$query.= ')';
		}

		// Other operators, with a list of values
		elseif(is_array($value))
		{
			$query.= '(';
			$sep   = '';

			foreach($value as $v)
			{
				if($isDirect)
				{
					$query.= $sep . $left . (is_string($v) ? $this->Database()->StringQuote($v) : $v);
				}
				else
				{
					$counter = $this->BindingsCounter();

					$query.= $sep . $left . ':condition_' . $counter;
					$bindings['condition_' . $counter] = $v;
				}

				$sep = (($operator === '=') || ($operator === 'LIKE')) ? ' OR ' : ' AND ';
			}

			$query.= ')';
		}

		// Other operators, with a single value
		else
		{
			if($isDirect)
			{
				$query.= $left . (is_string($value) ? $this->Database()->StringQuote($value) : $value);
			}
			else
			{
				$counter = $this->BindingsCounter();

				$query.= $left . ':condition_' . $counter;
				$bindings['condition_' . $counter] = $value;
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Fills with a WHERE statement
	//------------------------------------------------------------------------------------------------------------------
	protected function FillWhere(string &$query, array &$bindings, string $indent)
	{
		// Adds each condition
		$sep = $this->Ret() . $indent . 'WHERE ';

		foreach($this->Conditions() as $k => $v)
		{
			$query.= $sep;

			$this->FillCondition($query, $bindings, $this->Alias(), $k, $v);

			$sep = $this->Ret() . $indent . 'AND   ';
		}
	}
}
