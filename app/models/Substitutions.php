<?php

use Nette\ArrayList,
  Nette\ArrayHash;

class Substitutions extends ArrayList
{
  public function addBlank()
  {
    return $this[] = ArrayHash::from(array(
      "hour" => "",
      "class" => "",
      "subject" => "",
      "substitute" => ""
    ));
  }

  public function add($subst)
  {
    return $this[] = ArrayHash::from(array(
      "id" => $subst->id,
      "hour" => $subst->hour,
      "class" => $subst->class->name,
      "subject" => $subst->subject->abbr,
      "substitute" => $subst->substitute->surname
    ));
  }
}
