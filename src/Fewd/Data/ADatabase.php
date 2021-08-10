<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Data;


use Fewd\Core\AThing;
use Fewd\Core\TCore;
use PDO;
use PDOException;
use PDOStatement;


abstract class ADatabase extends AThing
{
	// Data
	private $_Data;
	public final function Data() : TData { return $this->_Data; }

	// Name
	private $_Name;
	public final function Name() : string { return $this->_Name; }

	// Host
	private $_Host;
	public final function Host() : string { return $this->_Host; }

	// Port
	private $_Port;
	public final function Port() : string { return $this->_Port; }

	// User
	private $_User;
	public final function User() : string { return $this->_User; }

	// Pass
	private $_Pass;
	public final function Pass() : string { return $this->_Pass; }

	// Driver
	private $_Driver;
	public final function Driver() : string { return $this->_Driver; }

	// The user id to use to manipulate some data
	private $_By;
	public final function By() : string        { return $this->_By;   }
	public       function SetBy(string $value) { $this->_By = $value; }

	// The datetime to use during data manipulation
	private $_When;
	public final function When() : string { return $this->_When; }

	// The current microtime to use during data manipulation
	private $_Microtime;
	public final function Microtime() : string { return $this->_Microtime; }

	// Datatables
	private $_Datatables;
	public final function Datatables()               : array            { return $this->_Datatables;                }
	public final function Datatable(   string $name) : TDatatable       { return $this->_Datatables[$name] ?? null; }
	public final function HasDatatable(string $name) : bool             { return isset($this->_Datatables[$name]);  }
	public       function AddDatatable(string $name, TDatatable $table) { $this->_Datatables[$name] = $table;       }

	// Handle
	private $_Handle;
	protected final function Handle() : ?PDO { return $this->_Handle; }

	// Handle to last executed query
	private $_LastQueryHandle;
	public final function LastQueryHandle() : PDOStatement { return $this->_LastQueryHandle; }

	// Last executed query
	private $_LastQuery;
	public final function LastQuery() : string { return $this->_LastQuery; }

	// Type of last executed query
	private $_LastQueryType;
	public final function LastQueryType() : string { return $this->_LastQueryType; }

	// Bindings used with last executed query
	private $_LastBindings;
	public final function LastBindings() : array { return $this->_LastBindings; }

	// Array of rows found during last executed select
	private $_LastResults;
	public final function LastResults() : array { return $this->_LastResults; }

	// Last inserted id
	private $_LastInsertId;
	public final function LastInsertId() : string { return $this->_LastInsertId; }

	// Number of affected rows during last executed query
	private $_LastCount;
	public final function LastCount() : int { return $this->_LastCount; }

	// Error code raised during last executed query
	private $_LastErrorCode;
	public final function LastErrorCode() : string { return $this->_LastErrorCode; }

	// Error message raised during last executed query
	private $_LastErrorMessage;
	public final function LastErrorMessage() : string { return $this->_LastErrorMessage; }

