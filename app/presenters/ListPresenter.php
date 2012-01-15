<?php

class ListPresenter extends BasePresenter
{
  protected $date, $list, $substitutions;

  public function renderDefault()
  {
    $this->template->lists = $this->table('lists')->order('date');
  }

  public function renderView($date)
  {
    $date = new \DateTime($date);
    $this->loadList($date);
  }

  /** @permission(list, edit) */
  public function renderEdit($date)
  {
    $date = new \DateTime($date);
    $this->loadList($date);

    if (!$this->list) return;

    $this->template->substitutions = $this->getListFromSession()->substitutions;
  }

  protected function loadList(\DateTime $date)
  {
    $this->template->date = $this->date = $date;
    $list = $this->table('lists')->where('date', $date)->fetch();
    $this->template->list = $this->list = $list;

    if (!$list) return;

    $this->template->substitutions = $this->substitutions = $list
      ->related('substitutions')->order('absention_id');
  }

  protected function getListFromSession()
  {
    $name = 'list-' . $this->date->format('Y-m-d');
    $session = $this->context->session;

    if (!$session->hasSection($name)) {
      $section = $session->getSection($name);
      $section->setExpiration(0); // until the browser is closed
      $section->date = $this->list->date;
      $section->substitutions = $this->getArrayFromList($this->list);
    }

    return $session->getSection($name);
  }

  private function getArrayFromList($list)
  {
    return array_map(function ($s) {
      return $s->toArray();
    }, iterator_to_array($list->related('substitutions')));
  }
}
