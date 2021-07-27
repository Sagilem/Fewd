<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\AModule;
use Fewd\Core\TCore;
use Fewd\Tracer\TTracer;
use PDO;


class TData extends AModule
{
	// Error constants
	public const ERROR_CONNECT       = 'Unable to connect to "{{NAME}}" database.';
	public const ERROR_NOT_CONNECTED = 'Not connected to database "{{NAME}}".';
	public const ERROR_QUERY_TYPE    = 'Unknown query type.';
	public const ERROR_QUERY         = 'Error [{{CODE}}] : {{MESSAGE}}.';

	// Query types constants
	public const QUERY_SELECT        = 'SELECT';
	public const QUERY_INSERT        = 'INSERT';
	public const QUERY_UPDATE        = 'UPDATE';
	public const QUERY_DELETE        = 'DELETE';
	public const QUERY_CREATE        = 'CREATE';
	public const QUERY_DROP          = 'DROP';
	public const QUERY_TRUNCATE      = 'TRUNCATE';

	// Datatype constants
	public const DATATYPE_CODE       = 'CODE';
	public const DATATYPE_FLAG       = 'FLAG';
	public const DATATYPE_KIND       = 'KIND';
	public const DATATYPE_NUMBER     = 'NUMBER';
	public const DATATYPE_FLOAT      = 'FLOAT';
	public const DATATYPE_TEXT       = 'TEXT';
	public const DATATYPE_MEMO       = 'MEMO';
	public const DATATYPE_DATETIME   = 'DATETIME';
	public const DATATYPE_MICROTIME  = 'MICROTIME';
	public const DATATYPE_SORT       = 'SORT';

	// Join type constants
	public const JOINTYPE_LEFT       = 'LEFT';
	public const JOINTYPE_INNER      = 'INNER';

	// Aggregation constants
	public const AGGREGATION_SUM     = 'SUM';
	public const AGGREGATION_AVERAGE = 'AVG';
	public const AGGREGATION_MINIMUM = 'MIN';
	public const AGGREGATION_MAXIMUM = 'MAX';
	public const AGGREGATION_COUNT   = 'COUNT';

	// Tracer
	private $_Tracer;
	public final function Tracer() : TTracer { return $this->_Tracer; }

