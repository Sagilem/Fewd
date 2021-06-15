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

		$translator = new TTranslator($core, 'en-US');
		$translator->Init();

		// Checks
		$this->Check($translator->DefaultCulture(), 'en-US');

		$translator->SetCulture('dk-DK');
		$this->Check($translator->Culture(), 'dk-DK');

		$translator->SetCulture('');
		$this->Check($translator->Culture(), 'en-US');

		$this->Check($translator->NeutralCulture('dk-DK'), 'dk');
		$this->Check($translator->NeutralCulture('en'), 'en');
		$this->Check($translator->NeutralCulture(''), '');

		$translator->Load('');
		$this->CheckTrue($translator->IsLoaded(''),'Culture is not charged');

		$translator->Load('fr');
		$this->CheckTrue($translator->IsLoaded('fr'), 'Culture is not charged');

		$translator->Load('en-US');
		$this->CheckTrue($translator->IsLoaded('en-US'), 'Culture is not charged');

		$translator->Learn('bye', 'fr', 'au revoir');
		$translator->Learn('bye', 'en', 'good bye');

		$this->Check($translator->Translate('bye', 'fr'), 'au revoir');
		$this->Check($translator->Translate('bye', 'fr-FR'), 'au revoir');
		$this->Check($translator->Translate('hello', 'en'),"[[hello]]");
		$this->Check($translator->Translate('bye','en-US'),'good bye');
		$this->Check($translator->Translate('hello','fr-FR'),"[[hello]]");
		$this->Check($translator->Translate('hello', ''), "[[hello]]");

		$translator->RecordDictionary('Fewd/Translator/Dict/');

	}
}
