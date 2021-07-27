<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------
// A **router** routes a given url (i.e. path + arguments) to the corresponding action.
//
// Thanks to a router, it is possible to share the same action by many urls. For example :
// `product.php?id=<xyz>`, `prod-<xyz>.php`, `<permalink>.php`... lead to the same unique product display.
//
// To achieve in this goal, the router uses **rules** and **actions**.
//
// A **rule** is a lambda function (callback) that turns a certain type of urls into a **route**.
// A **route** is a code (**route id**) + a query string composed by url args + some other args derived from the url.
// There are some helpers to implement the most basic rules : regexp transcoding, root transcoding...
//
// An **action** is a lambda function (callback) associated to a **route id**. The callback takes the route
// arguments + a http verb.
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Router;


use Fewd\Core\AModule;


class TRouter extends AModule
{
	// Rules
	private $_Rules = array();
    public final function Rules() { return $this->_Rules; }
	public       function AddRule(callable $callback) { $this->_Rules[] = $callback; }

	// Actions
	private $_Actions = array();
	public final function Actions()                                      { return $this->_Actions; }
	public final function Action(   string $routeId) : callable          { return $this->_Actions[$routeId] ?? null; }
	public final function HasAction(string $routeId) : bool              { return isset($this->_Actions[$routeId]); }
	public       function AddAction(string $routeId, callable $callback) { $this->_Actions[$routeId] = $callback; }


