<?php

use Nette\Application\UI,
  Nette\ComponentModel\IContainer,
  Nette\ArrayHash,
  Nette\ArrayList;

class ListEditForm extends UI\Form
{
  private $i = 1, $j = 1;

  public function createControls(ArrayHash $list)
  {
    $this->addSubmit('save', 'uložiť');

    $form = $this;
    $this->addSubmit('add', 'pridať')
      ->onClick[] = function() use (&$list, $form) {
        $list->absentions->addBlank()->substitutions->addBlank();
        $form->presenter->save();
      };

    foreach ($list->absentions as $abs) {
      $this->absContainer($abs);
    }
  }

  private function absContainer(ArrayHash $abs) {
    $cont = $this->addContainer("absention_{$this->i}");
    $this->i++;

    $this->input($cont, 'teacher', $abs->teacher);

    $form = $this;
    $cont->addSubmit('add', 'pridať')
      ->onClick[] = function() use (&$abs, $form) {
        $abs->substitutions->addBlank();
        $form->presenter->save();
      };
    
    foreach ($abs->substitutions as $subst) {
      $this->substContainer($subst, $cont);
    }
  }

  private function substContainer(ArrayHash $subst, $absCont) {
    $cont = $absCont->addContainer("substitution_{$this->j}");
    $this->j++;

    $this->input($cont, 'hour', $subst->hour);
    $this->input($cont, 'class', $subst->class);
    $this->input($cont, 'subject', $subst->subject);
    $this->input($cont, 'substitute', $subst->substitute);
  }

  private function input($container, $name, $value) {
    return $container->addText($name)
      ->setDefaultValue($value);
  }
}
