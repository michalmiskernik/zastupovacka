<?php

class ListPresenter extends BasePresenter
{
  protected $date, $list;

  public function renderDefault()
  {
    $this->template->lists = $this->table('lists')->order('date DESC');
  }

  public function renderView($date = 'today')
  {
    $date = new \DateTime($date);
    $list = $this->table('lists')->where('date', $date)->fetch();

    if ($list) {
      $substitutions = $list->related('substitutions')->order('absention_id');
      $this->template->substitutions = $substitutions;
    }
    
    $this->template->list = $list;
    $this->template->date = $date;
  }

  public function actionEdit($date)
  {
    $date = new \DateTime($date);
    $form = $this['listForm'];

    if (!$form->isSubmitted()) {
      $list = $this->table('lists')->where('date', $date)->fetch();
      $asts = $form['absentions'];
      $prev = NULL;

      foreach ($list->related('substitutions') as $sbt) {
        if ($sbt->absention_id !== $prev) {
          $ast = $sbt->absention;
          $asts[$ast->id]['teacher']->setValue($ast->teacher->surname);
        }

        $sbts = $asts[$sbt->absention_id]['substitutions'];

        $sbts[$sbt->id]->setValues(array(
          "hour" => $sbt->hour,
          "class" => $sbt->class->name,
          "subject" => $sbt->subject->abbr,
          "substitute" => $sbt->substitute->surname,
        ));

        $prev = $sbt->absention_id;
      }
    }
  }

  /** @permission(list, edit) */
  public function renderEdit($date)
  {
    $this->template->date = new \DateTime($date);
  }

  public function save() {
    $this->context->listStorage->save();
    $this->redirect('this');
  }

  protected function createComponentListForm() {
    return new ListForm;
  }
}
