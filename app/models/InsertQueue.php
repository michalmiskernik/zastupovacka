<?php

class InsertQueue extends Nette\Object
{
	private $inserts, $table;

	public function __construct($table)
	{
		$this->inserts = array();
		$this->table = $table;
	}

	public function add(Insert $insert)
	{
		$this->inserts[] = $insert;
	}

	public function run()
	{
		$values = array();

		foreach ($this->inserts as $insert) {
			if ($insert->needsResult()) {
				$row = $this->table->insert($insert->data);
				$insert->onDone($row);
			} else {
				$values[] = $insert->data;
			}
		}

		$this->table->insert($values);
	}
}

class Insert extends Nette\Object
{
	private $data;
	public $onDone;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public function needsResult()
	{
		return (bool) $this->onDone;
	}

	public function getData()
	{
		return $this->data;
	}
}
