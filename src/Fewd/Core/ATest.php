<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Core;


abstract class ATest
{
	// Failures
	private $_Failures = array();
	public final function Failures() : array { return $this->_Failures; }


	//------------------------------------------------------------------------------------------------------------------
	// No init on tests
	//------------------------------------------------------------------------------------------------------------------
	public final function Init()
	{
	}


	//------------------------------------------------------------------------------------------------------------------
	// Turns a path into a relative path to website
	//------------------------------------------------------------------------------------------------------------------
	protected function RelativePath(string $path) : string
	{
		$root = dirname($_SERVER['SCRIPT_FILENAME']);

		if($path === $root)
		{
			return '';
		}

		if(substr($path, 0, strlen($root) + 1) === $root . '/')
		{
			return substr($path, strlen($root));
		}

		return $path;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the position of the first difference between two strings
	//------------------------------------------------------------------------------------------------------------------
	protected function DifferencePosition(string $value1, string $value2) : int
	{
		if($value1 === $value2)
		{
			return -1;
		}

		$res     = 0;
		$length1 = strlen($value1);
		$length2 = strlen($value2);

		while(($res < $length1) && ($res < $length2) && (substr($value1, $res, 1) === substr($value2, $res, 1)))
		{
			$res++;
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Outputs a given message
	//------------------------------------------------------------------------------------------------------------------
	protected function Output(string $message)
	{
		$text = '<pre style="position:relative;overflow:auto;z-index:99999;padding:5px 5px 8px 5px;margin:10px;';
		$text.= 'font:13px Courier New;';
		$text.= 'text-align:left;';
		$text.= 'background:#af77ffaa;';
		$text.= 'border:2px solid #7f00ff;';
		$text.= 'color:#333;';
		$text.= '">' . $message . '</pre>';

		echo $text;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Formats a failure value
	//------------------------------------------------------------------------------------------------------------------
	protected function FailureValue(string $value, string $comparisonValue = '') : string
	{
		// If value is NULL :
		// Returns a message
		if($value === null)
		{
			return '<null>';
		}

		// If value is not string :
		// Returns the value turned into a string
		if(!is_string($value))
		{
			return strval($value);
		}

		// No html entity
		$res             = htmlentities($value          , ENT_QUOTES, 'UTF-8');
		$comparisonValue = htmlentities($comparisonValue, ENT_QUOTES, 'UTF-8');

		// If a comparison value was provided :
		// Formats value to enhance the difference
		if($comparisonValue !== '')
		{
			$pos = $this->DifferencePosition($res, $comparisonValue);

			if($pos !== -1)
			{
				$res = substr($res, 0, $pos) . '<strong style="color:white">--></strong>' . substr($res, $pos);
			}
		}

		// If value is a multiline string :
		// Adds some space around it, for a better display
		if(strpos($res, "\n") != false)
		{
			$res = "\n" . $res . "\n";
		}

		// Otherwise :
		// Adds quotes
		else
		{
			$res = '"' . $res . '"';
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Formats a failure text
	//------------------------------------------------------------------------------------------------------------------
	protected function FailureText(
		string $file,
		string $line,
		string $function,
		string $message,
		mixed  $foundValue    = '',
		mixed  $expectedValue =	'') : string
	{
		// Builds the main text
		$res = $function . '() failed at ' . $file . '[' . $line . ']';

		if(($message !== '') || (func_num_args() > 4))
		{
			$res.= " :\n";
		}

		// Adds the special message
		if($message !== '')
		{
			$res.= $message . "\n";
		}

		// If found and expected values where provided :
		// Adds them
		if(func_num_args() > 4)
		{
			$foundValue    = $this->FailureValue($foundValue   , $expectedValue);
			$expectedValue = $this->FailureValue($expectedValue);

			$res.= '    found    : <span style="color:red">'  . $foundValue    . '</span>' . "\n";
			$res.= '    expected : <span style="color:blue">' . $expectedValue . '</span>';
		}

		// Result
		return $res;
    }


	//------------------------------------------------------------------------------------------------------------------
	// Raises an error
	//------------------------------------------------------------------------------------------------------------------
	protected function Raise(string $message, mixed $foundValue = '', mixed $expectedValue = '')
	{
		$failure = array();

		// Gets info from caller (i.e. the external function that calls a method from the present class)
		$debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		$index = -1;
		foreach($debug as $k => $v)
		{
			if($v['file'] !== __FILE__)
			{
				$index = $k;
				break;
			}
		}

		if($index > -1)
		{
			$file     = $debug[$index]['file'    ];
			$line     = $debug[$index]['line'    ];
			$function = $debug[$index]['function'];

			$file = $this->RelativePath($file);
		}
		else
		{
			$file     = '<unknown>';
			$line     = -1;
			$function = 'Test';
		}

		// Stores the failure
		$failure['file'    ] = $file;
		$failure['line'    ] = $line;
		$failure['function'] = $function;
		$failure['message' ] = $message;

		// Gets failure text
		if(func_num_args() === 1)
		{
			$text = $this->FailureText($file, $line, $function, $message);
		}
		else
		{
			$failure['found'   ] = $foundValue;
			$failure['expected'] = $expectedValue;

			$text = $this->FailureText($file, $line, $function, $message, $foundValue, $expectedValue);
		}

		$failure['text'] = $text;

		// Stores the failure
		$this->_Failures[] = $failure;

		// Outputs the text
		$this->Output($text);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks a given result
	//------------------------------------------------------------------------------------------------------------------
	public function Check(
		string|int|float|null $foundValue,
		string|int|float|null $expectedValue,
		string                $message      = '')
	{
		// Alias
		$this->CheckEQ($foundValue, $expectedValue, $message);
	}

	public function CheckEQ(
		string|int|float|null $foundValue,
		string|int|float|null $expectedValue,
		string                $message      = '')
	{
		// Double values must be compared using an epsilon delta
		if(is_double($foundValue) || is_double($expectedValue))
		{
			$delta = $expectedValue - $foundValue;

			if(($delta > 0.0001) || ($delta < -0.0001))
			{
				$this->Raise($message, $foundValue, $expectedValue);
			}
		}

		// Other values can be directly compared
		elseif($foundValue != $expectedValue)
		{
			$this->Raise($message, $foundValue, $expectedValue);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a condition is true
	//------------------------------------------------------------------------------------------------------------------
	public function CheckTrue(bool $condition, string $message = '')
	{
		if(!$condition)
		{
			$this->Raise($message);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a condition is false
	//------------------------------------------------------------------------------------------------------------------
	public function CheckFalse(bool $condition, string $message = '')
	{
		if($condition)
		{
			$this->Raise($message);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a string matches with a given preg pattern
	//------------------------------------------------------------------------------------------------------------------
	public function CheckMatch(string $text, string $pattern, string $message = '')
	{
		// If no delimiter provided in pattern :
		// Adds a default delimiter
		if(substr($pattern, 0, 1) !== substr($pattern, -1))
		{
			$pattern = '/' . $pattern . '/';
		}

		// Checks if string matches with pattern
		if(!preg_match($pattern, $text))
		{
			$this->Raise($message, $text, 'pattern : ' . $pattern);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if an array exactly equals to another array
	//------------------------------------------------------------------------------------------------------------------
	public function CheckArray(array $foundArray, array $expectedArray, string $message = '')
	{
		if($foundArray !== $expectedArray)
		{
			$this->Raise($message, serialize($foundArray), serialize($expectedArray));
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if an array has the given keys
	//------------------------------------------------------------------------------------------------------------------
	public function CheckArrayKeys(array $foundArray, array $expectedKeys, string $message = '')
	{
		foreach($expectedKeys as $v)
		{
			if(!isset($foundArray[$v]))
			{
				$this->Raise($message, '', $v);
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a given key exists in a given array
	//------------------------------------------------------------------------------------------------------------------
	public function CheckArrayExist(array $array, string|int $key, string $message = '')
	{
		if(!isset($array[$key]))
		{
			$this->Raise($message);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a single value exists in a given array, for a given key, and is equal to the expected value
	//------------------------------------------------------------------------------------------------------------------
	public function CheckArrayValue(array $array, string|int $key, string|int|float|bool $value, string $message = '')
	{
		if(!isset($array[$key]))
		{
			$this->Raise($message);
		}
		elseif($array[$key] !== $value)
		{
			$this->Raise($message, $array[$key], $value);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		// To override !
	}
}