	// Indicates if a transaction was started
	private $_IsTransaction;
	public final function IsTransaction() : bool { return $this->_IsTransaction; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore      $core,
		TData      $data,
		string     $name,
		string     $host,
		string     $port,
		string     $user,
		string     $pass)
	{
		parent::__construct($core);

		$this->_Data = $data;
		$this->_Name = $name;
		$this->_Host = $host;
		$this->_Port = $port;
		$this->_User = $user;
		$this->_Pass = $pass;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Name       = $this->DefineName();
		$this->_Host       = $this->DefineHost();
		$this->_Port       = $this->DefinePort();
		$this->_User       = $this->DefineUser();
		$this->_Pass       = $this->DefinePass();
		$this->_Driver     = $this->DefineDriver();
		$this->_By         = $this->DefineBy();
		$this->_When       = $this->DefineWhen();
		$this->_Microtime  = $this->DefineMicrotime();
		$this->_Datatables = $this->DefineDatatables();

		$this->_Handle        = null;
		$this->_IsTransaction = false;

		$this->Clear();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Name
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineName() : string
	{
		return $this->Name();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Host
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineHost() : string
	{
		return $this->Host();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Port
	//------------------------------------------------------------------------------------------------------------------
	protected function DefinePort() : string
	{
		return $this->Port();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : User
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineUser() : string
	{
		return $this->User();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Pass
	//------------------------------------------------------------------------------------------------------------------
	protected function DefinePass() : string
	{
		return $this->Pass();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Driver
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDriver() : string
	{
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : By
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineBy() : string
	{
		return '-';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : When
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineWhen() : string
	{
		return $this->Core()->Now();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Microtime
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMicrotime() : string
	{
		return $this->Core()->MicroNow();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Datatables
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDatatables() : array
	{
		return array();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Clears last query
	//------------------------------------------------------------------------------------------------------------------
	public function Clear()
	{
		$this->_LastQueryHandle  = null;
		$this->_LastQuery        = '';
		$this->_LastQueryType    = '';
		$this->_LastBindings     = array();
		$this->_LastResults      = array();
		$this->_LastInsertId     = '';
		$this->_LastCount        = 0;
		$this->_LastErrorCode    = '';
		$this->_LastErrorMessage = '';
	}




	//==================================================================================================================
	//
	// DATABASE CONNECTION AND TRANSACTIONS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Gets the connection DSN
	//------------------------------------------------------------------------------------------------------------------
	protected function Dsn() : string
	{
		$res = $this->Driver() . ':host=' . $this->Host();

		if($this->Port() !== '')
		{
			$res.= ';port=' . $this->Port();
		}

		$res.= ';dbname=' . $this->Name();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if database is already connected
	//------------------------------------------------------------------------------------------------------------------
	public function IsConnected() : bool
	{
		return ($this->Handle() !== null);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Connects database (returns a potential error message)
	//------------------------------------------------------------------------------------------------------------------
	public function Connect() : string
	{
		// If already connected :
		// Does nothing
		if($this->Handle() !== null)
		{
			return '';
		}

		// Tries to connect
		$this->Clear();

		try
		{
			$this->_Handle = $this->Data()->RawMakePDO($this->Dsn(), $this->User(), $this->Pass());

			if($this->Handle() === null)
			{
				return $this->BuildError('CONNECT', TData::ERROR_CONNECT);
			}
		}
		catch(PDOException $exception)
		{
			return $exception->getMessage();
		}

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Starts a transaction
	//------------------------------------------------------------------------------------------------------------------
	public function Begin()
	{
		if($this->IsConnected())
		{
			$this->_IsTransaction = true;
			$this->Handle()->beginTransaction();
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Commits a transaction
	//------------------------------------------------------------------------------------------------------------------
	public function Commit()
	{
		if($this->IsConnected())
		{
			$this->_IsTransaction = false;
			$this->Handle()->commit();
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Rollbacks a transaction
	//------------------------------------------------------------------------------------------------------------------
	public function Rollback()
	{
		if($this->IsConnected())
		{
			$this->_IsTransaction = false;
			$this->Handle()->rollback();
		}
	}




	//==================================================================================================================
	//
	// SQL STATEMENTS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Quotes an element (table name, field name...)
	//------------------------------------------------------------------------------------------------------------------
	public function Quote(string $name) : string
	{
		return '`' . $name . '`';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Quotes a string value
	//------------------------------------------------------------------------------------------------------------------
	public function StringQuote(bool|int|float|string $value) : string
	{
		if(is_bool($value))
		{
			if($value)
			{
				return '\'X\'';
			}

			return '\'-\'';
		}

		if(is_int($value) || is_float($value))
		{
			return strval($value);
		}

		$value = str_replace('\'', '\\\'', $value);

		return '\'' . $value . '\'';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Wildcard character
	//------------------------------------------------------------------------------------------------------------------
	public function WildcardStatement() : string
	{
		return '%';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Auto-increment statement
	//------------------------------------------------------------------------------------------------------------------
	public function AutoIncrementStatement() : string
	{
		return 'AUTO_INCREMENT';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Datatype statement
	//------------------------------------------------------------------------------------------------------------------
	public function DatatypeStatement(string $datatype) : string
	{
		$datatype = $this->Data()->Datatype($datatype);

		switch($datatype)
		{
			case TData::DATATYPE_ID         : return 'INT';
			case TData::DATATYPE_CODE       : return 'VARCHAR(50)';
			case TData::DATATYPE_FLAG       : return 'CHAR(1)';
			case TData::DATATYPE_KIND       : return 'CHAR(4)';
			case TData::DATATYPE_NUMBER     : return 'BIGINT';
			case TData::DATATYPE_FLOAT      : return 'DOUBLE';
			case TData::DATATYPE_TEXT       : return 'VARCHAR(255)';
			case TData::DATATYPE_MEMO       : return 'LONGTEXT';
			case TData::DATATYPE_DATETIME   : return 'CHAR(14)';
			case TData::DATATYPE_MICROTIME  : return 'CHAR(22)';
			case TData::DATATYPE_SORT       : return 'CHAR(22)';
		}

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// TOP statement in a PDO SELECT query (intended for MS-SQL)
	//------------------------------------------------------------------------------------------------------------------
	public function TopStatement(string $start, string $length) : string
	{
		$this->Nop($start );
		$this->Nop($length);

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// AGGREGATION statement in a PDO SELECT query (based on a TData::AGGREGATION_* constant)
	//------------------------------------------------------------------------------------------------------------------
	public function AggregationStatement(string $aggregation, string $value) : string
	{
		$aggregation = $this->Data()->Aggregation($aggregation);

		switch($aggregation)
		{
			case TData::AGGREGATION_SUM     : return 'SUM('   . $value . ')';
			case TData::AGGREGATION_AVERAGE : return 'AVG('   . $value . ')';
			case TData::AGGREGATION_MINIMUM : return 'MIN('   . $value . ')';
			case TData::AGGREGATION_MAXIMUM : return 'MAX('   . $value . ')';
			case TData::AGGREGATION_COUNT   : return 'COUNT(' . $value . ')';
		}

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// JOIN statement in a PDO SELECT query (based on a TData::JOINTYPE_* constant)
	//------------------------------------------------------------------------------------------------------------------
	public function JoinStatement(string $jointype) : string
	{
		$jointype = $this->Data()->Jointype($jointype);

		switch($jointype)
		{
			case TData::JOINTYPE_INNER : return 'INNER JOIN';
			case TData::JOINTYPE_LEFT  : return 'LEFT JOIN';
		}

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// LIMIT statement in a PDO SELECT query
	//------------------------------------------------------------------------------------------------------------------
	public function LimitStatement(string $start, string $length) : string
	{
		return 'LIMIT ' . $start . ', ' . $length;
	}


	//------------------------------------------------------------------------------------------------------------------
	// DELETE statement
	//------------------------------------------------------------------------------------------------------------------
	public function DeleteStatement(string $tableName) : string
	{
		return 'DELETE FROM ' . $this->Quote($tableName);
	}


	//------------------------------------------------------------------------------------------------------------------
	// TRUNCATE TABLE statement
	//------------------------------------------------------------------------------------------------------------------
	public function TruncateTableStatement(string $tableName) : string
	{
		return 'TRUNCATE TABLE ' . $this->Quote($tableName);
	}


	//------------------------------------------------------------------------------------------------------------------
	// ENGINE statement
	//------------------------------------------------------------------------------------------------------------------
	public function EngineStatement($fulltexts = array()) : string
	{
		$this->Nop($fulltexts);

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// CREATE TABLE statement
	//------------------------------------------------------------------------------------------------------------------
	public function CreateTableStatement(
		string $tableName,
		array  $keys,
		array  $fields,
		array  $fulltexts = array()) : string
	{
		// Table name
		$res = 'CREATE TABLE IF NOT EXISTS ' . $this->Quote($tableName) . ' (';

		// Adds each field
		$sep = '';
		foreach($fields as $k => $v)
		{
			$res.= $sep . "\n\t" . $this->Quote($k) . ' ' . $this->DatatypeStatement($v) . ' NOT NULL';

			if(($v === TData::DATATYPE_ID) && isset($keys[$k]) && (count($keys) === 1))
			{
				$res.= ' ' . $this->AutoIncrementStatement();
			}

			$sep = ',';
		}

		// Adds fulltext indexes
		foreach($fulltexts as $k => $v)
		{
			$res.= $sep . "\n" . 'FULLTEXT ';
			$res.= $this->Quote($tableName . '__fulltext__' . $k);
			$res.= ' (' . $this->Quote($k) . ')';

			$sep = ',';
		}

		// Adds primary key columns
		if(!empty($keys))
		{
			$res.= $sep . "\n" . 'PRIMARY KEY (';
			$sep = '';

			foreach($keys as $k => $v)
			{
				$res.= $sep . "\n\t" . $this->Quote($k);
				$sep = ',';
			}

			$res.= "\n" . '))';
		}
		else
		{
			$res.= "\n" . ')';
		}

		// Adds the engine statement
		$res.= $this->EngineStatement($fulltexts);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// DROP TABLE statement
	//------------------------------------------------------------------------------------------------------------------
	public function DropTableStatement(string $tableName) : string
	{
		return 'DROP TABLE IF EXISTS ' . $this->Quote($tableName);
	}


	//------------------------------------------------------------------------------------------------------------------
	// SUBSTRING statement
	//------------------------------------------------------------------------------------------------------------------
	public function SubstringStatement(string $string, int $start, int $length) : string
	{
		return 'SUBSTR(' . $string . ', ' . $start . ', ' . $length . ')';
	}


	//------------------------------------------------------------------------------------------------------------------
	// CONCAT statement
	//------------------------------------------------------------------------------------------------------------------
	public function ConcatStatement($strings) : string
	{
		return 'CONCAT(' . implode(', ' . func_get_args()) . ')';
	}


	//------------------------------------------------------------------------------------------------------------------
	// IN statement
	//------------------------------------------------------------------------------------------------------------------
	public function InStatement(string $field, array $values) : string
	{
		$tb = array();

		foreach($values as $v)
		{
			$tb[] = $this->Quote($field) . ' = ' . $this->StringQuote($v);
		}

		if(empty($tb))
		{
			return '';
		}

		$res = implode(' OR ', $tb);

		return '(' . $res . ')';
	}


	//------------------------------------------------------------------------------------------------------------------
	// IN statement (with bindings generation)
	//------------------------------------------------------------------------------------------------------------------
	public function InWithBindingsStatement(
		string $field,
		string $label,
		array  $values,
		array  &$bindings) : string
	{
		$tb = array();

		foreach($values as $k => $v)
		{
			$tb[] = $this->Quote($field) . ' = :' . $label . '__' . $k;

			$bindings[$label . '__' . $k] = $v;
		}

		if(empty($tb))
		{
			return '';
		}

		$res = implode(' OR ', $tb);

		return '(' . $res . ')';
	}




	//==================================================================================================================
	//
	// QUERY EXECUTION
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Gets the wildcards (':xyz') from a prepared query
	//------------------------------------------------------------------------------------------------------------------
	public function QueryWildcards(string $query) : array
	{
		$res       = array();
		$wildcards = array();

		preg_match_all('`[:]([a-zA-Z0-9_]*)`', $query, $wildcards);

		if(isset($wildcards[1]))
		{
			foreach($wildcards[1] as $v)
			{
				$res[$v] = $v;
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the type of a query
	//------------------------------------------------------------------------------------------------------------------
	public function QueryType(string $query) : string
	{
		$query = $this->Core()->ToUpper(substr(trim($query), 0, 12));

		$begin = substr($query, 0, 6);

		if(($begin === TData::QUERY_SELECT) || ($begin === TData::QUERY_INSERT) ||
		   ($begin === TData::QUERY_UPDATE) || ($begin === TData::QUERY_DELETE))
		{
			return $begin;
		}

		if($query === 'CREATE TABLE')
		{
			return TData::QUERY_CREATE;
		}

		if(substr($query, 0, 10) === 'DROP TABLE')
		{
			return TData::QUERY_DROP;
		}

		if(substr($query, 0, 8) === 'TRUNCATE')
		{
			return TData::QUERY_TRUNCATE;
		}

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Sets error on last executed query
	//------------------------------------------------------------------------------------------------------------------
	protected function BuildError(
		string $errorCode,
		string $errorMessage,
		string $query       = '',
		array  $bindings    = array()) : string
	{
		// If in transaction mode :
		// Rollbacks everything
		if($this->IsTransaction())
		{
			$this->Rollback();
		}

		// Generates error message
		$res = str_replace('{{NAME}}', $this->Name(), $errorMessage);

		$this->_LastErrorCode    = $errorCode;
		$this->_LastErrorMessage = $res;
		$this->_LastQueryHandle  = null;

		// Screen alert
		$this->Data()->Alert($errorCode, $res, $query, $bindings);

		// Returns the error message
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Builds the index value for a given row, according to an array of indexes
	//------------------------------------------------------------------------------------------------------------------
	protected function IndexValue(array $row, array|string $indexes) : string
	{
		// Simple index
		if(is_string($indexes))
		{
			if(isset($row[$indexes]))
			{
				return strval($row[$indexes]);
			}

			return '';
		}

		// Complex index
		$res = '';
		$sep = '';

		foreach($indexes as $v)
		{
			if(!isset($row[$v]))
			{
				return '';
			}

			$res.= $sep . strval($row[$v]);
			$sep = '--';
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a SELECT query (returns a potential error message)
	//------------------------------------------------------------------------------------------------------------------
	protected function RunSelect(
		string        $query,
		?PDOStatement $preparedQuery,
		array         $bindings,
		array|string  $indexes,
		bool          $isHuge) : string
	{
		// If query is not prepared :
		// Runs it directly
		if($preparedQuery === null)
		{
			$handle = $this->Handle()->query($query);
		}

		// Otherwise :
		// Binds values to prepared query, then executes it
		else
		{
			$wildcards = $this->QueryWildcards($query);

			foreach($bindings as $k => $v)
			{
				// PDO query will fail if a binding is declared that is not a wildcard in the query.
				// Such a binding is therefore ignored
				if(!isset($wildcards[$k]))
				{
					continue;
				}

				// Binds current value
				if(is_string($v))
				{
					$preparedQuery->bindValue($k, $v, PDO::PARAM_STR);
				}
				elseif(is_numeric($v))
				{
					$preparedQuery->bindValue($k, $v, PDO::PARAM_INT);
				}
			}

			$handle = $preparedQuery;

			try
			{
				if(!$preparedQuery->execute())
				{
					$handle = false;
				}
			}
			catch(PDOException $e)
			{
				return $this->BuildError($e->errorInfo[0], $e->errorInfo[2], $query, $bindings);
			}
		}

		// If query came to an error :
		// Stops here
		if($handle === false)
		{
			$info = $this->Handle()->errorInfo();

			return $this->BuildError($info[0], $info[2], $query, $bindings);
		}

		$this->_LastQueryHandle = $handle;

		// If query is "huge" :
		// Results are not retrieved, it will be done by manuel fetch
		if($isHuge)
		{
			return '';
		}

		// If no index :
		// Directly generates the array of results in one step
		if(empty($indexes))
		{
			$this->_LastResults = $handle->fetchAll(PDO::FETCH_ASSOC);
		}

		// Otherwise :
		// Fetches each row
		else
		{
			$row = $handle->fetch(PDO::FETCH_ASSOC);
			while($row)
			{
				$indexValue = $this->IndexValue($row, $indexes);

				$this->_LastResults[$indexValue] = $row;

				$row = $handle->fetch(PDO::FETCH_ASSOC);
			}
		}

		// Counts records
		$this->_LastCount = count($this->LastResults());

		// No problem encountered
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Executes a query other than SELECT
	//------------------------------------------------------------------------------------------------------------------
	protected function RunExecute(
		string        $query,
		?PDOStatement $preparedQuery,
		array         $bindings) : string
	{
		// If query is not prepared :
		// Runs it directly
		if($preparedQuery === null)
		{
			$number = $this->Handle()->exec($query);
		}

		// Otherwise :
		// Binds values to prepared query, then executes it
		else
		{
			$wildcards = $this->QueryWildcards($query);

			foreach($bindings as $k => $v)
			{
				// PDO query will fail if a binding is declared that is not a wildcard in the query.
				// Such a binding is therefore ignored
				if(!isset($wildcards[$k]))
				{
					continue;
				}

				// Binds current value
				if(is_string($v))
				{
					$preparedQuery->bindValue($k, $v, PDO::PARAM_STR);
				}
				elseif(is_numeric($v))
				{
					$preparedQuery->bindValue($k, $v, PDO::PARAM_INT);
				}
			}

			try
			{
				if($preparedQuery->execute())
				{
					$number = $preparedQuery->rowCount();
				}
				else
				{
					$number = false;
				}
			}
			catch(PDOException $e)
			{
				return $this->BuildError($e->errorInfo[0], $e->errorInfo[2], $query, $bindings);
			}
		}

		// If query is an INSERT :
		// Gets the last inserted id
		$id = $this->Handle()->lastInsertId();
		if($id !== false)
		{
			$this->_LastInsertId = $id;
		}

		// If query came to an error :
		// Stops here
		if($number === false)
		{
			$info = $this->Handle()->errorInfo();

			return $this->BuildError($info[0], $info[2], $query, $bindings);
		}

		// Returns the number of affected rows
		$this->_LastCount = $number;

		// No problem encountered
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a query (returns a potential error message)
	//------------------------------------------------------------------------------------------------------------------
	public function Run(
		string       $query,
		array        $bindings = array(),
		array|string $indexes  = '',
		bool         $isHuge   = false) : string
	{
		// If database is not connected :
		// Error
		$this->Connect();

		if(!$this->IsConnected())
		{
			return $this->BuildError('NOT_CONNECTED', TData::ERROR_NOT_CONNECTED);
		}

		// Clears last query
		$this->Clear();
		$this->_LastQuery     = $query;
		$this->_LastQueryType = $this->QueryType($query);
		$this->_LastBindings  = $bindings;

		// Prepares query
		if(empty($bindings))
		{
			$preparedQuery = null;
		}
		else
		{
			$preparedQuery = $this->Handle()->prepare($query);
		}

		// Runs query
		if($this->LastQueryType() === TData::QUERY_SELECT)
		{
			return $this->RunSelect($query, $preparedQuery, $bindings, $indexes, $isHuge);
		}
		else
		{
			return $this->RunExecute($query, $preparedQuery, $bindings);
		}

		// Result
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a SQL query to get one record only
	//------------------------------------------------------------------------------------------------------------------
	public function RunOne(string $query, array $bindings) : string
	{
		$query.= ' LIMIT 0, 1';

		return $this->Run($query, $bindings);
	}
}
