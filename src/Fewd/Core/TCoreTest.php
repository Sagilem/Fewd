<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Core;


class TCoreTest extends ATest
{
	//------------------------------------------------------------------------------------------------------------------
	// Runs test on string helpers
	//------------------------------------------------------------------------------------------------------------------
	protected function RunStrings(TCore $core)
	{
		$this->CheckTrue( $core->StartsWith('abcdef', 'abc'   ));
		$this->CheckFalse($core->StartsWith('abcdef', 'abd'   ));
		$this->CheckTrue( $core->StartsWith('abcdef', ''      ));
		$this->CheckFalse($core->StartsWith('abcdef', ' '     ));
		$this->CheckFalse($core->StartsWith('abc'   , 'abcdef'));
		$this->CheckTrue( $core->StartsWith('abc'   , 'abc'   ));
		$this->CheckFalse($core->StartsWith(''      , 'a'     ));
		$this->CheckTrue( $core->StartsWith(''      , ''      ));

		$this->CheckTrue( $core->EndsWith('abcdef', 'def'   ));
		$this->CheckFalse($core->EndsWith('abcdef', 'cef'   ));
		$this->CheckTrue( $core->EndsWith('abcdef', ''      ));
		$this->CheckFalse($core->EndsWith('abcdef', ' '     ));
		$this->CheckFalse($core->EndsWith('abc'   , 'abcdef'));
		$this->CheckTrue( $core->EndsWith('abc'   , 'abc'   ));
		$this->CheckFalse($core->EndsWith(''      , 'a'     ));
		$this->CheckTrue( $core->EndsWith(''      , ''      ));

		$this->CheckTrue( $core->Contains('abcdef', 'abc'   ));
		$this->CheckTrue( $core->Contains('abcdef', 'cde'   ));
		$this->CheckTrue( $core->Contains('abcdef', 'def'   ));
		$this->CheckFalse($core->Contains('abcdef', 'xyz'   ));
		$this->CheckTrue( $core->Contains('abcdef', ''      ));
		$this->CheckTrue( $core->Contains('abc'   , 'abc'   ));
		$this->CheckFalse($core->Contains(''      , 'a'     ));
		$this->CheckTrue( $core->Contains(''      , ''      ));

		$this->Check($core->ToUpper('my test'), 'MY TEST');
		$this->Check($core->ToUpper('MY TEST'), 'MY TEST');
		$this->Check($core->ToUpper('éèê'    ), 'ÉÈÊ'    );
		$this->Check($core->ToUpper(''       ), ''       );
		$this->Check($core->ToUpper('  '     ), '  '     );

		$this->Check($core->ToLower('MY TEST'), 'my test');
		$this->Check($core->ToLower('my test'), 'my test');
		$this->Check($core->ToLower('ÉÈÊ'    ), 'éèê'    );
		$this->Check($core->ToLower(''       ), ''       );
		$this->Check($core->ToLower('  '     ), '  '     );

		$this->Check($core->WithoutAccents('éàoiÉ ÈÊ$'), 'eaoiE EEUSD');
		$this->Check($core->WithoutAccents(''         ), ''           );
		$this->Check($core->WithoutAccents('  '       ), '  '         );

		$this->Check($core->ToAscii('éà__oiÉ ÈÊ$'             ), 'ea-oie-eeusd');
		$this->Check($core->ToAscii('éà__oiÉ ÈÊ$'        , ' '), 'ea oie eeusd');
		$this->Check($core->ToAscii(' -  éà__oiÉ ÈÊ$ _--'     ), 'ea-oie-eeusd');
		$this->Check($core->ToAscii(' -  éà__oiÉ ÈÊ$ _--', '' ), 'eaoieeeusd'  );

		$this->Check($core->HtmlEntities('<ph p>'), '&lt;ph p&gt;');
		$this->Check($core->HtmlEntities(''      ), ''            );
		$this->Check($core->HtmlEntities('  '    ), '  '          );
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs test on identifiers helpers
	//------------------------------------------------------------------------------------------------------------------
	protected function RunIdentifiers(TCore $core)
	{
		$this->CheckMatch($core->UniqueId(          ), '^[0-9a-f]{4}[-][0-9a-f]{4}[-][0-9a-f]{4}[-][0-9a-f]{4}$');
		$this->CheckMatch($core->UniqueId(11        ), '^[0-9a-f]{4}[-][0-9a-f]{4}[-][0-9a-f]{3}$');
		$this->CheckMatch($core->UniqueId(14, '_', 5), '^[0-9a-f]{5}[_][0-9a-f]{5}[_][0-9a-f]{4}$');
		$this->CheckMatch($core->UniqueId(10, '' , 4), '^[0-9a-f]{10}$');
		$this->CheckMatch($core->UniqueId(10, '-', 0), '^[0-9a-f]{10}$');
		$this->Check(     $core->UniqueId(0         ), '');
		$this->Check(     $core->UniqueId(-1        ), '');

		$this->CheckMatch($core->RandomCode(                 ), '^[a-zA-Z0-9]{10}$');
		$this->CheckMatch($core->RandomCode(11               ), '^[a-zA-Z0-9]{11}$');
		$this->CheckMatch($core->RandomCode(14, '_', 5       ), '^[a-zA-Z0-9]{5}[_][a-zA-Z0-9]{5}[_][a-zA-Z0-9]{4}$');
		$this->CheckMatch($core->RandomCode(10, '' , 0, '@$!'), '^[@$!]{10}$');
		$this->Check(     $core->RandomCode(0                ), '');
		$this->Check(     $core->RandomCode(-1               ), '');

		$this->CheckMatch($core->RandomNumber(  ), '^[0-9]{4}$');
		$this->CheckMatch($core->RandomNumber(8 ), '^[0-9]{8}$');
		$this->Check(     $core->RandomNumber(0 ), '');
		$this->Check(     $core->RandomNumber(-1), '');

		$this->CheckMatch($core->RandomMnemonic(               ), '^[a-z]{7}$');
		$this->CheckMatch($core->RandomMnemonic(8              ), '^[a-z]{8}$');
		$this->CheckMatch($core->RandomMnemonic(5, 'bDfg', 'ae'), '^[bDfg][ae][bDfg][ae][bDfg]$');
		$this->CheckMatch($core->RandomMnemonic(5, 'bDfg'      ), '^[bDfg][aeiouy][bDfg][aeiouy][bDfg]$');
		$this->CheckMatch($core->RandomMnemonic(3, ''    , 'a' ), '^[bcdfghjklmnpqrstvwxz][a][bcdfghjklmnpqrstvwxz]$');
		$this->Check(     $core->RandomMnemonic(0              ), '');
		$this->Check(     $core->RandomMnemonic(-1             ), '');
		$this->Check(     $core->RandomMnemonic(0, 'bDfg', 'ae'), '');
		$this->CheckMatch($core->RandomMnemonic(8, ''    , ''  ), '^[a-z]{8}$');

		$this->Check(     $core->LabelMnemonic('my 2nd # little test'    ), 'myndlit');
		$this->CheckMatch($core->LabelMnemonic('My Test'             , 10), '^[m][y][t][e][s][t][a-z]{4}$');
		$this->Check(     $core->LabelMnemonic('My Test'             , 0 ), '');
		$this->Check(     $core->LabelMnemonic('My Test'             , -1), '');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs test on paths helpers
	//------------------------------------------------------------------------------------------------------------------
	protected function RunPaths(TCore $core)
	{
		$protocol = $core->Protocol();
		$hostRoot = $core->HostRoot();
		$domain   = $core->Domain();

		$this->CheckTrue( $core->IsExternalUrl('https://fewd.org'            ));
		$this->CheckTrue( $core->IsExternalUrl('ftp://fewd.org'              ));
		$this->CheckFalse($core->IsExternalUrl('fewd.org'                    ));
		$this->CheckFalse($core->IsExternalUrl($protocol . $domain . '/test' ));
		$this->CheckFalse($core->IsExternalUrl($protocol . $domain           ));
		$this->CheckFalse($core->IsExternalUrl($hostRoot . '/test'           ));
		$this->CheckFalse($core->IsExternalUrl($hostRoot                     ));

		$this->CheckFalse($core->IsAbsoluteUrl('https://fewd.org'            ));
		$this->CheckFalse($core->IsAbsoluteUrl('ftp://fewd.org'              ));
		$this->CheckFalse($core->IsAbsoluteUrl('fewd.org'                    ));
		$this->CheckTrue( $core->IsAbsoluteUrl($protocol . $domain . '/test' ));
		$this->CheckTrue( $core->IsAbsoluteUrl($protocol . $domain           ));
		$this->CheckFalse($core->IsAbsoluteUrl($hostRoot . '/test'           ));
		$this->CheckFalse($core->IsAbsoluteUrl($hostRoot                     ));

		$this->CheckFalse($core->IsAbsolutePath('https://fewd.org'           ));
		$this->CheckFalse($core->IsAbsolutePath('ftp://fewd.org'             ));
		$this->CheckFalse($core->IsAbsolutePath('fewd.org'                   ));
		$this->CheckFalse($core->IsAbsolutePath($protocol . $domain . '/test'));
		$this->CheckFalse($core->IsAbsolutePath($protocol . $domain          ));
		$this->CheckTrue( $core->IsAbsolutePath($hostRoot . '/test'          ));
		$this->CheckTrue( $core->IsAbsolutePath($hostRoot                    ));

		$this->Check($core->RelativeLink('https://fewd.org/test'             ), 'https://fewd.org/test');
		$this->Check($core->RelativeLink('https://fewd.org/test'      , true ), 'https://fewd.org/test');
		$this->Check($core->RelativeLink('https://fewd.org/test'      , false), '');
		$this->Check($core->RelativeLink('ftp://' . $domain . '/test'        ), 'ftp://' . $domain . '/test');
		$this->Check($core->RelativeLink('ftp://' . $domain . '/test' , true ), 'ftp://' . $domain . '/test');
		$this->Check($core->RelativeLink('ftp://' . $domain . '/test' , false), '');
		$this->Check($core->RelativeLink($protocol . $domain . '/test'       ), 'test');
		$this->Check($core->RelativeLink($protocol . $domain . '/test', true ), 'test');
		$this->Check($core->RelativeLink($protocol . $domain . '/test', false), 'test');
		$this->Check($core->RelativeLink($protocol . $domain                 ), '');
		$this->Check($core->RelativeLink($protocol . $domain          , true ), '');
		$this->Check($core->RelativeLink($protocol . $domain          , false), '');
		$this->Check($core->RelativeLink($hostRoot . '/test'                 ), 'test');
		$this->Check($core->RelativeLink($hostRoot . '/test'          , true ), 'test');
		$this->Check($core->RelativeLink($hostRoot . '/test'          , false), 'test');
		$this->Check($core->RelativeLink($hostRoot                           ), '');
		$this->Check($core->RelativeLink($hostRoot                    , true ), '');
		$this->Check($core->RelativeLink($hostRoot                    , false), '');

		$this->Check($core->AbsoluteUrl('https://fewd.org/test'             ), 'https://fewd.org/test');
		$this->Check($core->AbsoluteUrl('https://fewd.org/test'      , true ), 'https://fewd.org/test');
		$this->Check($core->AbsoluteUrl('https://fewd.org/test'      , false), '');
		$this->Check($core->AbsoluteUrl('ftp://' . $domain . '/test'        ), 'ftp://' . $domain . '/test');
		$this->Check($core->AbsoluteUrl('ftp://' . $domain . '/test' , true ), 'ftp://' . $domain . '/test');
		$this->Check($core->AbsoluteUrl('ftp://' . $domain . '/test' , false), '');
		$this->Check($core->AbsoluteUrl($protocol . $domain . '/test'       ), $protocol . $domain . '/test');
		$this->Check($core->AbsoluteUrl($protocol . $domain . '/test', true ), $protocol . $domain . '/test');
		$this->Check($core->AbsoluteUrl($protocol . $domain . '/test', false), $protocol . $domain . '/test');
		$this->Check($core->AbsoluteUrl($protocol . $domain                 ), $protocol . $domain);
		$this->Check($core->AbsoluteUrl($protocol . $domain          , true ), $protocol . $domain);
		$this->Check($core->AbsoluteUrl($protocol . $domain          , false), $protocol . $domain);
		$this->Check($core->AbsoluteUrl($hostRoot . '/test'                 ), $protocol . $domain . '/test');
		$this->Check($core->AbsoluteUrl($hostRoot . '/test'          , true ), $protocol . $domain . '/test');
		$this->Check($core->AbsoluteUrl($hostRoot . '/test'          , false), $protocol . $domain . '/test');
		$this->Check($core->AbsoluteUrl($hostRoot                           ), $protocol . $domain);
		$this->Check($core->AbsoluteUrl($hostRoot                    , true ), $protocol . $domain);
		$this->Check($core->AbsoluteUrl($hostRoot                    , false), $protocol . $domain);

		$this->Check($core->AbsolutePath('https://fewd.org/test'             ), 'https://fewd.org/test');
		$this->Check($core->AbsolutePath('https://fewd.org/test'      , true ), 'https://fewd.org/test');
		$this->Check($core->AbsolutePath('https://fewd.org/test'      , false), '');
		$this->Check($core->AbsolutePath('ftp://' . $domain . '/test'        ), 'ftp://' . $domain . '/test');
		$this->Check($core->AbsolutePath('ftp://' . $domain . '/test' , true ), 'ftp://' . $domain . '/test');
		$this->Check($core->AbsolutePath('ftp://' . $domain . '/test' , false), '');
		$this->Check($core->AbsolutePath($protocol . $domain . '/test'       ), $hostRoot . '/test');
		$this->Check($core->AbsolutePath($protocol . $domain . '/test', true ), $hostRoot . '/test');
		$this->Check($core->AbsolutePath($protocol . $domain . '/test', false), $hostRoot . '/test');
		$this->Check($core->AbsolutePath($protocol . $domain                 ), $hostRoot);
		$this->Check($core->AbsolutePath($protocol . $domain          , true ), $hostRoot);
		$this->Check($core->AbsolutePath($protocol . $domain          , false), $hostRoot);
		$this->Check($core->AbsolutePath($hostRoot . '/test'                 ), $hostRoot . '/test');
		$this->Check($core->AbsolutePath($hostRoot . '/test'          , true ), $hostRoot . '/test');
		$this->Check($core->AbsolutePath($hostRoot . '/test'          , false), $hostRoot . '/test');
		$this->Check($core->AbsolutePath($hostRoot                           ), $hostRoot);
		$this->Check($core->AbsolutePath($hostRoot                    , true ), $hostRoot);
		$this->Check($core->AbsolutePath($hostRoot                    , false), $hostRoot);

		$this->Check($core->Join('test'), 'test');
		$this->Check($core->Join('test1', 'test2'), 'test1/test2');
		$this->Check($core->Join('test/'), 'test');
		$this->Check($core->Join('/test'), '/test');
		$this->Check($core->Join('///test'), '/test');
		$this->Check($core->Join('/'), '/');
		$this->Check($core->Join('', '/test'), '/test');
		$this->Check($core->Join('', '/test1', '/test2', 'test3/'), '/test1/test2/test3');
		$this->Check($core->Join('test1', 'test2/test3', 'test4'), 'test1/test2/test3/test4');
		$this->Check($core->Join('test', '/', ''), 'test');
		$this->Check($core->Join('test1', '/', 'test2'), 'test1/test2');

		$this->Check($core->ArgsQuery(array()), '');
		$this->Check($core->ArgsQuery(array('t' => 1, 'u' => '2')), 't=1&u=2');
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs test on internet helpers
	//------------------------------------------------------------------------------------------------------------------
	protected function RunInternet(TCore $core)
	{
		// If no internet access (unwired localhost) :
		// Does nothing
		if(!function_exists('checkdnsrr') || !checkdnsrr('https://php.net'))
		{
			return;
		}

		// Tests servers
		$this->CheckTrue($core->IsValidDomain('https://fewd/org'));

		// Tests emails
		$this->CheckTrue($core->IsValidEmail('contact@fewd.org'));
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		$core = new TCore();
		$core->Init();

		$this->RunStrings(    $core);
		$this->RunIdentifiers($core);
		$this->RunPaths(      $core);
		$this->RunInternet(   $core);
	}
}
