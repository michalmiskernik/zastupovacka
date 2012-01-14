<?php

use Nette\Security as NS;

class ACL extends NS\Permission
{
  const TEACHER = 'ucitelia';
  const ADMIN = 'zastupovacka';

  public function __construct()
  {
    $this->addRole(self::TEACHER);
    $this->addRole(self::ADMIN, self::TEACHER);

    $this->addResource('absention');
    $this->addResource('list');

    $this->allow(self::TEACHER, 'absention', 'report');

    $this->allow(self::ADMIN, 'list', 'create');
  }

  public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL)
  {
    try {
      return parent::isAllowed($role, $resource, $privilege);
    } catch (Nette\InvalidStateException $e) {
      return FALSE;
    }
  }
}
