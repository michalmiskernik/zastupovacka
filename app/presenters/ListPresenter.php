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

    $this->copyListToSession();
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
}
