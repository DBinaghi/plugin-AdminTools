<?php
	/**
	 * Service for tag management in AdminTools
	 */
	class AdminTools_Service_TagService
	{
		public function __construct()
		{
			$this->db = get_db();
		}
		
		public function countUnused(): int
		{
			$select = $this->db->select()
				->from(
					array('t' => $this->db->Tag),
					array('count' => new Zend_Db_Expr('COUNT(*)'))
				)
				->joinLeft(
					array('rt' => $this->db->RecordsTag),
					't.id = rt.tag_id',
					array()
				)
				->where('rt.id IS NULL');
			return (int) $this->db->fetchOne($select);
		}
				
		public function countUntaggedItems(): int
		{
			$select = $this->db->select()
				->from(
					array('i' => $this->db->Item),
					new Zend_Db_Expr('COUNT(*)')
				)
				->joinLeft(
					array('rt' => $this->db->RecordsTag),
					'i.id = rt.record_id',
					array()
				)
				->where('rt.tag_id IS NULL');
			return (int) $this->db->fetchOne($select);
		}

		public function deleteUnused(): bool
		{
			$sql = "
				DELETE t
				FROM {$this->db->Tag} t
				LEFT JOIN {$this->db->RecordsTag} rt
					ON t.id = rt.tag_id
				WHERE rt.id IS NULL
			";
			$affected = $this->db->query($sql)->rowCount();

			return ($affected > 0);
		}
		
		/**
		 * Check if a tag with the given name already exists, excluding the current tag id.
		 * Returns the existing tag row (id, name) or null if no duplicate found.
		 */
		public function checkDuplicate(int $oldId, string $newName): ?array
		{
			$select = $this->db->select()
				->from(array('t' => $this->db->Tag), array('id', 'name'))
				->where('t.name = ?', $newName)
				->where('t.id != ?', $oldId)
				->limit(1);
			$result = $this->db->fetchRow($select);
			return $result ?: null;
		}

		/**
		 * Merge source tag into target tag:
		 * - reassigns all RecordsTag relations from source to target (ignoring duplicates)
		 * - deletes leftover relations for source
		 * - deletes source tag
		 * Returns true on success.
		 */
		public function merge(int $sourceId, int $targetId): int
		{
			if (!$sourceId || !$targetId || $sourceId === $targetId) {
				return -1;
			}

			$recordsTagTable = $this->db->getTableName('RecordsTag');
			$tagTable        = $this->db->getTableName('Tag');

			$this->db->query("
				UPDATE IGNORE {$recordsTagTable}
				SET tag_id = ?
				WHERE tag_id = ?
			", [$targetId, $sourceId]);

			$this->db->query(
				"DELETE FROM {$recordsTagTable} WHERE tag_id = ?",
				[$sourceId]
			);

			$this->db->query(
				"DELETE FROM {$tagTable} WHERE id = ?",
				[$sourceId]
			);

			// Return updated count for target tag
			return (int) $this->db->fetchOne(
				"SELECT COUNT(*) FROM {$recordsTagTable} WHERE tag_id = ?",
				[$targetId]
			);
		}
		
		/**
		 * Find pairs of tags with Levenshtein distance <= threshold.
		 * Returns array of ['tag1' => [...], 'tag2' => [...], 'distance' => int]
		 */
		public function findSimilar(int $threshold): array
		{
			$select = $this->db->select()
				->from(
					array('t' => $this->db->Tag),
					array('id', 'name')
				)
				->joinLeft(
					array('rt' => $this->db->RecordsTag),
					't.id = rt.tag_id',
					array('count' => new Zend_Db_Expr('COUNT(rt.id)'))
				)
				->group('t.id')
				->order('t.name ASC');

			$tags = $this->db->fetchAll($select);
			$pairs = [];
			$count = count($tags);

			for ($i = 0; $i < $count - 1; $i++) {
				for ($j = $i + 1; $j < $count; $j++) {
					$distance = levenshtein(
						mb_strtolower($tags[$i]['name']),
						mb_strtolower($tags[$j]['name'])
					);
					if ($distance <= $threshold && $distance > 0) {
						$pairs[] = [
							'tag1'     => [
								'id'    => (int)$tags[$i]['id'],
								'name'  => $tags[$i]['name'],
								'count' => (int)$tags[$i]['count'],
							],
							'tag2'     => [
								'id'    => (int)$tags[$j]['id'],
								'name'  => $tags[$j]['name'],
								'count' => (int)$tags[$j]['count'],
							],
							'distance' => $distance,
						];
					}
				}
			}

			return $pairs;
		}
	}
?>