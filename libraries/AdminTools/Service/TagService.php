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
	}
?>
