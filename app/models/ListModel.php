<?php

use Nette\ArrayHash,
	Nette\Database\Connection;

class ListModel extends Nette\Object
{
	private $db;

	public function __construct(Connection $conn)
	{
		$this->db = $conn;
	}

	/*** loading ****************************************************************/

	public function load($date)
	{
		$list = $this->db->table('lists')->where('date', $date)->fetch();
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

	/*** saving *****************************************************************/

	public function save($date, $absentions)
	{
		$this->findAbsentionIds($date, $absentions);
		$id = $this->findId($date);

		// $this->beginTransaction();

		if (!$id) { $id = $this->create($date); }
		$this->deleteSubstitutions($id);
		$this->createSubstitutions($id, $absentions);

		// $this->commitTransaction();
	}

	private function findAbsentionIds($date, $absentions)
	{
		$teacherIds = array_map(function ($absention) {
			return $absention->teacher;
		}, array_values(iterator_to_array($absentions)));

		$ids = $this->db->table('absentions')
			->select('id, teacher_id')
			->where('date', $date)
			->where('teacher_id', $teacherIds)
			->fetchPairs('teacher_id', 'id');

		foreach ($absentions as $absention) {
			if (isset($ids[$absention->teacher])) {
				$absention->id = $ids[$absention->teacher];
			} else {
				$absention->id = NULL;
			}
		}
	}

	private function findId($date)
	{
		$row = $this->db->table('lists')->select('id')->where('date', $date)->fetch();

		if ($row) {
			return $row->id;
		} else {
			return NULL;
		}
	}

	private function create($date)
	{
		$row = $this->db->table('lists')->insert(array(
			"date" => $date
		));
		return $row->id;
	}

	private function deleteSubstitutions($id)
	{
		$this->db->table('substitutions')->where('list_id', $id)->delete();
	}

	private function createSubstitutions($id, $absentions)
	{
		foreach ($absentions as $absention) {
			foreach ($absention->substitutions as $substitution) {
				$this->db->table('substitutions')->insert(array(
					"list_id" => $id,
					"absention_id" => $absention->id,
					"hour" => $substitution->hour,
					"class_id" => $substitution->class,
					"subject_id" => $substitution->subject,
					"substitute_id" => $substitution->substitute
				));
			}
		}
	}
}
