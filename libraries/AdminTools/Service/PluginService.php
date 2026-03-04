<?php
	/**
	 * Service for plugin management in AdminTools
	 */
	class AdminTools_Service_PluginService
	{
		public function __construct()
		{
			$this->db = get_db();
			$this->pluginTable = $this->db->getTableName('Plugin');
		}
		
		public function countInstalled(): int
		{
			return (int) $this->db->getTable('Plugin')->count();
		}

		public function countActive(): int
		{
			$select = $this->db->select()
				->from(
					$this->pluginTable,
					new Zend_Db_Expr('COUNT(*)')
				)
				->where('active = ?', 1);

			return (int) $this->db->fetchOne($select);
		}

		public function countInvalid(): int
		{
			$directories = glob(PLUGIN_DIR . '/*', GLOB_ONLYDIR);
			$directories = array_map('basename', $directories);

			$select = $this->db->select()
				->from($this->pluginTable, new Zend_Db_Expr('COUNT(*)'));

			if (!empty($directories)) {
				$select->where('name NOT IN (?)', $directories);
			}

			return (int) $this->db->fetchOne($select);
		}

		public function description()
		{
			$countInstalled = self::countInstalled();
			if ($countInstalled == 0) {
				$description = __('No Plugin is installed in the system.');
			} else {
				$description =  __(plural('The system contains <b>1</b> installed Plugin', 'The system contains <b>%s</b> installed Plugins', $countInstalled), $countInstalled);
				$description .= ', ';

				$countActive = self::countActive();
				$countInvalid = self::countInvalid();
				
				if ($countInstalled == $countActive) {
					$description .= __(plural('which is already active.', 'which are all already active.', $countInstalled));
				} elseif ($countActive == 0) {
					$description .= __(plural('which is not active.', 'which are all not active.', $countInstalled));
				} else {
					$description .= __(plural('of which <b>1</b> active.', 'of which <b>%d</b> active.', $countActive), $countActive);
				}

				$description .= ' ';

				if ($countInvalid == 0) {
					$description .= __('No Plugin appears to be invalid/damaged.');
				} else {
					$decription .= __(plural('<b>1</b> Plugin appears to be invalid/damaged, and can be safely removed.', '<b>%d</b> Plugins appear to be invalid/damaged, and can be safely removed.', $countInvalid), $countInvalid);
				}
			}

			return $description;
		}
		
		public function activateAll(): bool
		{
			return $this->_activateAll();
		}

		public function deactivateAll(): bool
		{
			return $this->_deactivateAll();
		}

		public function removeInvalid(): bool
		{
			return $this->_removeInvalid();
		}
		
		protected function _activateAll(): bool
		{
			$affected = $this->db->update($this->pluginTable, array('active' => 1), 'active = 0');
			
			return ($affected <> 0);
		}

		protected function _deactivateAll(): bool
		{
			$affected = $this->db->update($this->pluginTable, array('active' => 0), 'active = 1');

			return ($affected <> 0);
		}

		protected function _removeInvalid(): bool
		{
			$directories = glob(PLUGIN_DIR . '/*', GLOB_ONLYDIR);
			$directories = array_map('basename', $directories);

			if (empty($directories)) {
				$affected = $this->db->delete($this->pluginTable);
				return ($affected > 0);
			}

			$affected = $this->db->delete(
				$this->pluginTable,
				$this->db->quoteInto(
					'name NOT IN (?)',
					$directories
				)
			);

			return ($affected > 0);
		}
	}
?>