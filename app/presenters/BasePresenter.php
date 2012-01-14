<?php

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
  protected function table($name)
  {
    return $this->context->database->table($name);
  }

  public function beforeRender()
  {
    $this->template->__lists = $this->table('lists')
      ->where('date >= ?', new \DateTime('today + 2 days'))
      ->order('date');
  }

  public function templatePrepareFilters($template)
  {
    $latte = new Nette\Latte\Engine;
    $template->registerFilter($latte);

    $set = new Nette\Latte\Macros\MacroSet($latte->compiler);
    $set->addMacro('ifAllowed', function ($node, $writer) {
      $action = $node->tokenizer->fetchWord();
      $privilege = $node->tokenizer->fetchWord();

      return $writer->write('if ($user->isAllowed(%var, %var)):', $resource, $privilege);
    }, 'endif');
  }
}
