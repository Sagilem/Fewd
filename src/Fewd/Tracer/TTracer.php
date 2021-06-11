<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Tracer;


use Fewd\Core\TCore;
use Fewd\Core\AModule;
use Throwable;
use Exception;
use ReflectionClass;
use ReflectionProperty;


class TTracer extends AModule
{
	// Log
	private $_Log;
	public final function Log() : TLog { return $this->_Log; }

	// Log dirname
	private $_LogDirname;
	public final function LogDirname() : string { return $this->_LogDirname; }

	// Maximum depth
	private $_MaximumDepth;
	public final function MaximumDepth() : int { return $this->_MaximumDepth; }

	// Indicates if in debug mode
	private $_IsDebug = false;
	public final function IsDebug() : bool { return $this->_IsDebug; }

	// Indicates if output is on a log file
	private $_IsLog = false;
	public final function IsLog() : bool  { return $this->_IsLog;  }
	public       function LogOn()         { $this->_IsLog = true;  }
	public       function LogOff()        { $this->_IsLog = false; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(TCore $core, string $logDirname)
	{
		parent::__construct($core);

		$this->_LogDirname = $logDirname;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		// Defines
		$this->_Log        = $this->DefineLog();
		$this->_LogDirname = $this->DefineLogDirname();

		// Ensures that the log directory exists
		if(!is_dir($this->LogDirname()))
		{
			mkdir($this->LogDirname(), 0700, true);
		}

		// New error and exception handlers
		set_error_handler(    array($this, 'ErrorHandler'    ));
		set_exception_handler(array($this, 'ExceptionHandler'));

		// Sets maximum depth
		$this->SetMaximumDepth(10);

		// Debug is on at start
		$this->DebugOn();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Log
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineLog() : TLog
	{
		return $this->MakeLog('');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Log Dirname
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineLogDirname() : string
	{
		$res = $this->Core()->Join($this->LogDirname());

		if($res === '')
		{
			$res = 'Log';
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Activates debug mode
	//------------------------------------------------------------------------------------------------------------------
	public function DebugOn()
	{
		$this->_IsDebug = true;

		ini_set('display_errors', 1);
		error_reporting(-1);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Deactivates debug mode
	//------------------------------------------------------------------------------------------------------------------
	public function DebugOff()
	{
		$this->_IsDebug = false;

		ini_set('display_errors', 0);
		error_reporting(0);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Sets maximum depth
	//------------------------------------------------------------------------------------------------------------------
	public function SetMaximumDepth(int $maximumDepth)
	{
		if($maximumDepth < 1)
		{
			$maximumDepth = 1;
		}

		$this->_MaximumDepth = $maximumDepth;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Special error handling
	//------------------------------------------------------------------------------------------------------------------
	public function ErrorHandler(
		int    $errorNumber,
		string $errorText,
		string $errorFile,
		int    $errorLine) : bool
	{
		// Prepares type of error
		$isError = false;

		if(    ($errorNumber === E_ERROR  ) || ($errorNumber === E_USER_ERROR  ))
		{
			$label     = 'PHP ERROR';
			$isError   = true;
		}
		elseif(($errorNumber === E_WARNING) || ($errorNumber === E_USER_WARNING))
		{
			$label     = 'PHP WARNING';
		}
		elseif(($errorNumber === E_NOTICE ) || ($errorNumber === E_USER_NOTICE ))
		{
			$label     = 'PHP NOTICE';
		}
		else
		{
			$isError   = true;
			$isUnknown = true;
			$label     = 'PHP UNKNOWN ERROR';
		}

		// If error type is declared to be hidden :
		// Does nothing
		if(!isset($isUnknown) && !($errorNumber & error_reporting()))
		{
			return false;
		}

		// Prepares error label
		$value = $errorText;

		if($errorFile)
		{
			$value.= ' in <strong>' . $this->TracePath($errorFile) . '</strong>';
		}

		if($errorLine)
		{
			$value.= '<strong>[' . $errorLine . ']</strong>';
		}

		// Outputs error
		if($isError)
		{
			$this->TraceOutput($value, $label, '#333', '#ffcc99aa', 'orange');
		}
		else
		{
			$this->TraceOutput($value, $label, '#333', '#ffff99aa', '#ee8');
		}

		// Stops everything in case of severe error
		if($errorNumber == E_USER_ERROR)
		{
			die('');
		}

		// Bypasses the standard PHP error handler
		return true;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Special exception handling
	//------------------------------------------------------------------------------------------------------------------
	public function ExceptionHandler(Throwable $exception) : bool
	{
		// Inits a real exception
		$label = 'PHP EXCEPTION';

		// Prepares the exception label
		$file    = $this->TracePath($exception->getFile());
		$message = str_replace(' in ' . $this->Core()->HostRoot() . '/', ' at ', $exception->getMessage());

		$value   = $message . ' in <strong>' . $file . '[' . $exception->getLine() . ']</strong>';

		// Outputs error
		$this->TraceOutput($value, $label, '#333', '#ff9999aa', 'red');

		// Bypasses the standard PHP exception handler
		return true;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Formats a path for trace
	//------------------------------------------------------------------------------------------------------------------
	protected function TracePath(string $path) : string
	{
		return $this->Core()->RelativeLink($path);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Formats a structure (i.e. header + lines) for trace
	//------------------------------------------------------------------------------------------------------------------
	protected function TraceStructure(
		string $header,
	           $rows,
		bool   $isExpanded = false,
		int    $indent     = 0,
		array  $parents    = array()) : string
	{
		// If maximum depth has been reached :
		// Stops here
		if($indent === $this->MaximumDepth())
		{
			return "(... limit reached ...)";
		}

		// No line :
		// Just outputs the header
		if(!is_array($rows))
		{
			return '<strong>' . $header . '</strong> {' . $rows . '}';
		}

		if(is_array($rows) && empty($rows))
		{
			return '<strong>' . $header . '</strong> {}';
		}

		// Tree animation
		$code    = 'fewd_trace_' . uniqid();
		$display = 'document.getElementById(\'' . $code . '\').style.display';

		$more  = '+';
		$less  = '-';

		if($isExpanded)
		{
			$symbol = $less;
			$style  = 'block';
		}
		else
		{
			$symbol = $more;
			$style = 'none';
		}

		// Button "open / close"
		$button = '<a href="#" style="';
		$button.= 'border:1px solid;';
		$button.= 'padding-left:0.2em;';
		$button.= 'padding-right:0.2em;';
		$button.= 'text-decoration:none;';
		$button.= '" onclick="';

		$button.= $display . ' = ';
		$button.= '(' . $display . ' == \'block\') ? \'none\' : \'block\';';

		$button.= 'this.innerHTML = (this.innerHTML == \'' . $more . '\') ?';
		$button.= ' \'' . $less . '\' : \'' . $more . '\';return false;';

		$button.= '">' . $symbol . '</a>';

		// Container of sons of current level
		$container = '<div id="' . $code . '" style="';
		$container.= 'display:' . $style . ';';
		$container.= 'margin-left:' . (1.2 * ($indent  + 1)) . 'em;';
		$container.= 'border-left:1px solid;';
		$container.= 'padding-left:0.1em';
		$container.= '">';

		// Shows header
		$trace = '<strong>' . $header . '</strong> ' . $button;
		$trace.= $container;

		// Gets maximum index length
		$length = 0;
		foreach($rows as $k => $v)
		{
			$index = $this->Core()->HtmlEntities($k);
			$length = max($length, strlen($index));
		}

		// Shows each line
		$sep = '';

		foreach($rows as $k => $v)
		{
			$trace.= $sep;

			$index = $this->Core()->HtmlEntities($k);
			if($index === '')
			{
				$index = $k;
			}

			if(strlen($index) < $length)
			{
				$index.= str_repeat(' ', $length - strlen($index));
			}

			$trace.= $index . ' : ';

			$trace.= $this->TraceValue(
				$v,
				$indent + 1,
				$parents);

			$sep = "\n";
		}

		$trace.= '</div>';

		// Result
		return $trace;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Formats an object for trace
	//------------------------------------------------------------------------------------------------------------------
	protected function TraceObject($object, int $indent = 0, array $parents = array()) : string
	{
		// If object was previously traced :
		// Does not trace it
		foreach($parents as $v)
		{
			if($object === $v)
			{
				return get_class($object) . ' (... dead loop ...)';
			}
		}

		// If a "trace" method exists on object class :
		// Uses it
		if(method_exists($object, 'ToTrace'))
		{
			return $object->ToTrace($indent, $parents);
		}

		// Otherwise :
		// Indicates simply that it is an object
		$internal = 'object (' . get_class($object) . ')';

		// Gets public attributes
		$attributes = array();
		foreach($object as $k => $v)
		{
			$attributes[$k] = $object->{$k};
		}

		// Gets accessors
		try
		{
			$reflection = new ReflectionClass($object);
			$properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

			foreach($properties as $v)
			{
				$propertyKey = $v->getName();

				if(substr($propertyKey, 0, 1) === '_')
				{
					$propertyKey = substr($propertyKey, 1);

					if($reflection->hasMethod($propertyKey))
					{
						$method = $reflection->getMethod($propertyKey);
						$attributes[$propertyKey] = $method->invoke($object);
					}
				}
			}
		}
		catch(Exception $e)
		{
		}

		// Traces structure
		$parents[] = $object;

		return $this->TraceStructure(
			$internal,
			$attributes,
			(count($attributes) < 10),
			$indent,
			$parents);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Formats a value for trace
	//------------------------------------------------------------------------------------------------------------------
	protected function TraceValue($value, int $indent = 0, array $parents = array()) : string
	{
		// Handles null value
		if(is_null($value))
		{
			return 'NULL';
		}

		// Handles resource value
		if(is_resource($value))
		{
			return '(resource)';
		}

		// Handles array value
		if(is_array($value))
		{
			return $this->TraceStructure(
				'array',
				$value,
				(count($value) < 10),
				$indent,
				$parents);
		}

		// Handles boolean value
		if(is_bool($value))
		{
			if($value)
			{
				return 'true';
			}

			return 'false';
		}

		// Handles string value
		if(is_string($value))
		{
			return '"' . $this->Core()->HtmlEntities($value) . '"';
		}

		// Handles number value
		if(is_int($value) || is_float($value))
		{
			return $value;
		}

		// Handles object value
		if(is_object($value))
		{
			return $this->TraceObject($value, $indent, $parents);
		}

		// Handles unknown cases
		return '(' . gettype($value) . ')';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Outputs a value with a given design
	//------------------------------------------------------------------------------------------------------------------
	protected function TraceOutput(
		       $value,
		string $label      = '',
		string $color      = '#333',
		string $background = '#ccccccaa',
		string $border     = '#999')
	{
		// If debug functions are inactive :
		// Does nothing
		if(!$this->IsDebug())
		{
			return;
		}

		// Opens banner
		$res = '<pre style="position:relative;overflow:auto;z-index:99999;padding:5px 5px 8px 5px;margin:10px;';
		$res.= 'font:13px Courier New;';
		$res.= 'text-align:left;';
		$res.= 'background:'       . $background . ';';
		$res.= 'border:2px solid ' . $border     . ';';
		$res.= 'color:'            . $color      . ';';
		$res.= '">' . "\n";

		// Adds traced value
		if($label !== '')
		{
			$res.= '<strong>' . $label . '</strong> : ';
		}

		$res.= $value . "\n";

		// Closes banner
		$res.= '</pre>' . "\n";

		// Shows banner as a log or on screen
		if($this->IsLog())
		{
			$this->Log()->Write($res);
		}
		elseif($this->Core()->OutputFormat() === 'text/html')
		{
			echo $res;
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Traces a value
	//------------------------------------------------------------------------------------------------------------------
	public function Trace($value, string $label = '')
	{
		$value = $this->TraceValue($value);

		$this->TraceOutput($value, $label, '#333', '#ccccffaa', '#99f');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Shows a blocking error message and stops here
	//------------------------------------------------------------------------------------------------------------------
	public function Stop(string $message)
	{
		// Traces error
		$this->TraceOutput($message, '', '#333', '#ff9999aa', 'red');

		// Stops all
		exit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Shows a warning message
	//------------------------------------------------------------------------------------------------------------------
	public function Warn(string $message)
	{
		$this->TraceOutput($message, '', '#999', '#ffcc99aa', 'orange');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Maker : TLog
	//------------------------------------------------------------------------------------------------------------------
	public function MakeLog(string $filename, string $ret = "\n") : TLog
	{
		$res = new TLog($this->Core(), $this, $filename, $ret);
		$res->Init();

		return $res;
	}
}
