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

  /** @permission(list, edit) */
  public function actionEdit($date)
  {
    $date = new \DateTime($date);
    $form = $this['listForm'];

    $form['save']->onClick[] = callback($this, 'processListForm');

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
  public function renderEdit($date)
  {
    $this->template->date = new \DateTime($date);
    $this->addNamesToTemplate();
  }

  /** @permission(list, create) */
  public function actionCreate($date)
  {
    $date = new \DateTime($date);
    $form = $this['listForm'];

    $form['save']->onClick[] = callback($this, 'processListForm');

    if (!$form->isSubmitted()) {
      $form['absentions']->createOne()->getComponent('substitutions')->createOne();
    }
  }

  public function renderCreate($date)
  {
    $this->template->date = new \DateTime($date);
    $this->addNamesToTemplate();
  }

  public function processListForm($button)
  {
    $values = $button->form->getValues();
    $rawDate = $this->request->parameters['date'];
    $date = new \DateTime($rawDate);
    $this->context->listModel->save($date, $values->absentions);
    $this->redirect('view', $rawDate);
  }

  protected function createComponentListForm() {
    return $this->context->createListForm();
  }

  private function addNamesToTemplate()
  {
    $translator = $this->context->namesTranslator;
    $this->template->names = array(
      "teachers" => $translator->getAll('teachers'),
      "subjects" => $translator->getAll('subjects'),
      "classes" => $translator->getAll('classes')
    );
  }
}
