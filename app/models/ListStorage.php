<?php

use Nette\ArrayHash,
  Nette\ArrayList,
  Nette\Database\Table\ActiveRow;

class ListStorage extends Nette\Object
{
  private $list;
  private $session;

  public function __construct(Nette\Http\SessionSection $session)
  {
    $this->session = $session;
  }

  public function setList($list)
  {
    if ($list instanceof ActiveRow) {
      $list = $this->convertListRow($list);
    }

    $this->session->list = $this->list = $list;
  }

  public function getList()
  {
    if (!isset($this->list)) {
      $this->list = $this->session->list;
    }

    return $this->list;
  }

  public function save()
  {
    $this->setList($this->getList());
  }

  private function convertListRow($list)
  {
    $array = ArrayHash::from(array(
      "date" => $list->date,
      "absentions" => new Absentions
    ));

    $absentions = $array->absentions;

    foreach ($list->related('substitutions') as $subst) {
      $absention = $absentions->find($subst->absention_id);

      if (!$absention)
        $absention = $absentions->add($subst->absention);

      $absention->substitutions->add($subst);
    }

    return $array;
  }
}
