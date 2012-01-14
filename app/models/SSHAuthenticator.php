<?php

use Nette\Security as NS,
  Nette\DI;

class SSHAuthenticator extends Nette\Object implements NS\IAuthenticator
{

  protected $ssh, $context, $host, $port;

  public function __construct(DI\Container $context, $host, $port)
  {
    $this->context = $context;
    $this->host = $host;
    $this->port = $port;
  }

  public function authenticate(array $credentials)
  {
    $this->connect();
    list($username, $password) = $credentials;

    if ($this->sshAuthenticate($username, $password)) {
      $identity = $this->createIdentity($username);

      $this->logout();

      return $identity;
    }

    throw new NS\AuthenticationException("Zlé meno alebo heslo", self::INVALID_CREDENTIAL);
  }

  protected function connect()
  {
    $this->ssh = ssh2_connect($this->host, $this->port);
  }

  protected function sshAuthenticate($username, $password)
  {
    return (bool) @ssh2_auth_password($this->ssh, $username, $password);
  }

  protected function getGroups()
  {
    $stream = ssh2_exec($this->ssh, "groups");
    stream_set_blocking($stream, true);
    $groups = preg_split("/\s+/", trim(stream_get_contents($stream)));
    fclose($stream);

    return $groups;
  }

  protected function createIdentity($username)
  {
    $teacher = $this->context->database
      ->table('teachers')->where('username', $username)->fetch();
    
    if ($teacher) {
      return new NS\Identity($username, $this->getGroups(), $teacher->toArray());
    }

    throw new NS\AuthenticationException("Používateľ nenájdený", self::IDENTITY_NOT_FOUND);
  }

  protected function logout()
  {
    ssh2_exec($this->ssh, "logout");
  }

}
