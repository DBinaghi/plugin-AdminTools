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
			$isCompressed = (bool)get_option('admin_tools_backup_compress');
			$outputFile = ($isCompressed ? ADMIN_TOOLS_BACKUP_FILENAME . '.gz' : ADMIN_TOOLS_BACKUP_FILENAME);
			$memoryAllocated = (int)get_option('admin_tools_backup_memory');

			// preserve original memory value
			$old_limit = ini_get('memory_limit');
			
			// set config memory value
			if ($memoryAllocated > 0) ini_set('memory_limit', $memoryAllocated . 'M');

			// create db dump
			$dumper = new Mysqldump\Mysqldump(
				'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'], 
				$dbConfig['username'], 
				$dbConfig['password'],
				array(
					'compress' => ($isCompressed ? Mysqldump\Mysqldump::GZIP : Mysqldump\Mysqldump::NONE)
				)
			);

			if ((bool)get_option('admin_tools_backup_sessions_ignore')) {
				$dumper->setTableLimits(array(
					get_db()->getTableName('Session') => 0
				));
			}

			$dumper->start($outputFile);

			// if changed, restore original memory value
			if ($memoryAllocated > 0) ini_set('memory_limit', $old_limit);

			if ((bool)get_option('admin_tools_backup_download')) {
				if (file_exists($outputFile)) {
					// 1. Disable the theme layout and prevent view script rendering
					if ($this->_helper->hasHelper('viewRenderer')) {
						$this->_helper->viewRenderer->setNoRender(true);
					}

					// 2. Pulizia totale del buffer; elimina qualsiasi CSS o HTML già generato da Omeka
					while (ob_get_level()) {
						ob_end_clean();
					}

					// 3. Clear any existing response body/headers to be safe
					$response = $this->getResponse();
					// $response->clearAllHeaders();
					// $response->clearBody();

					// 4. Set final headers and file content
					$response->setHeader('Content-Type', 'text/plain', true)
							 ->setHeader('Content-Disposition', 'attachment; filename="OmekaDB-backup_' . date('Ymd_His') . ($isCompressed ? '.sql.gz' : '.sql') . '"')
							 ->setHeader('Content-Length', filesize($outputFile))
							 ->setHeader('Expires', 0)
							 ->setHeader('Cache-Control', 'must-revalidate')
							 ->setHeader('Pragma', 'public')
							 ->setBody(file_get_contents($outputFile));
							 
					// Force sending the response immediately
					$response->sendResponse();

					exit;
				} else {
					throw new Zend_Controller_Action_Exception(__('File not found.'), 404);
				}
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
			$this->_helper->redirector->gotoUrl(url('../tags/browse'));
		}

		public function pluginsActivateAction()
		{
			$this->_activatePlugins();
			$this->_helper->redirector('index', 'index');
		}

		public function pluginsActivateBrowseAction()
		{
			$this->_activatePlugins();
			$this->_helper->redirector->gotoUrl(url('../plugins/browse'));
		}

		public function pluginsDeactivateAction()
		{
			$this->_deactivatePlugins();
			$this->_helper->redirector->gotoUrl(url('../plugins/browse'));
		}

		public function pluginsDeactivateBrowseAction()
		{
			$this->_deactivatePlugins();
			$this->_helper->redirector->gotoUrl(url('../plugins/browse'));
		}

		public function pluginsRemoveDamagedAction()
		{
			$this->_removeDamagedPlugins();
			$this->_helper->redirector->gotoUrl(url('../plugins/browse'));
		}

		public function pluginsRemoveDamagedBrowseAction()
		{
			$this->_removeDamagedPlugins();
			$this->_helper->redirector->gotoUrl(url('../plugins/browse'));
		}

		private function _activatePlugins()
		{
            $db = get_db();
			$query = 'UPDATE ' . $db->getTableName('Plugin') . ' SET active = 1';
			$affected = $db->query($query)->rowCount();

            if ($affected > 0) {
				$this->_helper->flashMessenger(__('All Plugins are now active.'), 'success');
            } else {
				$this->_helper->flashMessenger(__('All installed Plugins were already active.'), 'alert');
            }    
		}

		private function _deactivatePlugins()
		{
			$db = get_db();
			$query = 'UPDATE ' . $db->getTableName('Plugin') . ' SET active = 0';
			$affected = $db->query($query)->rowCount();

            if ($affected > 0) {
				$this->_helper->flashMessenger(__('All Plugins are now inactive.'), 'success');
            } else {
				$this->_helper->flashMessenger(__('All installed Plugins were already inactive.'), 'alert');
            }    
		}

		private function _removeDamagedPlugins()
		{
			$db = get_db();
			$path = PLUGIN_DIR;
			$directories = str_replace($path . '/', '', glob($path . '/*', GLOB_ONLYDIR));
			$query = "DELETE FROM " . $db->getTableName('Plugin') . " WHERE name NOT IN ('" . implode("','", $directories) . "')";
			$affected = $db->query($query)->rowCount();


            if ($affected > 0) {
				$this->_helper->flashMessenger(__('All invalid/damaged Plugins were removed.'), 'success');
            } else {
				$this->_helper->flashMessenger(__('No invalid/damaged Plugin was found to remove.'), 'alert');
            }    
		}

		private function _deleteUnusedTags()
		{
			$db = get_db();
			$query = 'DELETE FROM ' . $db->getTableName('Tag') . ' WHERE id IN (SELECT id FROM (SELECT t1.id FROM ' . $db->getTableName('Tag') . ' t1 LEFT OUTER JOIN ' . $db->getTableName('RecordsTag') . ' rt ON t1.id = rt.tag_id GROUP BY t1.id HAVING COUNT(rt.id) = 0) tmp)';
			$affected = $db->query($query)->rowCount();

			if ($affected == 1) {
				$this->_helper->flashMessenger(__('1 unused Tag has been deleted.', $affected), 'success');
			} elseif ($affected > 1 ) {
				$this->_helper->flashMessenger(__('All %s unused Tags have been deleted.', $affected), 'success');
			} else {
				$this->_helper->flashMessenger(__('No unused Tag was found.'), 'alert');
			}
		}

		private function _getLastBackupDateTime()
		{
			$sqlFilename = ADMIN_TOOLS_BACKUP_FILENAME;
			$gzipFilename = ADMIN_TOOLS_BACKUP_FILENAME . '.gz';
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
				case 'day':
					$query = 'DELETE FROM ' . $db->getTableName('Session') . ' WHERE modified < ' . $date->modify("-1 day")->getTimeStamp();
					break;
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
