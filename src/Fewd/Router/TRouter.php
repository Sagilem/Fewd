<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------
// A **router** routes a given url + Http verb to the corresponding treatment.
//
// Thanks to a router, it is possible to share the same treatment by many urls/verbs. For example :
// associate `product.php?id=<xyz>`, `prod-<xyz>.php`, `<permalink>.php`... to the same unique product display.
// It is also possible to route different Http verbs to the same resource.
//
// To achieve this goal, the router uses **rules** and **routes**.
//
// A rule is a lambda function that returns the **route id** corresponding to a given url + verb.
// The router brings some helpers to implement the most basic rules : regexp transcoding, root transcoding...
// Rule callback arguments : a url, an array of arguments and a verb.
// Rule result : a route id, that is a route code sometimes completed by a query string of identification arguments
//
// A route associates a route id to a treatment (callback).
// Route callback arguments : an array of identification arguments
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Router;


use Fewd\Core\AModule;


class TRouter extends AModule
{
	// Rules
	protected $_Rules = array();
    public final function Rules()                                  { return $this->_Rules;               }
	public       function AddRule(callable $callback)              { $this->_Rules[] = $callback;        }

	// Routes
	private $_Routes = array();
	public final function Routes()                                 { return $this->_Routes;              }
	public       function Route(   string $id) : callable          { return $this->_Routes[$id] ?? null; }
	public       function HasRoute(string $id) : bool              { return isset($this->_Routes[$id]);  }
	public       function AddRoute(string $id, callable $callback) { $this->_Routes[$id] = $callback;    }


