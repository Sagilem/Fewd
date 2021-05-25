<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


include 'Lib/Fewd/Fewd.php';


use Fewd\App\TApp;


// Inits the app
$app = new TApp();
$app->Init();


// Runs all tests
$app->Test();


// Defines routes
$app->Router()->AddStrictRule('', 'HOMEPAGE');
$app->Router()->AddRoute('HOMEPAGE', function() { echo 'This is my Homepage.'; });


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
