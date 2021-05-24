<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Html;


use Fewd\Core\AModule;
use Exception;


class THtml extends AModule
{
 	// Moments
	 protected $_Moments = array();

	 // Linked resources
	 private $_Resources = array();
	 public function HasResource(string $id) : bool { return isset($this->_Resources[$id]); }
	 public function AddResource(string $id)        { $this->_Resources[$id] = '';          }

	// LessPhp compiler
	private $_Lessc = null;




	//==================================================================================================================
	//
	// LESS COMPILER
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Compiles a Less file into a Css file, and returns the Css filename
	//------------------------------------------------------------------------------------------------------------------
	public function CompileLess(string $lessFilename, string $cssFilename = '') : string
	{
		// If compiler was not loaded :
		// Loads it
		if($this->_Lessc === null)
		{
			include __DIR__ . '/Vendor/LessPhp/lessc.inc.php';

			$this->_Lessc = new \lessc();
		}

		// If provided path is not a Less file :
		// Does nothing
		if(substr($lessFilename, -5) !== '.less')
		{
			return '';
		}

		// If a Css filename was not provided :
		// Converts the Less filename into a Css filename
		if($cssFilename === '')
		{
			$cssFilename  = substr($lessFilename, 0, -5) . '.css';
		}

		// If CSS file does not exist,
		// Or if LESS file is younger than the CSS file :
		// Compiles it
		if(!is_file($cssFilename) || (filemtime($lessFilename) > filemtime($cssFilename)))
		{
			try
			{
				$this->_Lessc->compileFile($lessFilename, $cssFilename);
			}
			catch(Exception $e)
			{
				return '';
			}
		}

		// Result
		return $cssFilename;
	}




