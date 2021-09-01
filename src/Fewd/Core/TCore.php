<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Core;


use Exception;
use ReflectionFunction;



class TCore extends AModule
{
	// Indicates if server is localhost or not
	private $_IsLocalhost;
	public final function IsLocalhost() : bool { return $this->_IsLocalhost; }

	// Host root
	private $_HostRoot;
	public final function HostRoot() : string { return $this->_HostRoot; }

	// Protocol
	private $_Protocol;
	public final function Protocol() : string { return $this->_Protocol; }

	// Domain
	private $_Domain;
	public final function Domain() : string { return $this->_Domain; }

	// Home
	private $_Home;
	public final function Home() : string { return $this->_Home; }

	// Current absolute Url
	private $_CurrentAbsoluteUrl;
	public final function CurrentAbsoluteUrl() : string { return $this->_CurrentAbsoluteUrl; }

	// Current relative Url
	private $_CurrentRelativeUrl;
	public final function CurrentRelativeUrl() : string { return $this->_CurrentRelativeUrl; }

	// Current path (i.e. the "path" part from the current url)
	private $_CurrentPath;
	public final function CurrentPath() : string { return $this->_CurrentPath; }

	// Current args (equivalent to $_GET)
	private $_CurrentArgs;
	public final function CurrentArgs() : array { return $this->_CurrentArgs; }

	// Current Http verb
	private $_CurrentVerb;
	public final function CurrentVerb() : string { return $this->_CurrentVerb; }

	// Indicates if first hit
	private $_IsFirstHit;
	public final function IsFirstHit() : bool { return $this->_IsFirstHit; }

	// Modules
	private $_Modules = array();
	public final function Modules() : array                     { return $this->_Modules; }
	public final function Module(   string $id) : AModule       { return $this->_Modules[$id] ?? null; }
	public final function HasModule(string $id) : bool          { return isset($this->_Modules[$id]); }
	public       function AddModule(string $id, AModule $value) { $this->_Modules[$id] = $value; }

