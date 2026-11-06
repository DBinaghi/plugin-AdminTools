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
			$sql = "SELECT COUNT(*) FROM {$this->db->Item} i
            WHERE NOT EXISTS (
                SELECT 1 FROM {$this->db->RecordsTag} rt
                WHERE rt.record_id = i.id
                  AND rt.record_type = 'Item'
            )";
    		return (int)$this->db->fetchOne($sql);
		}
	}
?>
