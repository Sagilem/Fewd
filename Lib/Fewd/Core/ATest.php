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
			if($foundValue === null)
			{
				$foundValue = '<null>';
			}
			elseif(is_string($foundValue))
			{
				$foundValue = '"' . $foundValue . '"';
			}

			if($expectedValue === null)
			{
				$expectedValue = '<null>';
			}
			elseif(is_string($expectedValue))
			{
				$expectedValue = '"' . $expectedValue . '"';
			}

			$res.= '    found    : ' . $foundValue . "\n";
			$res.= '    expected : ' . $expectedValue;
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
	public function Check(string|int|float $foundValue, string|int|float $expectedValue, string $message = '')
	{
		// Alias
		$this->CheckEQ($foundValue, $expectedValue, $message);
	}

	public function CheckEQ(string|int|float $foundValue, string|int|float $expectedValue, string $message = '')
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
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		// To override !
	}
}