	//==================================================================================================================
	//
	// HTML TAGS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Transforms special characters in entities (&amp; etc.)
	//------------------------------------------------------------------------------------------------------------------
	public function HtmlEntities(string $string) : string
	{
		return htmlentities($string, ENT_QUOTES, 'UTF-8');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Transforms Html to plain text
	//------------------------------------------------------------------------------------------------------------------
	// Example : "<p>Text</p>" => "Text"
	//------------------------------------------------------------------------------------------------------------------
	public function HtmlToPlainText(string $html) : string
	{
		$rules = array(
			'@<script[^>]*?>.*?</script>@si',
			'@<[\/\!]*?[^<>]*?>@si',
			'@([\r\n])[\s]+@',
			'@&(quot|#34);@i',
			'@&(amp|#38);@i',
			'@&(lt|#60);@i',
			'@&(gt|#62);@i',
			'@&(nbsp|#160);@i',
			'@&(iexcl|#161);@i',
			'@&(cent|#162);@i',
			'@&(pound|#163);@i',
			'@&(copy|#169);@i',
			'@&(reg|#174);@i');

		$replace = array(
			'',
			'',
			'',
			'',
			'&',
			'<',
			'>',
			' ',
			chr(161),
			chr(162),
			chr(163),
			chr(169),
			chr(174));

		return preg_replace($rules, $replace, $html);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Escapes double-quotes in a value
	//------------------------------------------------------------------------------------------------------------------
	public function EscapeQuotes(string $value) : string
	{
		return str_replace('"', '&quot;', $value);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Html code for an attribute (with possible protection against quotes in value)
	//------------------------------------------------------------------------------------------------------------------
	public function Attribute(string $key, string $value) : string
	{
		// If attribute is a link attribute (href, src ...) :
		// Transforms its locator into an absolute link (to avoid SEO issues)
		if(($key === 'href') or ($key === 'src'))
		{
			$absoluteUrl = $this->Core()->AbsoluteUrl($value);

			return ' ' . $key . '="' . $absoluteUrl . '"';
		}

		// If attribute does not need to be protected against possible double quotes :
		// Directly returns it
		if(($key === 'target') or ($key === 'class') or ($key === 'type') or ($key === 'media'))
		{
			return ' ' . $key . '="' . $value . '"';
		}

		// Otherwise :
		// Returns an attribute with double-quote protection
		return ' ' . $key . '="' . $this->EscapeQuotes($value) . '"';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Html code for a "class" attribute
	//------------------------------------------------------------------------------------------------------------------
	public function ClassesAttribute($classes) : string
	{
		// If an array was given :
		// Uses values, or keys if values were not provided
		if(is_array($classes))
		{
			$tb = array();

			foreach($classes as $k => $v)
			{
				if($v === '')
				{
					$tb[$k] = $k;
				}
				else
				{
					$tb[$v] = $v;
				}
			}

			$classes = implode(' ', $tb);
		}

		// Result
		if(is_string($classes) && ($classes !== ''))
		{
			return $this->Attribute('class', $classes);
		}

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Html code for integrity and cross origin attributes
	//------------------------------------------------------------------------------------------------------------------
	public function IntegrityAttribute(string $integrity, string $origin) : string
	{
		if(($integrity === '') || ($origin === ''))
		{
			return '';
		}

		$res = ' integrity="' . $integrity . '"';
		$res.= ' crossorigin="' . $origin . '"';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Html code to open a tag
	//------------------------------------------------------------------------------------------------------------------
	// Tag is not closed to permit further addition of other attributes
	//------------------------------------------------------------------------------------------------------------------
	public function OpeningTag(string $tag, array $attributes = array()) : string
	{
		$res = '<' . $tag;

		foreach($attributes as $k => $v)
		{
			$res.= $this->Attribute($k, $v);
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Html code to close a tag
	//------------------------------------------------------------------------------------------------------------------
	public function ClosingTag() : string
	{
		return ' />';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Link ("a") opening tag
 	//------------------------------------------------------------------------------------------------------------------
	// Link can be in format "url>t", "url|d" or "url|d>t", where :
	// - "d" is a "data-dest" attribute
	// - "t" is a "target" attribute
	//------------------------------------------------------------------------------------------------------------------
	public function LinkOpeningTag(string $link, string $title = '', $classes = null) : string
	{
		// Splits URL parts
		$parts = explode('>', $link);
		$link = $parts[0];

		if(isset($parts[1]))
		{
			$target = $this->Attribute('target', $parts[1]);
		}
		else
		{
			$target = '';
		}

		$parts = explode('|', $link);
		$link = $parts[0];

		if(isset($parts[1]))
		{
			$data = $this->Attribute('data-dest', $parts[1]);
		}
		else
		{
			$data = '';
		}

		// Generates the "<a" link
		$link = $this->Core()->AbsoluteUrl($link);

		$res = '<a';
		$res.= $this->Attribute('href', $link) . $target . $data;

		if($title !== '')
		{
			$res.= $this->Attribute('title', $title);
		}

		$res.= $this->ClassesAttribute($classes);

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Link ("a") closing tag
	//------------------------------------------------------------------------------------------------------------------
	public function LinkClosingTag()
	{
		return '</a>';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Link tag
	//------------------------------------------------------------------------------------------------------------------
	public function LinkTag(string $link, string $title = '', $classes = null) : string
	{
		$res = $this->LinkOpeningTag($link, $title, $classes) . '>';
		$res.= $title;
		$res.= $this->LinkClosingTag();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Image opening tag
	//------------------------------------------------------------------------------------------------------------------
	public function ImageOpeningTag(string $link, string $title = '', $classes = null) : string
	{
		$link = $this->Core()->AbsoluteUrl($link);

		$res = '<img';
		$res.= $this->Attribute('src', $link);

		if($title !== '')
		{
			$res.= $this->Attribute('alt', $title);
		}

		$res.= $this->ClassesAttribute($classes);

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Image closing tag
	//------------------------------------------------------------------------------------------------------------------
	public function ImageClosingTag() : string
	{
		return ' />';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Image tag
	//------------------------------------------------------------------------------------------------------------------
	public function ImageTag(string $link, string $title = '', $classes = null) : string
	{
		$res = $this->ImageOpeningTag($link, $title, $classes);
		$res.= $this->ImageClosingTag();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Doctype tag
	//------------------------------------------------------------------------------------------------------------------
	public function DoctypeTag(string $language) : string
	{
		$res = '<!doctype html>';
		$res.= '<html lang="' . $language . '">';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Head-charset tag
	//------------------------------------------------------------------------------------------------------------------
	public function CharsetTag() : string
	{
		$res = '<meta charset="UTF-8" />';
		$res.= '<meta http-equiv="Content-Type" content="text/Html; charset=UTF-8" />';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Title tag
	//------------------------------------------------------------------------------------------------------------------
	public function TitleTag(string $title) : string
	{
		return '<title>' . $title . '</title>';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generator tag
	//------------------------------------------------------------------------------------------------------------------
	public function GeneratorTag(string $generator) : string
	{
		if($generator === '')
		{
			return '';
		}

		return '<meta name="generator"' . $this->Attribute('content', $generator) . ' />';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Robots tag
	//------------------------------------------------------------------------------------------------------------------
	public function RobotsTag(bool $isIndexed) : string
	{
		if($isIndexed)
		{
			return '<meta name="robots" content="all" />';
		}

		return '<meta name="robots" content="noindex" />';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Description tag
	//------------------------------------------------------------------------------------------------------------------
	public function DescriptionTag(string $description) : string
	{
		return '<meta name="description"' . $this->Attribute('content', $description) . ' />';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Viewport tag
	//------------------------------------------------------------------------------------------------------------------
	public function ViewportTag() : string
	{
		return '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Canonical link tag
	//------------------------------------------------------------------------------------------------------------------
	public function CanonicalLinkTag(string $canonicalLink) : string
	{
		$link = $this->Core()->AbsoluteUrl($canonicalLink);

		return '<link rel="canonical" href="' . $link . '" />';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Alternate link tag
	//------------------------------------------------------------------------------------------------------------------
	public function AlternateLinkTag(string $alternateLink, string $language, string $languageName) : string
	{
		$link = $this->Core()->AbsoluteUrl($alternateLink);

		$res = '<link rel="alternate"';
		$res.= ' hreflang="' . $language . '"';
		$res.= ' title="' . $languageName . '"';
		$res.= ' href="' . $link . '"';
		$res.= ' />';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Favicon tag
 	//------------------------------------------------------------------------------------------------------------------
	public function FaviconLinkTag(string $faviconLink, $integrity = '', $origin = '') : string
	{
		$link = $this->Core()->AbsoluteUrl($faviconLink);

		$res = '<link rel="icon"';
		$res.= ' type="image/png"';
		$res.= ' href="' . $link . '"';
		$res.= $this->IntegrityAttribute($integrity, $origin);
		$res.= ' />';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// CSS file tag
	//------------------------------------------------------------------------------------------------------------------
	public function CssLinkTag(string $link, string $media = '', $integrity = '', $origin = '') : string
	{
		// If no media provided :
		// Css file is convenient for all media
		if($media === '')
		{
			$media = 'all';
		}

		// Ensures that link is absolute
		$link = $this->Core()->AbsoluteUrl($link);

		// Result
		$res = '<link rel="stylesheet"';
		$res.= ' type="text/css"';
		$res.= ' media="' . $media . '"';
		$res.= ' href="'  . $link  . '"';
		$res.= $this->IntegrityAttribute($integrity, $origin);
		$res.= ' />';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// JS file tag
	//------------------------------------------------------------------------------------------------------------------
	public function JsLinkTag(string $link, $integrity = '', $origin = '') : string
	{
		// Ensures that link is absolute
		$link = $this->Core()->AbsoluteUrl($link);

		// Result
		$res = '<script type="text/javascript"';
		$res.= ' src="' . $link . '"';
		$res.= $this->IntegrityAttribute($integrity, $origin);
		$res.= '></script>';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// CSS inline script tag
	//------------------------------------------------------------------------------------------------------------------
	public function CssScriptTag(string $script) : string
	{
		return '<style type="text/css">' . $script . '</style>';
	}


	//------------------------------------------------------------------------------------------------------------------
	// JS inline script tag
	//------------------------------------------------------------------------------------------------------------------
	public function JsScriptTag(string $script) : string
	{
		return '<script type="text/javascript">' . $script . '</script>';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Html document header opening tag
	//------------------------------------------------------------------------------------------------------------------
	public function DocumentHeaderOpeningTag(
		string $language,
		string $title,
		string $description,
		bool   $isIndexed) : string
	{
		$res = $this->DoctypeTag($language);
		$res.= '<head ' . $this->Attribute('title', $title) . '>';
		$res.= $this->CharsetTag();
		$res.= $this->TitleTag($title);
		$res.= $this->RobotsTag($isIndexed);
		$res.= $this->DescriptionTag($description);
		$res.= $this->ViewportTag();

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Html document header closing tag
	//------------------------------------------------------------------------------------------------------------------
	public function DocumentHeaderClosingTag()
	{
		return '</head>';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Html document body opening tag
	//------------------------------------------------------------------------------------------------------------------
	public function DocumentBodyOpeningTag()
	{
		return '<body>';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Html document body closing tag
	//------------------------------------------------------------------------------------------------------------------
	public function DocumentBodyClosingTag()
	{
		return '</body>';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Headline tag
	//------------------------------------------------------------------------------------------------------------------
	public function HeadlineTag(
		string $title,
		int    $level = 1,
		string $icon  = '',
		string $class = '') : string
	{
		if($level < 1)
		{
			$level = 1;
		}

		if($level > 6)
		{
			$level = 6;
		}

		$res = '<h' . $level;

		if($class !== '')
		{
			$res.= ' class="' . $class . '"';
		}

		$res.= '>';

		if($icon !== '')
		{
			$res.= '<i class="' . $icon . '"></i>';
		}

		$res.= $title;

		$res.= '</h1>';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Paragraph tag
	//------------------------------------------------------------------------------------------------------------------
	public function ParagraphTag(string $text, string $class = '') : string
	{
		$res = '<p';

		if($class !== '')
		{
			$res.= ' class="' . $class . '"';
		}

		$res.= '>' . $text . '</p>';

		return $res;
	}




	//==================================================================================================================
	//
	// MOMENTS
	//
	//==================================================================================================================


	//------------------------------------------------------------------------------------------------------------------
	// Records a given function callback for a given moment
	//------------------------------------------------------------------------------------------------------------------
	public function RecordCallback(callable $callback, string $moment = '')
	{
		if($moment === '')
		{
			$moment = 'Body';
		}

		if(!isset($this->_Moments[$moment]))
		{
			$this->_Moments[$moment] = array();
		}

		$this->_Moments[$moment][] = $callback;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given content for a given moment
	//------------------------------------------------------------------------------------------------------------------
	public function RecordContent(string $content, string $moment = '')
	{
		$this->RecordCallback(function() use($content)
		{
			return $content;
		}, $moment);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given component to run at a given moment
	//------------------------------------------------------------------------------------------------------------------
	public function RecordComponent(AComponent $component, string $moment = '')
	{
		$this->RecordCallback(function() use($component)
		{
			return $component->Run();
		}, $moment);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given css link
	//------------------------------------------------------------------------------------------------------------------
	public function RecordCssLink(
		string $link,
		string $media     = '',
		string $integrity = '',
		string $origin    = '')
	{
		if($this->HasResource($link))
		{
			return;
		}

		$this->AddResource($link);

		$src = $this->CssLinkTag($link, $media, $integrity, $origin);

		$this->RecordContent($src, 'LoadCss');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given js link
	//------------------------------------------------------------------------------------------------------------------
	public function RecordJsLink(
		string $link,
		string $integrity = '',
		string $origin    = '')
	{
		if($this->HasResource($link))
		{
			return;
		}

		$this->AddResource($link);

		$src = $this->JsLinkTag($link, $integrity, $origin);

		$this->RecordContent($src, 'LoadJs');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given js link declared lately
	//------------------------------------------------------------------------------------------------------------------
	public function RecordLateJsLink(
		string $link,
		string $integrity = '',
		string $origin    = '')
	{
		if($this->HasResource($link))
		{
			return;
		}

		$this->AddResource($link);

		$src = $this->JsLinkTag($link, $integrity, $origin);

		$this->RecordContent($src, 'LoadLateJs');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Record a given css script
	//------------------------------------------------------------------------------------------------------------------
	public function RecordCssScript(string $script)
	{
		$src = $this->CssScriptTag($script);

		$this->RecordContent($src, 'LoadCss');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given js script
	//------------------------------------------------------------------------------------------------------------------
	public function RecordJsScript(string $script)
	{
		$src = $this->JsScriptTag($script);

		$this->RecordContent($src, 'LoadJs');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given js script declared lately
	//------------------------------------------------------------------------------------------------------------------
	public function RecordLateJsScript(string $script)
	{
		$src = $this->JsScriptTag($script);

		$this->RecordContent($src, 'LoadLateJs');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a given moment
	//------------------------------------------------------------------------------------------------------------------
	public function RunMoment(
		string $moment,
		string $openingTag = '',
		string $closingTag = '') : string
	{
		// If nothing attached to the given moment :
		// Returns nothing
		if(!isset($this->_Moments[$moment]))
		{
			return '';
		}

		// For each attachment to the given moment :
		// Adds its result to global result
		$res = '';

		foreach($this->_Moments[$moment] as $v)
		{
			$call = call_user_func($v, $this);

			if(is_string($call) && ($call !== ''))
			{
				$res.= $call;
			}
		}

		// If a result was found,
		// And if opening and closing tag where provided :
		// Adds them
		if(($res !== '') && ($openingTag !== '') && ($closingTag !== ''))
		{
			$res = $openingTag . $res . $closingTag;
		}

		// Result
		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates the whole Html output
	//------------------------------------------------------------------------------------------------------------------
	public function Run() : string
	{
		// Init
		$res = $this->RunMoment('Init');

		// Header
		$res.= $this->DocumentHeaderOpeningTag('fr', 'TITLE', 'DESCRIPTION', false);

		$res.= $this->RunMoment('LoadCss');
		$res.= $this->RunMoment('LoadJs');

		$res.= $this->DocumentHeaderClosingTag();

		// Body
		$res.= $this->DocumentBodyOpeningTag();

		$res.= $this->RunMoment('BeforeBody');

		$res.= $this->RunMoment('Body');

		$res.= $this->RunMoment('AfterBody');
		$res.= $this->RunMoment('LoadLateJs');

		$res.= $this->DocumentBodyClosingTag();

		// Result
		$res.= '</html>';

		return $res;
	}
}