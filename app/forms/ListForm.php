<?php

use Nette\Application\UI,
	Nette\DI\Container;

class ListForm extends UI\Form
{
	private $context;

	public function __construct(Container $context)
	{
		parent::__construct();

		$this->context = $context;
		$form = $this;

		$this->addSubmit('save', 'uložiť');

		$this->addDynamic('absentions', function ($container) use ($form) {

			$form->addName($container, 'teacher', 'teachers')
				->setRequired()
				->addRule(':exists', 'učiteľ neexistuje');

			$container->addDynamic('substitutions', function ($container) use ($form) {

				$container->addText('hour')
					->setRequired();

				$form->addName($container, 'class', 'classes')
					->setRequired()
					->addRule(':exists', 'trieda neexistuje');

				$form->addName($container, 'subject', 'subjects')
					->setRequired()
					->addRule(':exists', 'predmet neexistuje');

				$form->addName($container, 'substitute', 'teachers')
					->setRequired()
					->addRule(':exists', 'učiteľ neexistuje');

				$container->addSubmit('remove')->setValidationScope(NULL)->onClick[] = function ($button) {
					$container = $button->parent;
					$container->parent->remove($container, TRUE);
				};

			})->addSubmit('add')->setValidationScope(NULL)->onClick[] = function ($button) {
				$button->parent->createOne();
			};

			$container->addSubmit('remove')->setValidationScope(NULL)->onClick[] = function ($button) {
				$container = $button->parent;
				$container->parent->remove($container, TRUE);
			};

		})->addSubmit('add')->setValidationScope(NULL)->onClick[] = function ($button) {
			$container = $button->parent->createOne();
			$container['substitutions']->createOne();
		};
	}

	public function addName($container, $name, $group)
	{
		return $container[$name] = $this->context->createNameInput($group);
	}
}
