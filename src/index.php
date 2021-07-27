<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


include 'Fewd/Fewd.php';


use Fewd\App\TApp;


// Inits the app
$app = new TApp();
$app->Init();

// Runs all tests
if($app->Core()->IsLocalhost())
{
	$app->Test();
}

// Defines routes
$app->Router()->AddStrictRule('', 'HOMEPAGE');
$app->Router()->AddAction('HOMEPAGE', function() { echo 'This is my Homepage.'; });

// Runs app
$app->Run();



//----------------------------------------------------------------------------------------------------------------------
// Quick trace
//----------------------------------------------------------------------------------------------------------------------
function tr($value, $label = '')
{
	global $app;
	$app->Tracer()->Trace($value, $label);
}
