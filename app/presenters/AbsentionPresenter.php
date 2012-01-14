<?php

use Nette\Application\UI;

class AbsentionPresenter extends BasePresenter
{
  /** @permission(absention, viewMine) */
  public function renderDefault()
  {
    $this->template->absentions = $this->table('absentions')
      ->where('teacher_id', $this->context->user->identity->data["id"])
      ->order('date DESC');
  }

  public function resolveHours($hours)
  {
    $resolved = array();
    for ($i = 0; $i <= 8; $i++) {
      if ($hours & $i) $resolved[] = $i;
    }
    return $resolved;
  }

  /** @permission(absention, report) */
  public function renderReport()
  {}

  public function processReportForm(UI\Form $form)
  {
    $values = $form->getValues();

    $absention = array(
      'date' => new \DateTime($values['date']),
      'teacher_id' => $this->context->user->identity->data['id']
    );

    $hours = 0;
    for ($i = 0; $i <= 8; $i++) {
      if ($values['hour_' . $i]) {
        $hours += pow(2, $i);
      }
    }
    $absention['hours'] = $hours;

    $this->context->database->exec('INSERT INTO absentions', $absention);
    
    $this->redirect('default');
  }

  protected function createComponentReportForm()
  {
    $form = new UI\Form;

    $form->addText('date', 'Dátum')
      ->setRequired('Prosím zadaj dátum.');

    for ($i = 0; $i <= 8; $i++) {
      $form->addCheckbox('hour_' . $i);
    }

    $form->addSubmit('send', 'Nahlásiť');

    $form->onSuccess[] = callback($this, 'processReportForm');

    return $form;
  }
}
