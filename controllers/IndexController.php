<?php
	class AdminTools_IndexController extends Omeka_Controller_AbstractActionController
	{
		public function indexAction()
		{
			if (get_option('admin_tools_sessions_count')) {
				$this->view->sessionsCount = $this->_getSessionsCount();
			}
			
			$this->view->lastBackupDateTime = $this->_getLastBackupDateTime();
					
			$this->view->sessionMaxLifeTime = number_format($this->_getSessionMaxLifeTime() / (60 * 60 * 24), 0);
		}
		
		public function backupAction()
		{
			$db = get_db();
			$dbConfig = $db->getAdapter()->getConfig();
			$isCompressed = get_option('admin_tools_backup_compress');
			$outputFile = ($isCompressed ? str_replace('.sql', '.gz', ADMIN_TOOLS_BACKUP_FILENAME) : ADMIN_TOOLS_BACKUP_FILENAME);
			
			$dumper = new Mysqldump\Mysqldump(
				'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'], 
				$dbConfig['username'], 
				$dbConfig['password'],
				array(
					'compress' => ($isCompressed ? Mysqldump\Mysqldump::GZIP : Mysqldump\Mysqldump::NONE)
				)
			);
			
			if (get_option('admin_tools_backup_sessions_ignore')) {
				$dumper->setTableLimits(array(
					get_db()->getTableName('Session') => 0
				));
			}
			
			$dumper->start($outputFile);

			if (get_option('admin_tools_backup_download') && file_exists($outputFile)) {
				header('Content-type: ' . ($isCompressed ? 'application/gzip' : 'text/plain'));
				header('Content-Disposition: attachment; filename="OmekaDB-backup_' . date('Ymd_His') . ($isCompressed ? '.gz' : '.sql') . '"');
				header('Content-Length: ' . filesize($outputFile));
				$inputStream = fopen($outputFile, 'rb');
				$outputStream = fopen('php://output', 'wb');
				stream_copy_to_stream($inputStream, $outputStream);
				fclose($outputStream);
				fclose($inputStream);
				exit;
			}

			$this->_helper->flashMessenger(__('A %s backup copy of the Omeka database has been created.', ($isCompressed ? __('compressed') : '')), 'success');
			$this->_helper->redirector('index', 'index');
		}
		
		public function resetCacheAction()
		{
			$cache = Zend_Registry::get('Zend_Translate');
			$cache::clearCache();
			
			$this->_helper->flashMessenger(__('The translations cache has been reset.'), 'success');
			$this->_helper->redirector('index', 'index');
		}

		public function maintenanceAction()
		{
			$op = $this->getRequest()->getParam('op');
			if ($op == 'enable') {
				set_option('admin_tools_maintenance_active', 1);
				$this->_helper->flashMessenger(__('The "Under Maintenance" sign is on, and access to the website has been limited.'), 'success');
			} else {
				set_option('admin_tools_maintenance_active', 0);
				$this->_helper->flashMessenger(__('The website is online again.'), 'success');
			}
			
			$this->_helper->redirector('index', 'index');
		}

		public function trimSessionsAction()
		{
			$rng = $this->getRequest()->getParam('rng');
			
			if ($rng == 'expired') {
				if ($this->_trimSessionsTable($rng)) {
					$this->_helper->flashMessenger(__('Omeka\'s Sessions table has been trimmed up to all unexpired sessions.'), 'success');
				}
			} elseif ($rng != '') {
				if ($this->_trimSessionsTable($rng)) {
					$this->_helper->flashMessenger(__('Omeka\'s Sessions table has been trimmed up to 1 %s ago.', __($rng)), 'success');
				}
			}
			
			$this->_helper->redirector('index', 'index');
		}
		
		public function deleteTagsAction()
		{
			$this->_deleteUnusedTags();
			$this->_helper->redirector('index', 'index');
		}

		public function deleteTagsBrowseAction()
		{
			$this->_deleteUnusedTags();
			$this->_helper->redirector('browse','tags','');
		}

		private function _deleteUnusedTags()
		{
			$db = get_db();
			$query = 'DELETE FROM ' . $db->getTableName('Tag') . ' WHERE id IN (SELECT id FROM (SELECT t1.id FROM ' . $db->getTableName('Tag') . ' t1 LEFT OUTER JOIN ' . $db->getTableName('RecordsTag') . ' rt ON t1.id = rt.tag_id GROUP BY name HAVING COUNT(rt.id) = 0) tmp)';
			$affected = $db->query($query)->rowCount();

			if ($affected == 1) {
				$this->_helper->flashMessenger(__('1 unused tag has been deleted.', $affected), 'success');
			} elseif ($affected > 1 ) {
				$this->_helper->flashMessenger(__('All %s unused tags have been deleted.', $affected), 'success');
			} else {
				$this->_helper->flashMessenger(__('No unused tag was found.'), 'alert');
			}
		}
			
		private function _getLastBackupDateTime()
		{
			$sqlFilename = ADMIN_TOOLS_BACKUP_FILENAME;
			$gzipFilename = str_replace('.sql', '.gz', ADMIN_TOOLS_BACKUP_FILENAME);
			if (file_exists($sqlFilename)) {
				$sqlFileMTime = filemtime($sqlFilename);
				if (file_exists($gzipFilename)) {
					$gzipFileMTime = filemtime($gzipFilename);
					if ($sqlFileMTime > $gzipFileMTime) {
						return $this->_getLastBackupDateTimeString($sqlFileMTime);
					} else {
						return $this->_getLastBackupDateTimeString($gzipFileMTime);
					}
				} else {
					return $this->_getLastBackupDateTimeString($sqlFileMTime);
				}
			} elseif (file_exists($gzipFilename)) {
				$gzipFileMTime = filemtime($gzipFilename);
				return $this->_getLastBackupDateTimeString($gzipFileMTime);
			} else {
				return null;
			}
		}
		
		private function _getLastBackupDateTimeString($mtime)
		{
			return ' (' . __('last backup was created on %s at %s', date('d/m/Y', $mtime), date('H:i:s', $mtime)) . ')';
		}
		
		private function _getSessionsCount() 
		{
			$db = get_db();
			return $db->getTable('Session')->count();			
		}
		
		private function _getSessionMaxLifeTime()
		{
			$applicationFile = '../application/config/application.ini';
			if (!file_exists($applicationFile)) {
				throw new Zend_Config_Exception(__('Your Omeka application configuration file is missing.'));
			}
			if (!is_readable($applicationFile)) {
				throw new Zend_Config_Exception(__('Your Omeka application configuration file cannot be read by the application.'));
			}
			$sessionIni = new Zend_Config_Ini($applicationFile, 'production');
			$sessionParams = $sessionIni->toArray();
			return $sessionParams['resources']['session']['gc_maxlifetime'];
		}

		private function _trimSessionsTable($rng)
		{
			$date = new DateTime();
			$db = get_db();
			
			switch($rng) {
				case 'week':
					$query = 'DELETE FROM ' . $db->getTableName('Session') . ' WHERE modified < ' . $date->modify("-1 week")->getTimeStamp();
					break;
				case 'month':
					$query = 'DELETE FROM ' . $db->getTableName('Session') . ' WHERE modified < ' . $date->modify("-1 month")->getTimeStamp();
					break;
				case 'year':
					$query = 'DELETE FROM ' . $db->getTableName('Session') . ' WHERE modified < ' . $date->modify("-1 year")->getTimeStamp();
					break;
				case 'expired':
					$query = 'DELETE FROM ' . $db->getTableName('Session') . ' WHERE modified+lifetime < ' . $date->getTimeStamp();
					break;
				default:
					return false;
			}

			$db->query($query);
			return true;
		}
	}
?>
