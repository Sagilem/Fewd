<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Tracer;


use Fewd\Core\AThing;
use Fewd\Core\TCore;


class TLog extends AThing
{
	// Tracer
	private $_Tracer;
	public final function Tracer() : TTracer { return $this->_Tracer; }

	// Filename
	private $_Filename;
	public final function Filename() : string { return $this->_Filename; }

	// Carriage return
	private $_Ret;
	public final function Ret() : string { return $this->_Ret; }

	// Handle to file
	private $_Handle;
	public final function Handle() { return $this->_Handle; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(TCore $core, TTracer $tracer, string $filename, string $ret)
	{
		parent::__construct($core);

		$this->_Tracer   = $tracer;
		$this->_Filename = $filename;
		$this->_Ret      = $ret;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Destructor
	//------------------------------------------------------------------------------------------------------------------
	// Automatically closes log file
	//------------------------------------------------------------------------------------------------------------------
	public function __destruct()
	{
		if($this->Handle())
		{
			// Closes HTML tags
			$this->Write('</body></html>');

			// Closes the log file
			fclose($this->Handle());
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Filename = $this->DefineFilename();
		$this->_Ret      = $this->DefineRet();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Filename
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineFilename() : string
	{
		$res = $this->Filename();

		if($res === '')
		{
			$res = 'log_{DATETIME}.html';
		}

		$datetime = @date('YmdHis');

		$res = str_replace('{DATETIME}', $datetime, $res);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Ret
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineRet() : string
	{
		$res = $this->Ret();

		if(empty($res))
		{
			$res = "\n";
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Writes a message to log
	//------------------------------------------------------------------------------------------------------------------
	public function Write(string $message)
	{
		// If still no handle :
		// Opens the log file for writing
		if($this->Handle() === null)
		{
			// Ensures that the log directory exists
			if(!is_dir($this->Tracer()->LogDirname()))
			{
				if(mkdir($this->Tracer()->LogDirname(), 0700, true) === false)
				{
					return;
				}
			}

			// Opens handle
			$path = $this->Core()->Join(
				$this->Core()->HostRoot(),
				$this->Tracer()->LogDirname(),
				$this->Filename());

			$this->_Handle = fopen($path, 'w');

			$this->Write('<!DOCTYPE html>');
			$this->Write('<html lang="en-US"><body>');
		}

		// Writes message
		if($this->Handle())
		{
			fwrite($this->Handle(), $message . $this->Ret());
		}
	}
}
