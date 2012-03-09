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
    $list = $this->context->listModel->load($date);
    
    $this->template->list = $list;
    $this->template->date = $date;
  }

  public function actionEdit($date)
  {
    $date = new \DateTime($date);
    $form = $this['listForm'];

    $form['save']->onClick[] = callback($this, 'processListEditForm');

    if (!$form->isSubmitted()) {
      $absentions = $this->context->listModel->load($date);
      
      foreach ($absentions as $id => $absention) {
        $container = $form['absentions'][$id];
        $container->setValues($absention);

        foreach ($absention->substitutions as $id => $substitution) {
          $container['substitutions'][$id]->setValues($substitution);
        }
      }
    }
  }

  /** @permission(list, edit) */
  public function renderEdit($date)
  {
    $this->template->date = new \DateTime($date);
  }

  public function processListEditForm($button)
  {
    $values = $button->form->getValues();
    $date = new \DateTime($this->request->parameters['date']);
    $this->context->listModel->save($date, $values->absentions);
  }

  protected function createComponentListForm() {
    return $this->context->createListForm();
  }
}
