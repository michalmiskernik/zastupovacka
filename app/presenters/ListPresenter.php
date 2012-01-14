<?php

class ListPresenter extends BasePresenter
{
  public function renderDefault()
  {
    $this->template->lists = $this->table('lists')->order('date');
  }

  public function renderToday()
  {
    $date = new \DateTime('today');
    $this->loadList($date);
    $this->setView('view');
  }

  public function renderView($date)
  {
    $date = new \DateTime($date);
    $this->loadList($date);
  }

  protected function loadList(\DateTime $date)
  {
    $list = $this->table('lists')->where('date', $date)->fetch();
    $this->template->list = $list;
    $this->template->substitutions = $list
      ->related('substitutions')->order('absention_id');
  }
}
