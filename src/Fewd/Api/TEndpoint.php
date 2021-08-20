<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Api;


use Fewd\Core\AThing;
use Fewd\Core\TCore;



class TEndpoint extends AThing
{
	// Api
	private $_Api;
	public final function Api() : TApi { return $this->_Api; }

	// Path (example : "pet/{id}")
	private $_Path;
	public final function Path() : string { return $this->_Path; }

	// Summary
	private $_Summary;
	public final function Summary() : string { return $this->_Summary; }

	// Description
	private $_Description;
	public final function Description() : string { return $this->_Description; }

	// Chapter
	private $_Chapter;
	public final function Chapter() : ?TChapter { return $this->_Chapter; }

	// Maximum limit (i.e. maximum records number that could be GET at once)
	private $_MaximumLimit;
	public final function MaximumLimit() : int { return $this->_MaximumLimit; }

	// Gets the maximum age for a preflight run (see CORS standard)
	private $_MaximumAge;
	public final function MaximumAge() : int { return $this->_MaximumAge; }

	// Wildcards in path
	private $_Wildcards;
	public final function Wildcards()             : array { return $this->_Wildcards;             }
	public final function HasWildcard(string $id) : bool  { return isset($this->_Wildcards[$id]); }

	// Operations associated to verbs
	private $_Operations = array();
	public final function Operations() : array                              { return $this->_Operations;               }
	public final function Operation(   string $verb) : TOperation           { return $this->_Operations[$verb] ?? null;}
	public final function HasOperation(string $verb) : bool                 { return isset($this->_Operations[$verb]); }
	public       function AddOperation(string $verb, TOperation $operation) { $this->_Operations[$verb] = $operation;  }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(
		TCore     $core,
		TApi      $api,
		string    $path,
		string    $summary,
		string    $description,
		?TChapter $chapter,
		int       $maximumLimit,
		int       $maximumAge)
	{
		parent::__construct($core);

		$this->_Api          = $api;
		$this->_Path         = $path;
		$this->_Summary      = $summary;
		$this->_Description  = $description;
		$this->_Chapter      = $chapter;
		$this->_MaximumLimit = $maximumLimit;
		$this->_MaximumAge   = $maximumAge;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_Path         = $this->DefinePath();
		$this->_Wildcards    = $this->DefineWildcards();
		$this->_Summary      = $this->DefineSummary();
		$this->_Description  = $this->DefineDescription();
		$this->_MaximumLimit = $this->DefineMaximumLimit();
		$this->_MaximumAge   = $this->DefineMaximumAge();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Path
	//------------------------------------------------------------------------------------------------------------------
	protected function DefinePath() : string
	{
		// Path is case insensitive
		$res = $this->Core()->ToLower($this->Path());

		// No query string in path
		$pos = strpos($res, '?');
		if($pos !== false)
		{
			$res = substr($res, 0, $pos);
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Wildcards
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineWildcards() : array
	{
		// Gets wildcards from endpoint's path
		$res     = array();
		$matches = array();

		$nb = preg_match_all('/([{].*[}])/U', $this->Path(), $matches);
		if(is_int($nb) && ($nb > 0))
		{
			foreach($matches[0] as $v)
			{
				$key       = substr($v, 1, -1);
				$res[$key] = $key;
			}
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Summary
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineSummary() : string
	{
		// If a summary was provided :
		// Keeps it
		if($this->Summary() !== '')
		{
			return $this->Summary();
		}

		// Summary contains path (without wildcards)
		$res  = '';
		$path = $this->Path();

		foreach($this->Wildcards() as $k => $v)
		{
			$path = str_replace('{' . $k . '}', '', $path);
		}

		$parts = explode('/', $path);

		$sep = '';
		foreach($parts as $v)
		{
			if($v !== '')
			{
				if($sep === '')
				{
					$v = ucFirst($v);
				}

				$res.= $sep . $v;
				$sep = ' ';
			}
		}

		// Id ends with " by xxx and yyy" (where xxx and yyy are wildcards)
		$sep = ' by ';
		foreach($this->Wildcards() as $k => $v)
		{
			$res.= $sep . $k;
			$sep = ' and ';
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Description
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDescription() : string
	{
		return $this->Description();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Maximum records number
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMaximumLimit() : int
	{
		if($this->MaximumLimit() <= 0)
		{
			return 0;
		}

		return $this->MaximumLimit();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Maximum age for a preflight run
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineMaximumAge() : int
	{
		if ($this->MaximumAge() <= 0)
		{
			return 60;
		}

		return $this->MaximumAge();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a list of allowed verbs for a preflight run (see CORS standard)
	//------------------------------------------------------------------------------------------------------------------
	public function AllowedVerbs() : string
	{
		$verbs = array();
		foreach($this->Operations() as $k => $v)
		{
			$verbs[$k] = $k;
		}

		$verbs['OPTIONS'] = 'OPTIONS';

		$res = implode(', ', $verbs);

		return $res;
	}
}
