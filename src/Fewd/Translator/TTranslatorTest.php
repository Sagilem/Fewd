<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Translator;


use Fewd\Core\ATest;
use Fewd\Core\TCore;


class TTranslatorTest extends ATest
{
	//------------------------------------------------------------------------------------------------------------------
	// Runs the test
	//------------------------------------------------------------------------------------------------------------------
	public function Run()
	{
		$core = new TCore();
		$core->Init();

		// Checks
		// With no culture when TTranslator object is instanciated
		$translator = new TTranslator($core, '');
		$translator->Init();

		$this->Check($translator->DefaultCulture(), 'en');
		$this->Check($translator->Culture(), 'fr-FR');
		$this->Check($translator->NeutralCulture($translator->Culture()), 'fr');

		$translator->SetCulture('fr-FR');
		$this->Check($translator->Culture(), 'fr-FR');
		$this->Check($translator->NeutralCulture($translator->Culture()), 'fr');

		$this->Check($translator->IsLoaded($translator->Culture()), false);
		$translator->Load($translator->Culture());
		$this->Check($translator->IsLoaded($translator->Culture()), true);

		// With culture when TTranslator object is instanciated
		$translator = new TTranslator($core, 'en-US');
		$translator->Init();

		$this->Check($translator->DefaultCulture(), 'en-US');

		$translator->SetCulture('');
		$this->Check($translator->Culture(), 'en-US');

		$this->Check($translator->NeutralCulture('dk-DK'), 'dk');
		$this->Check($translator->NeutralCulture('en'), 'en');
		$this->Check($translator->NeutralCulture(''), '');

		$translator->Load('');
		$this->CheckTrue($translator->IsLoaded(''), 'Culture is not loaded');

		$translator->Load('en-US');
		$this->CheckTrue($translator->IsLoaded('en-US'), 'Culture is not loaded');

		$translator->Learn('bye', 'fr', 'au revoir');
		$translator->Learn('bye', 'en', 'good bye');

		$this->Check($translator->Translate('bye', 'fr'), 'au revoir');
		$this->Check($translator->Translate('bye', 'fr-FR'), 'au revoir');
		$this->Check($translator->Translate('hello', 'en'), "[[hello]]");
		$this->Check($translator->Translate('bye', 'en-US'), 'good bye');
		$this->Check($translator->Translate('hello', 'fr-FR'), "[[hello]]");
		$this->Check($translator->Translate('hello', ''), "[[hello]]");

		$this->Check($translator->Say('bye'), 'good bye');
	}
}
