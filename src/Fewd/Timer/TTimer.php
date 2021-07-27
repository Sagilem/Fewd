<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Timer;


use Fewd\Core\AModule;


class TTimer extends AModule
{
	// Clocks
	private $_Clocks = array();
	protected final function Clocks() : array                   { return $this->_Clocks; }
	protected final function Clock(   string $id) : float       { return $this->_Clocks[$id] ?? 0.0; }
	protected final function HasClock(string $id) : bool        { return isset($this->_Clocks[$id]); }
	protected       function SetClock(string $id, float $value) { $this->_Clocks[$id] = $value; }
	protected       function DelClock(string $id)               { unset($this->_Clocks[$id]); }

	// Breaks
	private $_Breaks = array();
	protected final function Breaks() : array                   { return $this->_Breaks; }
	protected final function Break(   string $id) : float       { return $this->_Breaks[$id] ?? 0.0; }
	protected final function HasBreak(string $id) : bool        { return isset($this->_Breaks[$id]); }
	protected       function SetBreak(string $id, float $value) { $this->_Breaks[$id] = $value; }
	protected       function DelBreak(string $id)               { unset ($this->_Breaks[$id]); }

	// Indicates if displaying an output is possible
	private $_IsOutput = false;
	public final function IsOutput() : bool  { return $this->_IsOutput;  }
	public       function OutputOn()         { $this->_IsOutput = true;  }
	public       function OutputOff()        { $this->_IsOutput = false; }

	// Css style
	private $_Style;
	public final function Style() : string { return $this->_Style; }


	//------------------------------------------------------------------------------------------------------------------
	// Destructor
	//------------------------------------------------------------------------------------------------------------------
	// Displays all active clocks
	//------------------------------------------------------------------------------------------------------------------
	public function __destruct()
	{
		foreach($this->Clocks() as $k => $v)
		{
			if($k !== '')
			{
				$this->Show($k);
			}
		}

		$this->Show('');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		// Inherited
		parent::Init();

		// Initializes a default timezone when needed
		if(!date_default_timezone_get())
		{
			date_default_timezone_set('Europe/Paris');
		}

		// Memorizes current timer
		$this->Start('');

		// Defines
		$this->_Style = $this->DefineStyle();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Style
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineStyle() : string
	{
		return 'position:relative';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the current microtime
	//------------------------------------------------------------------------------------------------------------------
	public function Militime() : float
	{
		list($usec, $sec) = explode(' ', microtime());

		return ((float)$usec + (float)$sec) * 1000;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Starts a clock
	//------------------------------------------------------------------------------------------------------------------
	public function Start(string $code)
	{
		$this->DelBreak($code);
		$this->SetClock($code, $this->Militime());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Stops a clock and shows it
	//------------------------------------------------------------------------------------------------------------------
	public function Stop(string $code) : float
	{
		$this->DelBreak($code);
		$this->DelClock($code);

		return $this->Show($code);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Pauses a clock
	//------------------------------------------------------------------------------------------------------------------
	public function Pause(string $code)
	{
		if($this->HasClock($code) && !$this->HasBreak($code))
		{
			$this->SetBreak($code, $this->Militime());
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Unpauses a clock
	//------------------------------------------------------------------------------------------------------------------
	public function Unpause(string $code)
	{
		if($this->HasBreak($code))
		{
			$this->SetClock($code, $this->Clock($code) + $this->Militime() - $this->Break($code));

			$this->DelBreak($code);
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets clock value
	//------------------------------------------------------------------------------------------------------------------
	public function Get(string $code) : float
	{
		if($this->HasBreak($code))
		{
			return $this->Break($code) - $this->Clock($code);
		}

		return $this->Militime() - $this->Clock($code);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets memory usage
	//------------------------------------------------------------------------------------------------------------------
	public function GetMemoryUsage() : int
	{
		$res = ini_get('memory_limit');

		switch(substr($res, -1))
		{
			case 'G': case 'g': $res = (int)$res * 1073741824; break;
			case 'M': case 'm': $res = (int)$res * 1048576;    break;
			case 'K': case 'k': $res = (int)$res * 1024;       break;
			default:            $res = (int)$res;
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets memory peak
	//------------------------------------------------------------------------------------------------------------------
	public function GetMemoryPeak() : int
	{
		return memory_get_peak_usage(true);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Shows clock
	//------------------------------------------------------------------------------------------------------------------
	public function Show(string $code) : float
	{
		// Gets duration
		$duration = $this->Get($code) / 1000;

		$formattedValue = '';
		if($code !== '')
		{
			$formattedValue = $code . '=';
		}

		// Prepares duration output
		$formattedValue.= number_format($duration, 3) . ' s';

		// If timer is not paused :
		if(!$this->HasBreak($code))
		{
			// Gets memory ratios
			$usage = $this->GetMemoryUsage();
			$peak  = $this->GetMemoryPeak();
			$ratio = ceil(100 * $peak / $usage);

			// Prepares memory ratios output
			$formattedValue.= ', ' . round($peak / 1048576, 1) . ' Mo, ' . $ratio . '%';
		}

		// Outputs values
		if($this->IsOutput() && ($this->Core()->OutputFormat() === 'text/html'))
		{
			$show = '<div style="text-align:left;z-index:99999;margin:30px 10px 10px 10px;' . $this->Style() . '">';
			$show.= '<span style="padding:5px;font:italic 11px sans-serif;color:#333;';
			$show.= 'background:rgba(220, 220, 220, .5)">(' . $formattedValue . ')</span></div>' . "\n";

			echo $show;
		}

		// Returns value
		return $duration;
	}
}