	//------------------------------------------------------------------------------------------------------------------
	// Adds a strict rule
	//------------------------------------------------------------------------------------------------------------------
	public function AddStrictRule(string $strict, string $routeId)
	{
		$strict = $this->Core()->ToLower($strict);

		$callback = function(string $path, array $args) use($strict, $routeId) : string
		{
			$this->Nop($args);

			if($path === $strict)
			{
				return $routeId;
			}

			return '';
		};

		$this->AddRule($callback);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a regexp rule
	//------------------------------------------------------------------------------------------------------------------
	public function AddRegexpRule(string $regexp, string $routeId, array|string $idName = '')
	{
		$regexp = '#' . $regexp . '#';

		$callback = function(string $path, array $args) use($regexp, $routeId, $idName) : string
		{
			if(preg_match($regexp, $path, $matches) === 1)
			{
				if(is_string($idName))
				{
					if($idName === '')
					{
						$idName = array();
					}
					else
					{
						$idName = array($idName);
					}
				}

				foreach($idName as $k => $v)
				{
					if(isset($matches[$k + 1]))
					{
						$args[$v] = $matches[$k + 1];
					}
				}

				$res = $routeId;

				if(!empty($args))
				{
					$res.= '?' . http_build_query($args);
				}

				return $res;
			}

			return '';
		};

		$this->AddRule($callback);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a root rule
	//------------------------------------------------------------------------------------------------------------------
	public function AddRootRule(string $root, string $routeId, string $idName = '')
	{
		$root = $this->Core()->ToLower($root);

		$callback = function(string $path, array $args) use($root, $routeId, $idName) : string
		{
			if($this->Core()->StartsWith($path, $root))
			{
				if($idName !== '')
				{
					$arg = substr($path, strlen($root));
					$args[$idName] = $arg;
				}

				$res = $routeId;

				if(!empty($args))
				{
					$res.= '?' . http_build_query($args);
				}

				return $res;
			}

			return '';
		};

		$this->AddRule($callback);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Adds a shape rule (shape = start + end)
	//------------------------------------------------------------------------------------------------------------------
	public function AddShapeRule(string $start, string $end, string $routeId, string $idName = '')
	{
		$start = $this->Core()->ToLower($start);
		$end   = $this->Core()->ToLower($end  );

		$callback = function(string $path, array $args) use($start, $end, $routeId, $idName) : string
		{
			if($this->Core()->StartsWith($path, $start) && $this->Core()->EndsWith($path, $end))
			{
				if($idName !== '')
				{
					$arg = substr($path, strlen($start), -strlen($end));
					$args[$idName] = $arg;
				}

				$res = $routeId;

				if(!empty($args))
				{
					$res.= '?' . http_build_query($args);
				}

				return $res;
			}

			return '';
		};

		$this->AddRule($callback);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the HTTP message corresponding to a given code
	//------------------------------------------------------------------------------------------------------------------
	public function Message(int $code) : string
	{
		// The list is ordered depending on usage frequency (most frequent codes are first)
		switch($code)
		{
			case 200 : return 'OK';
			case 201 : return 'Created';
			case 202 : return 'Accepted';
			case 203 : return 'Non-Authoritative Information';
			case 204 : return 'No Content';
			case 205 : return 'Reset Content';
			case 206 : return 'Partial Content';
			case 207 : return 'Multi-Status';
			case 208 : return 'Multi-Status';
			case 226 : return 'IM Used';
			case 400 : return 'Bad Request';
			case 401 : return 'Unauthorized';
			case 402 : return 'Payment Required';
			case 403 : return 'Forbidden';
			case 404 : return 'Not Found';
			case 405 : return 'Method Not Allowed';
			case 406 : return 'Not Acceptable';
			case 407 : return 'Proxy Authentication Required';
			case 408 : return 'Request Timeout';
			case 409 : return 'Conflict';
			case 410 : return 'Gone';
			case 411 : return 'Length Required';
			case 412 : return 'Precondition Failed';
			case 413 : return 'Payload Too Large';
			case 414 : return 'URI Too Long';
			case 415 : return 'Unsupported Media Type';
			case 416 : return 'Requested Range Not Satisfiable';
			case 417 : return 'Expectation Failed';
			case 418 : return 'I\'m a teapot';
			case 421 : return 'Misdirected Request';
			case 422 : return 'Unprocessable Entity';
			case 423 : return 'Locked';
			case 424 : return 'Failed Dependency';
			case 426 : return 'Upgrade Required';
			case 428 : return 'Precondition Required';
			case 429 : return 'Too Many Requests';
			case 431 : return 'Request Header Fields Too Large';
			case 451 : return 'Unavailable For Legal Reasons';
			case 500 : return 'Internal Server Error';
			case 501 : return 'Not Implemented';
			case 502 : return 'Bad Gateway';
			case 503 : return 'Service Unavailable';
			case 504 : return 'Gateway Timeout';
			case 505 : return 'HTTP Version Not Supported';
			case 506 : return 'Variant Also Negotiates';
			case 507 : return 'Insufficiant Storage';
			case 508 : return 'Loop Detected';
			case 510 : return 'Not Extended';
			case 511 : return 'Network Authentication Required';
			case 300 : return 'Multiple Choice';
			case 301 : return 'Moved Permanently';
			case 302 : return 'Found';
			case 303 : return 'See Other';
			case 304 : return 'Not Modified';
			case 305 : return 'Use Proxy';
			case 306 : return 'unused';
			case 307 : return 'Temporary Redirect';
			case 308 : return 'Permanent Redirect';
			case 100 : return 'Continue';
			case 101 : return 'Switching Protocol';
			case 103 : return 'Processing';
		}

		return 'Not found';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Gets the HTTP code + message corresponding to a given code
	//------------------------------------------------------------------------------------------------------------------
	public function CodeMessage(int $code) : string
	{
		return $code . ' ' . $this->Message($code);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates a header with a given HTTP code
	//------------------------------------------------------------------------------------------------------------------
	public function Header(int $code)
	{
		header('HTTP/1.1 ' . $this->CodeMessage($code));
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
	// Gets the route corresponding to a given url path + url arguments
	//------------------------------------------------------------------------------------------------------------------
	public function Route(string $path, array $args) : string
	{
		foreach($this->Rules() as $v)
		{
			$res = call_user_func($v, $path, $args);

			if($res !== '')
			{
				return $res;
			}
		}

		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a given route
	//------------------------------------------------------------------------------------------------------------------
	public function RunRoute(string $route, string $verb)
	{
		// Splits route id and query string
		$routeId     = $route;
		$queryString = '';

		$pos = strpos($route, '?');

		if($pos !== false)
		{
			$routeId     = substr($route, 0, $pos);
			$queryString = substr($route, $pos + 1);
		}

		// If route id is known :
		// Runs action
		if($this->HasAction($routeId))
		{
			$action = $this->Action($routeId);
			$args   = array();

			parse_str($queryString, $args);

			call_user_func($action, $args, $verb);
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
		$route = $this->Route($this->Core()->CurrentPath(), $this->Core()->CurrentArgs());

		$this->RunRoute($route, $this->Core()->CurrentVerb());
	}
}
