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
    $storage = $this->context->listStorage;

    $list = $storage->getList();
    if (!$list || $list->date != $date) {
      $row = $this->table('lists')->where('date', $date)->fetch();
      $storage->setList($row);
    }

    $form = $this['listEditForm'];
    $form->createControls($storage->getList());
  }

  /** @permission(list, edit) */
  public function renderEdit($date)
  {
    $list = $this->context->listStorage->getList();
    $this->template->list = $list;
    
    $this->template->form = $this['listEditForm'];
  }

  public function save() {
    $this->context->listStorage->save();
    $this->redirect('this');
  }

  protected function createComponentListEditForm() {
    return new ListEditForm;
  }
}
