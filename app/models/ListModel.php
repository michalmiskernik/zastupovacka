<?php

use Nette\ArrayHash;

class ListModel extends Nette\Object
{
	private $lists;

	public function __construct($lists)
	{
		$this->lists = $lists;
	}

	public function load($date)
	{
		$list = $this->lists->where('date', $date)->fetch();
		$absentions = array();

		foreach ($list->related('substitutions') as $sbt) {

			if (!isset($absentions[$sbt->absention_id])) {
				$absentions[$sbt->absention_id] = ArrayHash::from(array(
					"teacher" => $sbt->absention->teacher->surname,
					"substitutions" => array()
				));
			}

			$absentions[$sbt->absention_id]->substitutions[$sbt->id] = ArrayHash::from(array(
				"hour" => $sbt->hour,
				"class" => $sbt->class->name,
				"subject" => $sbt->subject->abbr,
				"substitute" => $sbt->substitute->surname,
			));

		}

		return $absentions;
	}
}
