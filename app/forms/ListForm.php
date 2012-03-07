<?php

use Nette\Application\UI;

class ListForm extends UI\Form
{
	public function __construct()
	{
		parent::__construct();

		$this->addSubmit('save', 'uložiť');

		$this->addDynamic('absentions', function ($container) {
			$container->addText('teacher')->setRequired();

			$container->addDynamic('substitutions', function ($container) {
				$container->addText('hour')->setRequired();
				$container->addText('class')->setRequired();
				$container->addText('subject')->setRequired();
				$container->addText('substitute')->setRequired();
			})->addSubmit('add', 'pridať')->setValidationScope(NULL)->onClick[] = function ($button) {
				$button->parent->createOne();
			};
		})->addSubmit('add', 'pridať')->setValidationScope(NULL)->onClick[] = function ($button) {
			$container = $button->parent->createOne();
			$container['substitutions']->createOne();
		};
	}
}
