<?php
//----------------------------------------------------------------------------------------------------------------------
// FEWD - Just a FEW Development (https://fewd.org)
//----------------------------------------------------------------------------------------------------------------------


namespace Fewd\Html;


use Fewd\Core\TCore;
use Fewd\Core\AThing;


abstract class AComponent extends AThing
{
	// Html
	private $_Html;
	public final function Html() : THtml { return $this->_Html; }

	// Id
	private $_Id;
	public final function Id() : string { return $this->_Id; }


	//------------------------------------------------------------------------------------------------------------------
	// Constructor
	//------------------------------------------------------------------------------------------------------------------
	public function __construct(TCore $core, THtml $html, string $id)
	{
		parent::__construct($core);

		$this->_Html = $html;
		$this->_Id   = $id;
	}


	//------------------------------------------------------------------------------------------------------------------
	// Init
	//------------------------------------------------------------------------------------------------------------------
	public function Init()
	{
		parent::Init();

		$this->InitMoments();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Inits moments
	//------------------------------------------------------------------------------------------------------------------
	protected function InitMoments()
	{
	}


	//------------------------------------------------------------------------------------------------------------------
	// Opening tag
	//------------------------------------------------------------------------------------------------------------------
	protected function OpeningTag() : string
	{
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Closing tag
	//------------------------------------------------------------------------------------------------------------------
	protected function ClosingTag() : string
	{
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Content
	//------------------------------------------------------------------------------------------------------------------
	protected function Content() : string
	{
		return '';
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given function callback for a given moment
	//------------------------------------------------------------------------------------------------------------------
	public function RecordCallback(callable $callback, string $moment = '')
	{
		if($moment === '')
		{
			$moment = 'Body';
		}

		$moment = $this->Id() . '@@' . $moment;

		$this->Html()->RecordCallback($callback, $moment);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given content for a given moment
	//------------------------------------------------------------------------------------------------------------------
	public function RecordContent(string $content, string $moment = '')
	{
		if($moment === '')
		{
			$moment = 'Body';
		}

		$moment = $this->Id() . '@@' . $moment;

		$this->Html()->RecordContent($content, $moment);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Records a given component to run at a given moment
	//------------------------------------------------------------------------------------------------------------------
	public function RecordComponent(AComponent $component, string $moment = '')
	{
		if($moment === '')
		{
			$moment = 'Body';
		}

		$moment = $this->Id() . '@@' . $moment;

		$this->Html()->RecordComponent($component, $moment);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Runs a given moment
	//------------------------------------------------------------------------------------------------------------------
	protected function RunMoment(string $moment, string $openingTag = '', string $closingTag = '') : string
	{
		$moment = $this->Id() . '@@' . $moment;

		return $this->Html()->RunMoment($moment, $openingTag, $closingTag);
	}


	//------------------------------------------------------------------------------------------------------------------
	// Generates component HTML
	//------------------------------------------------------------------------------------------------------------------
	public function Run() : string
	{
		$res = $this->RunMoment('Init');

		$res.= $this->OpeningTag();

		$res.= $this->RunMoment('BeforeBody');
		$res.= $this->Content();
		$res.= $this->RunMoment('Body');
		$res.= $this->RunMoment('AfterBody');

		$res.= $this->ClosingTag();

		$res.= $this->RunMoment('Conclusion');

		return $res;
	}
}