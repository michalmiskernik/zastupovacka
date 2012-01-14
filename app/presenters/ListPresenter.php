<?php

class ListPresenter extends BasePresenter
{
  public function renderDefault()
  {
    $this->template->lists = $this->table('list')->order('date');
  }

  public function renderView($date)
  {
    
  }
}
