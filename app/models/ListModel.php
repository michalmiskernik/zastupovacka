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

		if (!$list) {
			return $list;
		}

		$absentions = array();

		foreach ($list->related('substitutions')->order('hour') as $sbt) {

			if (!isset($absentions[$sbt->absention_id])) {
				$absentions[$sbt->absention_id] = ArrayHash::from(array(
					"teacher" => $sbt->absention->teacher->surname,
					"substitutions" => array()
				));
			}

			$absentions[$sbt->absention_id]->substitutions[$sbt->id] = ArrayHash::from(array(
				"hour" => $sbt->hour,
				"class" => implode($this->getClasses($sbt), '/'),
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

	private function getClasses($substitution)
	{
		if ($substitution->class) {
			return array($substitution->class->name);
		} else {
			$classes = $substitution->related('substitution_classes');
			return array_map(function ($sc) {
				return $sc->class->name;
			}, iterator_to_array($classes));
		}
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
			throw $e;
		}
	}

	private function create($date, $absentions)
	{
		$list = $this->db->table('lists')->insert(array(
			"date" => $date
		));

		$this->assignAbsentionIds($list, $absentions);

		$this->db->beginTransaction();

		try {
			$this->createAbsentions($list, $absentions);
			$this->createSubstitutions($list, $absentions);
			$this->db->commit();
		} catch (PDOException $e) {
			$this->db->rollBack();
			throw $e;
		}
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
		$queue = new InsertQueue($this->db->table('substitutions'));
		$scTable = $this->db->table('substitution_classes');

		foreach ($absentions as $absention) {
			foreach ($absention->substitutions as $substitution) {
				$data = array(
					"list_id" => $list->id,
					"absention_id" => $absention->id,
					"hour" => $substitution->hour,
					"class_id" => is_array($substitution->class) ? NULL : $substitution->class,
					"subject_id" => $substitution->subject,
					"substitute_id" => $substitution->substitute
				);

				$insert = new Insert($data);

				if (is_array($substitution->class)) {
					$classIds = $substitution->class;
					$insert->onDone[] = function ($row) use ($classIds, $scTable) {
						foreach ($classIds as $id) {
							$scTable->insert(array(
								"substitution_id" => $row->id,
								"class_id" => $id
							));
						}
					};
				}

				$queue->add($insert);
			}
		}

		$queue->run();
	}
}