	// Ticket generator
	private $_Ticket = 1000;
	public final function Ticket() : string { return '' . ($this->_Ticket++); }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct()
	{
		parent::__construct($this);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_IsLocalhost        = $this->DefineIsLocalhost();
		$this->_HostRoot           = $this->DefineHostRoot();
		$this->_Protocol           = $this->DefineProtocol();
		$this->_Domain             = $this->DefineDomain();
		$this->_Home               = $this->DefineHome();
		$this->_CurrentAbsoluteUrl = $this->DefineCurrentAbsoluteUrl();
		$this->_CurrentRelativeUrl = $this->DefineCurrentRelativeUrl();
		$this->_CurrentPath        = $this->DefineCurrentPath();
		$this->_CurrentArgs        = $this->DefineCurrentArgs();
		$this->_CurrentVerb        = $this->DefineCurrentVerb();
		$this->_IsFirstHit         = $this->DefineIsFirstHit();

		mb_internal_encoding('UTF-8');
        mb_regex_encoding(   'UTF-8');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : IsLocalhost
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineIsLocalhost() : bool
	{
		return
			(  (       $_SERVER['HTTP_HOST']         === 'localhost' )
			|| (       $_SERVER['HTTP_HOST']         === '127.0.0.1' )
			|| (       $_SERVER['HTTP_HOST']         === 'website'   )
			|| (substr($_SERVER['HTTP_HOST'], 0, 10) === 'localhost:'));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : HostRoot
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineHostRoot() : string
	{
		$path   = __DIR__;

		$script = $_SERVER['SCRIPT_FILENAME'];

		while(($path !== '/') && !$this->StartsWith($script, $path . '/'))
		{
			$path = dirname($path);
		}

		return $path;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Protocol
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineProtocol() : string
	{
		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';

		return $protocol . '://';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Domain
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDomain() : string
	{
		if($this->IsLocalhost())
		{
			$res  = 'localhost';

			$host = substr($this->HostRoot(), strlen($_SERVER['DOCUMENT_ROOT']) + 1);

			if($host !== '')
			{
				$res.= '/' . $host;
			}

			return $res;
		}

		return $_SERVER['HTTP_HOST'];
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Home
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineHome() : string
	{
		return $this->Protocol() . $this->Domain();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Current absolute Url
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineCurrentAbsoluteUrl() : string
	{
		$res = $this->Protocol() . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if(substr($res, -1) === '/')
		{
			$res = substr($res, 0, -1);
		}

		return strtolower($res);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Current relative Url
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineCurrentRelativeUrl() : string
	{
		return $this->RelativeLink($this->CurrentAbsoluteUrl());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Current path
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineCurrentPath() : string
	{
		$res = $this->CurrentRelativeUrl();

		$pos = strpos($res, '?');

		if($pos !== false)
		{
			return substr($res, 0, $pos);
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Current arguments
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineCurrentArgs() : array
	{
		return $_GET;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Current Http verb
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineCurrentVerb() : string
	{
		return $_SERVER['REQUEST_METHOD'];
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Is first hit
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineIsFirstHit() : bool
	{
		// If visit comes from nowhere :
		// This is a first hit
		$referer = $_SERVER['HTTP_REFERER'] ?? '';

		if(!isset($referer) || ($referer === null) || ($referer === ''))
		{
			return true;
		}

		// If visit comes from another page from the current domain :
		// This is not a first hit
		if($this->StartsWith($referer, $this->Home()))
		{
			return false;
		}

		// Otherwise :
		// This is a first hit
		return true;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the output format (depending on sent headers)
	//------------------------------------------------------------------------------------------------------------------
	public function OutputFormat()
	{
		$list = headers_list();

		foreach($list as $v)
		{
			$v = strtolower($v);

			if(substr($v, 0, 14) === 'content-type: ')
			{
				return substr($v, 15);
			}
		}

		return 'text/html';
	}




	//==================================================================================================================
	//
	// STRINGS HELPERS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given text starts with another one
	//------------------------------------------------------------------------------------------------------------------
	public function StartsWith(string $haystack, string $needle) : bool
	{
		if($needle === '')
		{
			return true;
		}

		return (substr($haystack, 0, strlen($needle)) === $needle);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given text ends with another one
	//------------------------------------------------------------------------------------------------------------------
	public function EndsWith(string $haystack, string $needle) : bool
	{
		if($needle === '')
		{
			return true;
		}

		return (substr($haystack, -strlen($needle)) === $needle);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given text contains another one
	//------------------------------------------------------------------------------------------------------------------
	public function Contains(string $haystack, string $needle) : bool
	{
		if($needle === '')
		{
			return true;
		}

		$pos = strpos($haystack, $needle);

		return ($pos !== false);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Converts a text into uppercase
	//------------------------------------------------------------------------------------------------------------------
	function ToUpper(string $text) : string
	{
		return mb_strtoupper($text);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Converts a text into lowercase
	//------------------------------------------------------------------------------------------------------------------
	function ToLower(string $text) : string
	{
		return mb_strtolower($text);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Removes accents from a text
	//------------------------------------------------------------------------------------------------------------------
	public function WithoutAccents(string $text) : string
	{
		// Removes accents
		$accents = array(
			'á', 'à', 'â', 'ã', 'ª', 'ä', 'å', 'æ',
			'é', 'è', 'ê', 'ë',
			'í', 'ì', 'î', 'ï',
			'œ', '', 'ò', 'ó', 'ô', 'õ', 'ö', 'º', 'ø',
			'ú', 'ù', 'û', 'ü',
			'ý', 'ÿ',
			'ç',
			'ñ',
			'Á', 'À', 'Â', 'Ã', 'ª', 'Ä', 'Å', 'Æ',
			'É', 'È', 'Ê', 'Ë',
			'Í', 'Ì', 'Î', 'Ï',
			'Œ', '', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø',
			'Ú', 'Ù', 'Û', 'Ü',
			'Ý', 'Ÿ',
			'Ç',
			'Ñ',
			'ß', '',
			'¥', '€', '$', '£');

		$noAccents = array(
			'a', 'a', 'a', 'a', 'a', 'a', 'a', 'ae',
			'e', 'e', 'e', 'e',
			'i', 'i', 'i', 'i',
			'oe','oe','o', 'o', 'o', 'o', 'o', 'o', 'o',
			'u', 'u', 'u', 'u',
			'y', 'y',
			'c',
			'n',
			'A', 'A', 'A', 'A', 'A', 'A', 'A', 'AE',
			'E', 'E', 'E', 'E',
			'I', 'I', 'I', 'I',
			'OE','OE','O', 'O', 'O', 'O', 'O', 'O',
			'U', 'U', 'U', 'U',
			'Y', 'Y',
			'C',
			'N',
			'ss','f',
			'JPY', 'EUR', 'USD', 'GBP');

		$res = str_replace($accents, $noAccents, $text);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Converts a string in an Ascii string (with accents removal, and so on)
	//------------------------------------------------------------------------------------------------------------------
	public function ToAscii(string $text, string $separator = '-') : string
	{
		// Removes all HTML tags
		$res = strip_tags($text);

		// Replaces all separators (blanks, '_', '-') by the given separator
		$res = preg_replace(
			array('/\s+/', '/[_|-]+/'),
			array($separator, $separator), $res);

		// Removes separators at beginning and end of label
		if($separator !== '')
		{
			$res = preg_replace('/^' . $separator . '+|' . $separator . '+$/', '', $res);
		}

		// Removes accents
		$res = $this->WithoutAccents($res);

		// Turns label into lower case
		$res = $this->ToLower($res);

		// Ensures that only ascii characters are kept
		$res = preg_replace('/[^a-z0-9- ]/', '', $res);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Transforms special characters in entities (&amp; etc.)
	//------------------------------------------------------------------------------------------------------------------
	public function HtmlEntities(string $text) : string
	{
		return htmlentities($text, ENT_QUOTES, 'UTF-8');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Concatenates string parts with a given separator
	//------------------------------------------------------------------------------------------------------------------
	public function Concatenate(string $separator, array|string $parts) : string
	{
		// Gets parts
		if(!is_array($parts))
		{
			$parts = func_get_args();
			array_Shift($parts);
		}

		// For each part :
		$res = '';
		$sep = '';

		foreach($parts as $v)
		{
			// If current part is empty,
			// Or is not a string :
			// Ignores it
			if(($v === '') || !is_string($v))
			{
				continue;
			}

			// Otherwise :
			// Adds it
			$res.= $sep . $v;
			$sep = $separator;
		}

		// Result
		return $res;
	}




	//==================================================================================================================
	//
	// ARRAYS HELPERS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Deep-converts an object into an array
	//------------------------------------------------------------------------------------------------------------------
	public function ObjectToArray(object $object) : array
	{
		$res = array();
		foreach($object as $k => $v)
		{
			$res[$k] = $this->ObjectToArray($v);
		}

        return $res;
    }


	//------------------------------------------------------------------------------------------------------------------
	// Generates a string with the first values from a given array
	//------------------------------------------------------------------------------------------------------------------
	public function ArrayToString(
		array  $array,
		int    $limit     = 5,
		string $separator = ', ',
		string $ellipsis  = '...',
		string $pattern   = '{{VALUE}}') : string
	{
		$res = '';
		$sep = '';
		$n   = 0;

		foreach($array as $v)
		{
			$n++;
			if($n > $limit)
			{
				$res.= $sep . $ellipsis;
				break;
			}

			$cur = strval($v);
			$cur = str_replace('{{VALUE}}', $cur, $pattern);

			$res.= $sep . $cur;
			$sep = $separator;
		}

		return $res;
	}




	//==================================================================================================================
	//
	// IDENTIFIERS HELPERS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Generates a unique identifier with a given length and format (xxxx-xxxx-xxxx-xxxx by default)
	//------------------------------------------------------------------------------------------------------------------
	public function UniqueId(int $length = 16, string $separator = '-', int $step = 4) : string
	{
		// If no length provided :
		// Returns an empty string
		if($length <= 0)
		{
			return '';
		}

		// Gets a "uniqid" of 13 digits
		$res = uniqid();
		$n   = 13;

		// Adds other "uniqids" to reach the expected length
		while($n < $length)
		{
			$res.= uniqid();
			$n += 13;
		}

		// Adjusts the length
		$res = substr($res, 0, $length);

		// If a specific format was provided :
		// Applies it
		if(($separator !== '') && ($step > 0))
		{
			$parts = str_split($res, $step);
			$res   = implode($separator, $parts);
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates a UUID version 4
	//------------------------------------------------------------------------------------------------------------------
	public function Uuid() : string
	{
		$data = openssl_random_pseudo_bytes(16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

	    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates a random alphanumeric code
	//------------------------------------------------------------------------------------------------------------------
	public function RandomCode(int $length = 10, string $separator = '', int $step = 0, string $digits = '') : string
	{
		// If no length provided :
		// Returns an empty string
		if($length <= 0)
		{
			return '';
		}

		// Defines characters used for code generation
		if($digits === '')
		{
			$digits = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		}

		// Randomly selects a given number of characters
		$digitsLength = strlen($digits);

		srand((double)microtime() * 1000000);
		$res = '';
		for($i = 0; $i < $length; $i++)
		{
			$res.= $digits[rand() % $digitsLength];
		}

		// If a specific format was provided :
		// Applies it
		if(($separator !== '') && ($step > 0))
		{
			$parts = str_split($res, $step);
			$res   = implode($separator, $parts);
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates a random number with a given length
	//------------------------------------------------------------------------------------------------------------------
	public function RandomNumber(int $length = 4) : string
	{
		if($length <= 0)
		{
			return '';
		}

		$res = mt_rand(0, pow(10, $length) - 1);
		$res = str_pad($res, $length, '0', STR_PAD_LEFT);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates a mnemonic code (i.e. a series of syllables)
	//------------------------------------------------------------------------------------------------------------------
	public function RandomMnemonic(
		int    $length     = 7,
		string $consonants = '',
		string $vowels     = '') : string
	{
		// If no length provided :
		// Returns an empty string
		if($length <= 0)
		{
			return '';
		}

		// Defines consonants list
		if($consonants === '')
		{
            $chars[1] = 'bcdfghjklmnpqrstvwxz';
		}
		else
		{
			$chars[1] = $consonants;
        }

		// Defines vowels list
		if($vowels === '')
		{
			$chars[2] = 'aeiouy';
		}
		else
		{
            $chars[2] = $vowels;
        }

		// Randomly select a given number of characters
		$n[1] = strlen($chars[1]);
		$n[2] = strlen($chars[2]);

		$j   = 1;
		$res = '';
		for($i = 0; $i < $length; $i++)
		{
			$res.= $chars[$j][rand() % $n[$j]];
			$j = 3 - $j;
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates a mnemonic code from a label
 	//------------------------------------------------------------------------------------------------------------------
	public function LabelMnemonic(
		string $label,
		int    $length = 7) : string
	{
		// If no length provided :
		// Returns an empty string
		if($length <= 0)
		{
			return '';
		}

		// Transforms label into a simple ascii string
		$res = $this->ToAscii($label, '');

		// Removes numbers
		$res = str_replace(array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), '', $res);

		// If label is too short :
		// Completes with a random mnemonic
		if(strlen($res) < $length)
		{
			$res.= $this->RandomMnemonic($length - strlen($res));
		}

		// If label is too long :
		// Shortens it to the expected length
		else
		{
			$res = substr($res, 0, $length);
		}

		// Result
		return $res;
	}




	//==================================================================================================================
	//
	// PATHS AND URLS HELPERS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a link has a given root
	//------------------------------------------------------------------------------------------------------------------
	public function HasRoot(string $link, string $root) : bool
	{
		return ($this->StartsWith($link, $root . '/') || ($link === $root));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a link is an absolute url to an external domain
	//------------------------------------------------------------------------------------------------------------------
	public function IsExternalUrl(string $link) : bool
	{
		if(strpos($link, '://') === false)
		{
			return false;
		}

		return !$this->HasRoot($link, $this->Protocol() . $this->Domain());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a link is an absolute url to the current domain
	//------------------------------------------------------------------------------------------------------------------
	public function IsAbsoluteUrl(string $link) : bool
	{
		return $this->HasRoot($link, $this->Protocol() . $this->Domain());
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a link is an absolute file name
	//------------------------------------------------------------------------------------------------------------------
	public function IsAbsoluteFilename(string $link) : bool
	{
		$hostRoot = $this->HostRoot();

		return ($this->StartsWith($link, $hostRoot . '/') || ($link === $hostRoot));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Turns a link into a relative link
	//------------------------------------------------------------------------------------------------------------------
	public function RelativeLink(string $link, bool $keepsExternal = true) : string
	{
		// If link is an external url :
		// Returns nothing
		if($this->IsExternalUrl($link))
		{
			if($keepsExternal)
			{
				return $link;
			}

			return '';
		}

		// If link is an absolute url :
		// Removes protocol and domain name
		if($this->IsAbsoluteUrl($link))
		{
			return substr($link, strlen($this->Protocol()) + strlen($this->Domain()) + 1);
		}

		// If it is an absolute filename :
		// Removes host root
		if($this->IsAbsoluteFilename($link))
		{
			return substr($link, strlen($this->HostRoot()) + 1);
		}

		// Otherwise :
		// It is already a relative url
		return $link;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Turns a link into an absolute url to the current domain
	//------------------------------------------------------------------------------------------------------------------
	public function AbsoluteUrl(string $link, bool $keepsExternal = true) : string
	{
		// If link is an external url :
		// Returns nothing
		if($this->IsExternalUrl($link))
		{
			if($keepsExternal)
			{
				return $link;
			}

			return '';
		}

		// If link is already an absolute url :
		// Returns it directly
		if($this->IsAbsoluteUrl($link))
		{
			return $link;
		}

		// If link is an absolute filename :
		// Transforms it into a relative filename
		if($this->IsAbsoluteFilename($link))
		{
			$link = $this->RelativeLink($link);
		}

		// Adds protocol and domain to link
		$res = $this->Protocol() . $this->Domain();

		if($link !== '')
		{
			$res.= '/' . $link;
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Turns a link into an absolute file name
	//------------------------------------------------------------------------------------------------------------------
	public function AbsoluteFilename(string $link, bool $keepsExternal = true) : string
	{
		// If link is an external url :
		// Returns nothing
		if($this->IsExternalUrl($link))
		{
			if($keepsExternal)
			{
				return $link;
			}

			return '';
		}

		// If link is already an absolute filename :
		// Returns it
		if($this->IsAbsoluteFilename($link))
		{
			return $link;
		}

		// If link is an absolute url :
		// Transforms it into a relative url
		if($this->IsAbsoluteUrl($link))
		{
			$link = $this->RelativeLink($link);
		}

		// Adds host root
		$res = $this->HostRoot();

		if($link !== '')
		{
			$res.= '/' . $link;
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Joins link parts
	//------------------------------------------------------------------------------------------------------------------
	public function Join($parts) : string
	{
		// Gets parts
		if(!is_array($parts))
		{
			$parts = func_get_args();
		}

		// For each part :
		$res = '';
		$sep = '';

		foreach($parts as $v)
		{
			// If current part is empty,
			// Or is not a string :
			// Ignores it
			if(($v === '') || !is_string($v))
			{
				continue;
			}

			// If current part is the first not empty one,
			// And begins with '/' :
			// This leading '/' is kept
			if($this->StartsWith($v, '/') && ($res === ''))
			{
				$res.= '/';
			}

			// Removes leading '/'
			while($this->StartsWith($v, '/'))
			{
				$v = substr($v, 1);
			}

			// Removes trailing '/'
			while($this->EndsWith($v, '/'))
			{
				$v = substr($v, 0, -1);
			}

			// If cleansed part is empty :
			// Ignores it
			if($v === '')
			{
				continue;
			}

			// Adds part to result
			$res.= $sep . $v;
			$sep = '/';
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Transforms an args array into a query string
	//------------------------------------------------------------------------------------------------------------------
	public function ArgsQuery(array $args) : string
	{
		return $this->ArgsSubQuery($args, '');
	}

	protected function ArgsSubQuery(array $args, string $name = '') : string
	{
		$res = '';
		$sep = '';

		// For each argument :
		foreach($args as $k => $v)
		{
			// Builds key
			// (default integer keys are masked)
			// example : u[][a]=2&u[b]=1&u[]=3, and not u[0][a]=2&u[b]=1&u[1]=3
			$i = 0;
			if($k === $i)
			{
				$key = '';
				$i++;
			}
			else
			{
				$key = '' . $k;
			}

			// If a name was provided :
			//
			if($name !== '')
			{
				$key = $name . '[' . $key . ']';
			}

			// If value is an array :
			// Recursive call
			if(is_array($v))
			{
				$res.= $sep . $this->ArgsQuery($v, $key);
				$sep = '&';
			}

			// If value is a scalar :
			// Outputs it
			elseif(is_scalar($v))
			{
				$value = '' . $v;

				$res.= $sep . $key . '=' . urlencode($value);
				$sep = '&';
			}
		}

		// Result
		return $res;
	}




	//==================================================================================================================
	//
	// FILE HELPERS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Gets all files and subdirectories within a directory (recursively)
	//------------------------------------------------------------------------------------------------------------------
	public function Files(string $dirname, bool $keepsSubdirectories = false, array &$res = array()) : array
	{
		// Scans files
		try
		{
			$path = realpath($dirname);

			if($path === false)
			{
				return array();
			}

			$files = scandir($path);
		}
		catch(Exception)
		{
			return $res;
		}

		// For each file in path :
		foreach($files as $v)
		{
			// If current file is a special path :
			// Ignores it
			if(($v === '.') || ($v === '..') || ($v === '.DS_Store'))
			{
				continue;
			}

			// If current file is a subdirectory :
			$cur = $path . '/' . $v;

			try
			{
				$isDirectory = is_dir($cur);
			}
			catch(Exception)
			{
				$isDirectory = false;
			}

			if($isDirectory)
			{
				// Gets subfiles from this subdirectory
				$this->Files($cur, $keepsSubdirectories, $res);

				// If subdirectories are not kept :
				// Ignores it
				if(!$keepsSubdirectories)
				{
					continue;
				}
			}

			// Adds current file to result
			$res[$cur] = $v;
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the last modified date within a directory
	//------------------------------------------------------------------------------------------------------------------
	public function LastModifiedDate(string $dirname) : int
	{
		$res = 0;

		$files = $this->Files($dirname, true);

		foreach($files as $k => $v)
		{
			try
			{
				$cur = filemtime($k);

				if($cur > $res)
				{
					$res = $cur;
				}
			}
			catch(Exception)
			{
				continue;
			}
		}

		return $res;
	}




	//==================================================================================================================
	//
	// DATETIME HELPERS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Gets current date and time
	//------------------------------------------------------------------------------------------------------------------
	public function Now() : string
	{
		return date('YmdHis');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets current date, time and microtime
	//------------------------------------------------------------------------------------------------------------------
	public function MicroNow() : string
	{
		// Gets the decimal part of current microtime
		$microtime   = microtime(true);
		$decimalPart = $microtime - (int)$microtime;
		$microtime   = $decimalPart * 1000000000;

		// Generates the datemicrotime
		return date('YmdHis') . substr($microtime, 0, 8);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets end of time
	//------------------------------------------------------------------------------------------------------------------
	public function EndOfTime() : string
	{
		return '99991231235959';
	}




	//==================================================================================================================
	//
	// REFLECTION HELPERS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Gets arguments from a function
	//------------------------------------------------------------------------------------------------------------------
	public function FunctionArguments(callable $callback) : array
	{
		$reflection = new ReflectionFunction($callback);

		$res = array();
		foreach($reflection->getParameters() as $v)
		{
			$res[$v->name] = $v;
		}

		return $res;
	}




	//==================================================================================================================
	//
	// INTERNET HELPERS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Removes a given argument from a given url
	//------------------------------------------------------------------------------------------------------------------
	public function RemoveArgumentFromUrl(string $url, string $arg) : string
	{
		$res = preg_replace('/(.*)([?]|&)' . $arg . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
		$res = substr($res, 0, -1);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Checks if a domain name is valid
	//------------------------------------------------------------------------------------------------------------------
	public function IsValidDomain(string $domain, string $serverType = '') : bool
	{
		// If "checkdnsrr" function is available :
		// Uses it directly
		if(function_exists('checkdnsrr'))
		{
			if($serverType === '')
			{
				return checkdnsrr($domain);
			}

			return checkdnsrr($domain, $serverType);
		}

		// Otherwise :
		// Tries with an "nslookup" command
		$n = strlen($domain);

		if(empty($serverType))
		{
			$serverType = 'MX';
		}

		exec('nslookup -type=' . $serverType . ' ' . $domain, $res);

		// Checks on each line if it begins with server name
		foreach($res as $v)
		{
			if(substr($v, 0, $n) === $domain)
			{
				return true;
			}
		}

		return false;
	}


	//------------------------------------------------------------------------------------------------------------------
	// // Checks if an email address is valid
	//------------------------------------------------------------------------------------------------------------------
	public function IsValidEmail(string $email) : bool
	{
		// If the first e-mail part (before "@") has not the correct format :
		// Bad e-mail
		if(!preg_match('`^[A-Za-z0-9_][-.A-Za-z0-9_]*@`', $email))
		{
			return false;
		}

		// Gets server name
		$pos    = strpos($email, '@');
		$domain = substr($email, $pos + 1);

		// If server name has not the correct format :
		// Bad e-mail
		if(!preg_match('`^([A-Za-z0-9_][-._A-Za-z0-9]*\.[a-zA-Z]{2,}.*)$`', $domain))
		{
			return false;
		}

		// If a check of PHP e-mail filter does not work :
		// Bad e-mail
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			return false;
		}

		// If MX server check is KO :
		// Bad e-mail
		if(!$this->IsValidDomain($domain, 'MX'))
		{
			return false;
		}

		// Otherwise :
		// Good e-mail
		return true;
	}
}