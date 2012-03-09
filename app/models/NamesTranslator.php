<?php

use Nette\Database\Connection,
	Nette\ArrayHash;

class NamesTranslator extends Nette\Object
{
	private $db, $names;

	public function __construct(Connection $conn)
	{
		$this->db = $conn;
		$this->names = new ArrayHash;

		$this->load();
	}

	private function load()
	{
		$this->names->classes = $this->db->table('classes')->fetchPairs('name', 'id');
		$this->names->subjects = $this->db->table('subjects')->fetchPairs('abbr', 'id');
		$this->names->teachers = $this->db->table('teachers')->fetchPairs('surname', 'id');
	}

	public function translate($type, $name)
	{
		if (isset($this->names[$type][$name])) {
			return $this->names[$type][$name];
		}
		
		return NULL;
	}
}