	// Databases
	private $_Databases;
	public final function Databases() : array                            { return $this->_Databases;                }
	public final function Database(   string $name) : ADatabase          { return $this->_Databases[$name] ?? null; }
	public final function HasDatabase(string $name) : bool               { return isset($this->_Databases[$name]);  }
	protected    function AddDatabase(string $name, ADatabase $database) { $this->_Databases[$name] = $database;    }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(TCore $core, TTracer $tracer)
	{
		parent::__construct($core);

		$this->_Tracer = $tracer;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a string is a datatype
	//------------------------------------------------------------------------------------------------------------------
	public function IsDatatype(string $datatype) : bool
	{
		return (($datatype === self::DATATYPE_CODE       ) ||
		        ($datatype === self::DATATYPE_FLAG       ) ||
		        ($datatype === self::DATATYPE_KIND       ) ||
		        ($datatype === self::DATATYPE_NUMBER     ) ||
		        ($datatype === self::DATATYPE_FLOAT      ) ||
		        ($datatype === self::DATATYPE_TEXT       ) ||
		        ($datatype === self::DATATYPE_MEMO       ) ||
		        ($datatype === self::DATATYPE_DATETIME   ) ||
		        ($datatype === self::DATATYPE_MICROTIME  ) ||
		        ($datatype === self::DATATYPE_SORT       ));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given string is a join type
	//------------------------------------------------------------------------------------------------------------------
	public function IsJointype(string $jointype) : bool
	{
		return (($jointype === self::JOINTYPE_INNER) ||
		        ($jointype === self::JOINTYPE_LEFT ));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a string is an aggregation function
	//------------------------------------------------------------------------------------------------------------------
	public function IsAggregation(string $aggregation) : bool
	{
		return (($aggregation === self::AGGREGATION_SUM    ) ||
		        ($aggregation === self::AGGREGATION_AVERAGE) ||
		        ($aggregation === self::AGGREGATION_MINIMUM) ||
		        ($aggregation === self::AGGREGATION_MAXIMUM) ||
		        ($aggregation === self::AGGREGATION_COUNT  ));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Formats a datatype
	//------------------------------------------------------------------------------------------------------------------
	public function Datatype(string $datatype) : string
	{
		if($this->IsDatatype($datatype))
		{
			return $datatype;
		}

		return self::DATATYPE_TEXT;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Formats an aggregation
	//------------------------------------------------------------------------------------------------------------------
	public function Aggregation(string $aggregation) : string
	{
		if($this->IsAggregation($aggregation))
		{
			return $aggregation;
		}

		return self::AGGREGATION_SUM;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Formats a jointype
	//------------------------------------------------------------------------------------------------------------------
	public function Jointype(string $jointype) : string
	{
		if($this->IsJointype($jointype))
		{
			return $jointype;
		}

		return self::JOINTYPE_INNER;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Special fields
	//------------------------------------------------------------------------------------------------------------------
	public function FieldSort()        : string { return 'sort';         }
	public function FieldCreatedBy()   : string { return 'created_by';   }
	public function FieldCreatedWhen() : string { return 'created_when'; }
	public function FieldUpdatedBy()   : string { return 'updated_by';   }
	public function FieldUpdatedWhen() : string { return 'updated_when'; }


	//------------------------------------------------------------------------------------------------------------------
	// Converts a value for a given datatype
	//------------------------------------------------------------------------------------------------------------------
	public function Convert(mixed $value, string $datatype) : mixed
	{
		if($datatype === TData::DATATYPE_NUMBER)
		{
			return intval($value);
		}

		if($datatype === TData::DATATYPE_FLOAT)
		{
			return floatval($value);
		}

		if($datatype === TData::DATATYPE_FLAG)
		{
			if($value)
			{
				return 'X';
			}

			return '-';
		}

		return strval($value);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Alerts about an error encoutered in a query
	//------------------------------------------------------------------------------------------------------------------
	public function Alert(string $errorCode, string $errorMessage, string $query = '', array $bindings = array())
	{
		$error = self::ERROR_QUERY;
		$error = str_replace('{{CODE}}'   , $errorCode   , $error);
		$error = str_replace('{{MESSAGE}}', $errorMessage, $error);

		$this->Tracer()->Warn($error, 'DATA ERROR');

		if($query !== '')
		{
			$this->Tracer()->Warn($query, 'QUERY');
		}

		if(!empty($bindings))
		{
			$this->Tracer()->Warn($bindings, 'BINDINGS');
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Raw maker : PDO
	//------------------------------------------------------------------------------------------------------------------
	public function RawMakePDO(string $dsn, string $user, string $pass) : PDO
	{
		return new PDO($dsn, $user, $pass);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Mysql database
	//------------------------------------------------------------------------------------------------------------------
	public function MakeDatabaseMysql(
		string $name,
		string $host,
		string $port,
		string $user,
		string $pass) : TDatabaseMysql
	{
		$res = new TDatabaseMysql($this->Core(), $this, $name, $host, $port, $user, $pass);
		$res->Init();

		$this->AddDatabase($res->Name(), $res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Sqlite database
	//------------------------------------------------------------------------------------------------------------------
	public function MakeDatabaseSqlite(string $name, string $host) : TDatabaseSqlite
	{
		$res = new TDatabaseSqlite($this->Core(), $this, $name, $host);
		$res->Init();

		$this->AddDatabase($res->Name(), $res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Datatable
	//------------------------------------------------------------------------------------------------------------------
	public function MakeDatatable(
		ADatabase $database,
		string    $name,
		array     $fields    = array(),
		bool      $isSorted  = false,
		bool      $isManaged = false) : TDatatable
	{
		$res = new TDatatable($this->Core(), $this, $database, $name, $fields, $isSorted, $isManaged);
		$res->Init();

		$database->AddDatatable($this->Name(), $res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Select
	//------------------------------------------------------------------------------------------------------------------
	public function MakeSelect(
		TDatatable $datatable,
		string     $alias      = '',
		array      $fields     = array(),
		array      $conditions = array(),
		array      $groups     = array(),
		array      $havings    = array(),
		array      $orders     = array(),
		int        $pageStart  = 0,
		int        $pageLength = 0,
		bool       $isDistinct = false,
		bool       $isHuge     = false) : TSelect
	{
		$res = new TSelect(
			$this->Core(),
			$this,
			$datatable,
			$alias,
			$fields,
			$conditions,
			$groups,
			$havings,
			$orders,
			$pageStart,
			$pageLength,
			$isDistinct,
			$isHuge);

		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : One
	//------------------------------------------------------------------------------------------------------------------
	public function MakeOne(
		TDatatable $datatable,
		string     $alias      = '',
		array      $fields     = array(),
		array      $conditions = array()) : TOne
	{
		$res = new TOne($this->Core(), $this, $datatable, $alias, $fields, $conditions);
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Insert
	//------------------------------------------------------------------------------------------------------------------
	public function MakeInsert(
		TDatatable $datatable,
		array      $records = array(),
		bool       $isBulk  = true   ) : TInsert
	{
		$res = new TInsert($this->Core(), $this, $datatable, $records, $isBulk);
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Update
	//------------------------------------------------------------------------------------------------------------------
	public function MakeUpdate(
		TDatatable $datatable,
		array      $values     = array(),
		array      $conditions = array()) : TUpdate
	{
		$res = new TUpdate($this->Core(), $this, $datatable, $values, $conditions);
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Delete
	//------------------------------------------------------------------------------------------------------------------
	public function MakeDelete(
		TDatatable $datatable,
		array      $conditions = array()) : TDelete
	{
		$res = new TDelete($this->Core(), $this, $datatable, $conditions);
		$res->Init();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : Join
	//------------------------------------------------------------------------------------------------------------------
	public function MakeJoin(
		TDatatable|TSelect $source,
		array              $links,
		array              $fields     = array(),
		array              $conditions = array(),
		string             $jointype   = '',
		string             $alias      = '') : TJoin
	{
		$res = new TJoin($this->Core(), $this, $source, $links, $fields, $conditions, $jointype, $alias);
		$res->Init();

		return $res;
	}
}