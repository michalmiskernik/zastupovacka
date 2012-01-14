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

  public function startup()
  {
    parent::startup();

    $reflection = $this->reflection;
    try {
      $name = $this->formatActionMethod($this->action);
      $method = $reflection->getMethod($name);
      $this->checkPermissions($method);
    } catch (ReflectionException $e) {}

    try {
      $name = $this->formatRenderMethod($this->view);
      $method = $reflection->getMethod($name);
      $this->checkPermissions($method);
    } catch (ReflectionException $e) {}
  }

  private function checkPermissions(Reflector $reflector)
  {
    $annotation = $reflector->getAnnotation('permission');
    if (is_null($annotation)) return;

    $resource = $annotation[0];
    $privilege = $annotation[1];

    if (!$this->user->isAllowed($resource, $privilege)) {
      throw new Nette\Application\ForbiddenRequestException;
    }
  }
}
