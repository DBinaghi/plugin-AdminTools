<?php
	class AdminTools_Service
	{
		public static function getPluginsInstalledCount()
		{
			$db = get_db();
			return $db->getTable('Plugin')->count();
		}

		public static function getPluginsActiveCount()
		{
			$db = get_db();
			$sql = "SELECT COUNT(*) FROM " . $db->getTableName('Plugin') . " WHERE active = 1";
			return $db->fetchOne($sql);
		}

		public static function getPluginsInvalidCount()
		{
			$db = get_db();
			$path = PLUGIN_DIR;
			$directories = str_replace($path . '/', '', glob($path . '/*', GLOB_ONLYDIR));
			$sql = "SELECT COUNT(*) FROM " . $db->getTableName('Plugin') . " WHERE name NOT IN ('" . implode("','", $directories) . "')";
			return $db->fetchOne($sql);
		}

		public static function getPluginsDescription()
		{
			$pluginsInstalledCount = self::getPluginsInstalledCount();

			if ($pluginsInstalledCount == 0) {
				$description = __('No Plugin is installed in the system.');
			} else {
				$pluginsActiveCount = self::getPluginsActiveCount();
				$description =  __(plural('The system contains <b>1</b> installed Plugin', 'The system contains <b>%s</b> installed Plugins', $pluginsInstalledCount), $pluginsInstalledCount);
				$description .= ', ';
				if ($pluginsInstalledCount == $pluginsActiveCount) {
					$description .= __(plural('which is already active.', 'which are all already active.', $pluginsInstalledCount));
				} elseif ($pluginsActiveCount == 0) {
					$description .= __(plural('which is not active.', 'which are all not active.', $pluginsInstalledCount));
				} else {
					$description .= __(plural('of which <b>1</b> active.', 'of which <b>%d</b> active.', $pluginsActiveCount), $pluginsActiveCount);
				}

				$description .= ' ';

				$pluginsInvalidCount = self::getPluginsInvalidCount();
				if ($pluginsInvalidCount == 0) {
					$description .= __('No Plugin appears to be invalid/damaged.');
				} else {
					$decription .= __(plural('<b>1</b> Plugin appears to be invalid/damaged, and can be safely removed.', '<b>%d</b> Plugins appear to be invalid/damaged, and can be safely removed.', $this->pluginsInvalid), $this->pluginsInvalid);
				}
			}

			return $description;
		}

		public static function getTagsUnusedCount()
		{
			$db = get_db();
			$sql = 'SELECT COUNT(*) FROM ' . $db->getTableName('Tag') . ' WHERE id IN (SELECT id FROM (SELECT t1.id FROM ' . $db->getTableName('Tag') . ' t1 LEFT OUTER JOIN ' . $db->getTableName('RecordsTag') . ' rt ON t1.id = rt.tag_id GROUP BY t1.id HAVING COUNT(rt.id) = 0) tmp)';
			return $db->fetchOne($sql);
		}
	}
?>
