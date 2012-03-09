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
		$list = $this->loadList($date);
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

	private function loadList($date)
	{
		return $this->db->table('lists')->where('date', $date)->fetch();
	}

	/*** saving *****************************************************************/

	public function save($date, $absentions)
	{
		$list = $this->loadList($date);

		if (!$list) {
			$this->create($date, $absentions);
		} else {
			$this->update($list, $absentions);
		}
	}

	private function update($list, $absentions)
	{
		$this->assignAbsentionIds($list, $absentions);

		$this->db->beginTransaction();

		try {
			$this->createAbsentions($list, $absentions);
			$this->deleteSubstitutions($list);
			$this->createSubstitutions($list, $absentions);
			$this->db->commit();
		} catch (PDOException $e) {
			$this->db->rollBack();
		}
	}

	private function create($date, $absentions)
	{
		$list = $this->db->table('lists')->insert(array(
			"date" => $date
		));
	}

	private function assignAbsentionIds($list, $absentions)
	{
		$teachers = $this->extractTeachers($absentions);

		$ids = $this->db->table('absentions')
			->select('id, teacher_id')
			->where('date', $list->date)
			->where('teacher_id', $teachers)
			->fetchPairs('teacher_id', 'id');

		foreach ($absentions as $absention) {
			if (isset($ids[$absention->teacher])) {
				$absention->id = $ids[$absention->teacher];
			} else {
				$absention->id = NULL;
			}
		}
	}

	private function extractTeachers($absentions)
	{
		return array_values(
			array_map(
				function ($absention) {
					return $absention->teacher;
				},
				iterator_to_array($absentions)
			)
		);
	}

	private function createAbsentions($list, $absentions)
	{
		$new = array_filter(
			iterator_to_array($absentions),
			function ($absention) {
				return is_null($absention->id);
			}
		);

		foreach ($new as $absention) {
			$this->db->table('absentions')->insert(array(
				"date" => $list->date,
				"hours" => 511,
				"teacher_id" => $absention->teacher
			));

			$absention->id = $this->db->lastInsertId();
		}
	}

	private function deleteSubstitutions($list)
	{
		$this->db->table('substitutions')->where('list_id', $list->id)->delete();
	}

	private function createSubstitutions($list, $absentions)
	{
		$values = array();

		foreach ($absentions as $absention) {
			foreach ($absention->substitutions as $substitution) {
				$values[] = array(
					"list_id" => $list->id,
					"absention_id" => $absention->id,
					"hour" => $substitution->hour,
					"class_id" => $substitution->class,
					"subject_id" => $substitution->subject,
					"substitute_id" => $substitution->substitute
				);
			}
		}

		$this->db->table('substitutions')->insert($values);
	}
}
