<?php

class NameInput extends Nette\Forms\Controls\TextInput
{
	private $group, $translator;

	public function __construct($group, NamesTranslator $translator)
	{
		parent::__construct();

		$this->translator = $translator;
		$this->group = $group;
	}

	public function getRawValue()
	{
		return parent::getValue();
	}

	public function getValue()
	{
		return $this->translator->translate($this->group, $this->getRawValue());
	}

	public function isFilled()
	{
		return (string) $this->getRawValue() !== '';
	}

	public static function validateExists($field)
	{
		return (bool) $field->getValue();
	}
}
