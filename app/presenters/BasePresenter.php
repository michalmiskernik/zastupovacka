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
}
