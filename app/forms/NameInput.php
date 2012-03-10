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
		$values = explode('/', $this->getRawValue());
		$translator = $this->translator;
		$group = $this->group;

		if (count($values) == 1) {
			return $translator->translate($group, $values[0]);
		} else {
			return array_map(function ($value) use ($translator, $group) {
				return $translator->translate($group, $value);
			}, $values);
		}
	}

	public function isFilled()
	{
		return (string) $this->getRawValue() !== '';
	}

	public static function validateExists($field)
	{
		$value = $field->getValue();
		
		if (is_array($value)) {
			return !in_array(NULL, $field->getValue(), TRUE);
		} else {
			return (bool) $value;
		}
	}
}