	//------------------------------------------------------------------------------------------------------------------
	// Adds a strict rule
	//------------------------------------------------------------------------------------------------------------------
	public function AddStrictRule(string $strict, string $routeId)
	{
		$strict = $this->Core()->ToLower($strict);

		$this->AddRule(function(string $url, array $args, string $verb) use($strict, $routeId) : string
		{
			$this->Nop($args);
			$this->Nop($verb);

			if($url === $strict)
			{
				return $routeId;
			}

			return '';
		});
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a regexp rule
	//------------------------------------------------------------------------------------------------------------------
	public function AddRegexpRule(string $regexp, string $routeId, string $idName = '')
	{
		if(substr($regexp, 0, 1) !== substr($regexp, -1))
		{
			$regexp = '/' . $regexp . '/';
		}

		$this->AddRule(function(string $url, array $args, string $verb) use($regexp, $routeId, $idName) : string
		{
			$this->Nop($args);
			$this->Nop($verb);

			if(preg_match($regexp, $url, $matches) === 1)
			{
				if(($idName === '') || !isset($matches[1]))
				{
					return $routeId;
				}

				$arg = $this->Core()->ToLower($matches[1]);

				return $routeId . '?' . $idName . '=' . urlencode($arg);
			}

			return '';
		});
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a root rule
	//------------------------------------------------------------------------------------------------------------------
	public function AddRootRule(string $root, string $routeId, string $idName = '')
	{
		$root = $this->Core()->ToLower($root);

		$this->AddRule(function(string $url, array $args, string $verb) use($root, $routeId, $idName) : string
		{
			$this->Nop($args);
			$this->Nop($verb);

			if($this->Core()->StartsWith($url, $root))
			{
				if($idName === '')
				{
					return $routeId;
				}

				$arg = substr($url, strlen($root));

				return $routeId . '?' . $idName . '=' . urlencode($arg);
			}

			return '';
		});
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a shape rule (shape = start + end)
	//------------------------------------------------------------------------------------------------------------------
	public function AddShapeRule(string $start, string $end, string $routeId, string $idName = '')
	{
		$start = $this->Core()->ToLower($start);
		$end   = $this->Core()->ToLower($end  );

		$this->AddRule(function(string $url, array $args, string $verb)
		    use($start, $end, $routeId, $idName) : string
		{
			$this->Nop($args);
			$this->Nop($verb);

			if($this->Core()->StartsWith($url, $start) && $this->Core()->EndsWith($url, $end))
			{
				if($idName === '')
				{
					return $routeId;
				}

				$arg = substr($url, strlen($start), -strlen($end));

				return $routeId . '?' . $idName . '=' . urlencode($arg);
			}

			return '';
		});
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the HTTP message corresponding to a given code
	//------------------------------------------------------------------------------------------------------------------
	public function Message(int $code)
	{
		switch($code)
		{
			case 200 : return 'OK';
			case 201 : return 'Created';
			case 202 : return 'Accepted';
			case 204 : return 'No Content';
			case 400 : return 'Bad Request';
			case 401 : return 'Unauthorized';
			case 403 : return 'Forbidden';
			case 404 : return 'Not found';
			case 405 : return 'Method Not Allowed';
			case 415 : return 'Unsupported Media Type';
			case 422 : return 'Unprocessable Entity';
			case 500 : return 'Internal Server Error';
			case 503 : return 'Service Unavailable';
			case 504 : return 'Gateway Timeout';
		}

		return '404 Not found';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates a header with a given HTTP code
	//------------------------------------------------------------------------------------------------------------------
	public function Header(int $code)
	{
		header('HTTP/1.1 ' . $code . ' ' . $this->Message($code));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the stylesheet corresponding to a given error page
	//------------------------------------------------------------------------------------------------------------------
	protected function ErrorStylesheet(int $code) : string
	{
		// Code is not used here, but it could be used in an overriden method
		$this->Nop($code);

		// Gets the error stylesheet link
		$dir = $this->Core()->RelativeLink(__DIR__);

		return $dir . '/Css/RouterErrors.css';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the error page title
	//------------------------------------------------------------------------------------------------------------------
	protected function ErrorTitle(int $code) : string
	{
		return $this->Message($code);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the error page message
	//------------------------------------------------------------------------------------------------------------------
	protected function ErrorMessage(int $code) : string
	{
		$res = $this->Message($code);

		$url = $this->Core()->CurrentRelativeUrl();

		if($url === '')
		{
			$res.= '.';
		}
		else
		{
			$res.= ' : <pre>' . $url . '</pre>';
		}

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the error return link
	//------------------------------------------------------------------------------------------------------------------
	protected function ErrorReturn(int $code) : string
	{
		$this->Nop($code);

		$url = $this->Core()->Home();

        if ($url === $this->Core()->CurrentAbsoluteUrl())
		{
            return '';
        }

		return 'Return to <a href="' . $url . '" title="' . $url . '">' . $url . '</a>';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the html for a Http error page
	//------------------------------------------------------------------------------------------------------------------
	protected function ErrorPage(int $code) : string
	{
		$stylesheet = $this->Core()->AbsoluteUrl($this->ErrorStylesheet($code));

		$res = '<!doctype html>';
		$res.= '<html>';
		$res.= '<head>';
		$res.= '<meta charset="utf-8">';
		$res.= '<title>' . $this->ErrorTitle($code) . '</title>';
		$res.= '<link rel="stylesheet" type="text/css" href="' . $stylesheet . '" />';
		$res.= '</head>';
		$res.= '<body>';
		$res.= '<div class="error-container error-' . $code . '">';
		$res.= '<h1 class="error-code">' . $code . '</h1>';
		$res.= '<p class="error-message">' . $this->ErrorMessage($code) . '</p>';
		$res.= '<p class="error-return">' . $this->errorReturn($code) . '</p>';
		$res.= '</div>';
		$res.= '</body>';
		$res.= '</html>';

		return $res;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Redirects to an error page
	//------------------------------------------------------------------------------------------------------------------
	public function RedirectError(int $code)
	{
        $this->Header($code);

        echo $this->ErrorPage($code);
		die();
    }


	//------------------------------------------------------------------------------------------------------------------
	// Redirects to 404 error page
	//------------------------------------------------------------------------------------------------------------------
	public function Redirect404()
	{
		$this->RedirectError(404);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Redirects permanently to a given Url
	//------------------------------------------------------------------------------------------------------------------
	public function Redirect301(string $url)
	{
		$url = $this->Core()->AbsoluteUrl($url);

		header('HTTP/1.1 301 Moved Permanently', true, 301);
		header('Location: ' . $url);
		die();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Redirects temporary to a given Url
	//------------------------------------------------------------------------------------------------------------------
	public function Redirect302(string $url)
	{
		$url = $this->Core()->AbsoluteUrl($url);

		header('Status: 302 Moved Temporarily', true, 302);
		header('Location: ' . $url);
		die();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the route id corresponding to a given url / args / verb
	//------------------------------------------------------------------------------------------------------------------
	public function RouteId(string $url, string $verb = '') : string
	{
		// Url must me in lowercase to be correctly scanned
		$url = $this->Core()->ToLower($url);

		// Inits arguments
		$args = array();

		// Splits url from its arguments
		$pos = strpos($url, '?');

		if($pos !== false)
		{
			$queryString = substr($url, $pos + 1);
			$url         = substr($url, 0, $pos);

			parse_str($queryString, $args);
		}

		// For each rule :
		// Returns the first found route id
		foreach($this->_Rules as $v)
		{
			$res = call_user_func($v, $url, $args, $verb);

			if($res !== '')
			{
				return $res;
			}
		}

		// No rule found :
		// Returns an empty route id
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a given route
	//------------------------------------------------------------------------------------------------------------------
	public function RunRoute(string $routeId)
	{
		// Splits route id from arguments given as a query string
		$queryString = '';
		$pos         = strpos($routeId, '?');

		if($pos !== false)
		{
			$queryString = substr($routeId, $pos + 1);
			$routeId     = substr($routeId, 0, $pos);
		}

		// If route is known :
		// Runs route
		if($this->HasRoute($routeId))
		{
			$route = $this->Route($routeId);
			$args  = array();

			parse_str($queryString, $args);

			call_user_func($route, $args);
		}

		// Otherwise :
		// Redirects to a 404 error
		else
		{
			$this->Redirect404();
		}
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs route for the current url / verb
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		$url     = $this->Core()->CurrentRelativeUrl();
		$verb    = $this->Core()->CurrentVerb();

		$routeId = $this->RouteId($url, $verb);

		$this->RunRoute($routeId);
	}
}
