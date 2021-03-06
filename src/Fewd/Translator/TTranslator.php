<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Translator;


use Fewd\Core\AModule;
use Fewd\Core\TCore;
use Exception;


class TTranslator extends AModule
{
	// Browser cultures
	private $_BrowserCultures = array();
	public final function BrowserCultures() : array { return $this->_BrowserCultures; }

	// Default culture
	private $_DefaultCulture;
	public final function DefaultCulture() : string { return $this->_DefaultCulture; }

	// Current culture
	private $_Culture;
	public final function Culture() : string { return $this->_Culture; }

	// Dictionaries
	protected $_Dictionaries = array();

	// Translations
	protected $_Translations = array();


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(TCore $core, string $defaultCulture)
	{
		parent::__construct($core);

		$this->_DefaultCulture = $defaultCulture;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->_BrowserCultures = $this->DefineBrowserCultures();
		$this->_DefaultCulture  = $this->DefineDefaultCulture();
		$this->_Culture         = $this->DefineCulture();

		$this->RecordDictionary(__DIR__ . '/Dict');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Browser cultures
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineBrowserCultures() : array
	{
		// If no culture determined by the browser :
		// Returns nothing
		$cultures = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

		if($cultures === '')
		{
			return array();
		}

		// Gets possible cultures
		$parts = explode(",", $cultures);
		$res   = array();
		foreach ($parts as $v)
		{
			// Gets quality value ("q=" value)
			if(preg_match('/(.*);q=([0-1]{0,1}.\d{0,4})/i', $v, $matches))
			{
				$res[$matches[1]] = (float)$matches[2];
			}
			else
			{
                $res[$v] = 1.0;
            }
		}

		// Sorts by quality
		arsort($res);
		$res = array_keys($res);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Default culture
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineDefaultCulture() : string
	{
		if($this->DefaultCulture() === '')
		{
			return 'en';
		}

		return $this->DefaultCulture();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Define : Culture
	//------------------------------------------------------------------------------------------------------------------
	protected function DefineCulture() : string
	{
		// If browser indicates some cultures :
		// The current culture is by default the preferred culture (i.e. the first one)
		$cultures = $this->BrowserCultures();

		foreach($cultures as $v)
		{
			return $v;
		}

		// Otherwise :
		// Uses the default culture
		return $this->DefaultCulture();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Sets culture
	//------------------------------------------------------------------------------------------------------------------
	public function SetCulture(string $culture)
	{
		if($culture === '')
		{
			$culture = $this->DefaultCulture();
		}

		$this->_Culture = $culture;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets neutral culture corresponding to a given culture
	//------------------------------------------------------------------------------------------------------------------
	public function NeutralCulture(string $culture) : string
	{
		$pos = strpos($culture, '-');
		if($pos === false)
		{
			return $culture;
		}

		return substr($culture, 0, $pos);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a dictionary
	//------------------------------------------------------------------------------------------------------------------
	public function RecordDictionary(string $path)
	{
		// Dictionary must be an absolute filename
		$path = $this->Core()->AbsoluteFilename($path);

		// If dictionary is already recorded :
		// Does nothing
		if(isset($this->_Dictionaries[$path]))
		{
			return;
		}

		// Records the new dictionary
		$this->_Dictionaries[$path] = 0;

		// Already loaded cultures must be completed with the new dictionary
		foreach($this->_Translations as $k => $v)
		{
			$fewd       = &$this->_Translations[$k];
			$dictionary = $this->Core()->Join($path, $k . '.php');
			if(file_exists($dictionary))
			{
				try
				{
					include_once $dictionary;
				}
				catch(Exception $e)
				{
					$this->Nop($e);
				}
			}
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Indicates if a given culture has been already loaded
	//------------------------------------------------------------------------------------------------------------------
	public function IsLoaded(string $culture) : bool
	{
		return isset($this->_Translations[$culture]);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Loads a given culture
	//------------------------------------------------------------------------------------------------------------------
	public function Load(string $culture)
	{
		// A culture cannot be re-loaded
		if($this->IsLoaded($culture))
		{
			return;
		}

		// Initiates the translations array for the given culture
		$fewd = array();

		// Loads each dictionary for the given culture
		foreach($this->_Dictionaries as $k => $v)
		{
			$dictionary = $this->Core()->Join($k, $culture . '.php');
			if(file_exists($dictionary))
			{
				try
				{
					include_once $dictionary;
				}
				catch(Exception $e)
				{
					$this->Nop($e);
				}
			}
		}

		$this->_Translations[$culture] = &$fewd;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Learns a translation in a given culture
	//------------------------------------------------------------------------------------------------------------------
	public function Learn(string $code, string $culture, string $translation)
	{
		$this->_Translations[$culture][$code] = $translation;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Searches the translation in a given culture or another culture
	//------------------------------------------------------------------------------------------------------------------
	protected function Translation(
		string $code,
		string $culture,
		array  $culturesToTry,
		array  $replacements,
		array  $triedCultures = array(),
		int $index = 0) : string
	{

		// If all the possible values of culture are not already tried :
		// Continues to try
		if(count($triedCultures) < count($culturesToTry))
		{
			// If the actual culture isn't already tried :
			// Tries it
			if(!in_array($culture, $triedCultures))
			{
				// If culture was not loaded :
				// Loads it
				if($this->IsLoaded($culture) === false)
				{
					$this->Load($culture);
				}

				// If expected translation is known :
				// Returns it
				if(array_key_exists($culture, $this->_Translations))
				{
					if(array_key_exists($code, $this->_Translations[$culture]))
					{
						$res = $this->_Translations[$culture][$code];

						foreach($replacements as $k => $v)
						{
							$res = str_replace('{{' . $k . '}}', $v, $res);
						}

						return $res;
					}
				}
			}

			// Or tries to translate with another culture
			array_push($triedCultures, $culture);
			$culture = $culturesToTry[$index];
			$index += 1;

			return $this->Translation($code, $culture, $culturesToTry, $replacements, $triedCultures, $index);
		}

		// Or returns the code itself
		else
		{
			return '[[' . $code . ']]';
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets a translation in a given culture
	//------------------------------------------------------------------------------------------------------------------
	public function Translate(string $code, string $culture, array $replacements = array()) : string
	{
		// Define all the possible values of culture
		$neutralCulture        = $this->NeutralCulture($culture);
		$defaultCulture        = $this->DefaultCulture();
		$neutralDefaultCulture = $this->NeutralCulture($defaultCulture);

		// Group all the possible values of culture in array
		$culturesToTry = [$neutralCulture, $defaultCulture, $neutralDefaultCulture];

		// Search the translation and return the r??sult
		$res = $this->Translation($code, $culture, $culturesToTry, $replacements);

		return $res;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Says something in the current culture
	//------------------------------------------------------------------------------------------------------------------
	public function Say(string $code, array $replacements = array()) : string
	{
		return $this->Translate($code, $this->Culture(), $replacements);
	}
}