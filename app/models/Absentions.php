<?php

use Nette\ArrayList,
  Nette\ArrayHash;

class Absentions extends ArrayList
{
  public function addBlank()
  {
    return $this[] = ArrayHash::from(array(
      "teacher" => "",
      "substitutions" => new Substitutions
    ));
  }

  public function add($absention)
  {
    return $this[] = ArrayHash::from(array(
      "id" => $absention->id,
      "teacher" => $absention->teacher->surname,
      "substitutions" => new Substitutions
    ));
  }

  public function find($id)
  {
    foreach ($this as $absention) {
      if ($absention->id === $id)
        return $absention;
    }

    return FALSE;
  }
}
