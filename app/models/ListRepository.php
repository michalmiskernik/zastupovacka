<?php

/**
* 
*/
class ListRepository extends Nette\Object
{
	private $db;

	public function __construct(DibiConnection $db)
	{
		$this->db = $db;
	}
}
