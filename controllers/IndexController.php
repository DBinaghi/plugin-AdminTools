<?php
	class AdminTools_IndexController extends Omeka_Controller_AbstractActionController
	{
		protected $sessionService;
		protected $pluginService;
		protected $tagService;
		protected $backupService;

		public function init()
		{
			require_once dirname(__FILE__) . '/../libraries/AdminTools/Service/BackupService.php';
			$this->backupService = new AdminTools_Service_BackupService();
			require_once dirname(__FILE__) . '/../libraries/AdminTools/Service/PluginService.php';
			$this->pluginService = new AdminTools_Service_PluginService();
			require_once dirname(__FILE__) . '/../libraries/AdminTools/Service/SessionService.php';
			$this->sessionService = new AdminTools_Service_SessionService();
			require_once dirname(__FILE__) . '/../libraries/AdminTools/Service/TagService.php';
			$this->tagService = new AdminTools_Service_TagService();
		}

		public function indexAction()
		{
			$csrf = new Omeka_Form_Element_SessionCsrfToken('csrf_token');
			$this->view->csrfToken = $csrf->getToken();

			$this->view->lastBackupDateTime = $this->backupService->getLastBackupDateTime();

			if (get_option('admin_tools_sessions_count')) {
				$this->view->sessionsCount = $this->sessionService->countAll();
				$this->view->sessionsTblSize = self::dbTableSize(get_db()->getTableName('Session'));
			}
			$this->view->sessionsMaxLifeTime = number_format($this->sessionService->maxLifeTime() / (86400), 0); // 86400 sec = 1 day
			if ((bool)get_option('admin_tools_sessions_graph')) {
				$rows = $this->sessionService->chartData((int)$this->view->sessionsMaxLifeTime);
				$ascisse  = [];
				$ordinate = [];
				foreach ($rows as $row) {
					$ascisse[]  = date_format(date_create($row['session_date']), 'd/m');
					$ordinate[] = (int)$row['total'];
				}
				$this->view->chartLabels = $ascisse;
				$this->view->chartData   = $ordinate;
			}
			$this->view->sessionsExpiredCount = $this->sessionService->countExpired();
			$this->view->sessionsYearCount = $this->sessionService->countByInterval('YEAR');
			$this->view->sessionsMonthCount = $this->sessionService->countByInterval('MONTH');
			$this->view->sessionsWeekCount = $this->sessionService->countByInterval('WEEK');
			$this->view->sessionsDayCount = $this->sessionService->countByInterval('DAY');
			
			$this->view->pluginsInstalled = $this->pluginService->countInstalled();
			$this->view->pluginsActive = $this->pluginService->countActive();
			$this->view->pluginsInvalid = $this->pluginService->countInvalid();
			$this->view->pluginsDescription = $this->pluginService->description();
			
			$this->view->itemsUntagged = $this->tagService->countUntaggedItems();
		}

		public function backupAction()
		{
			$options = [
				'compress'			=> (bool) get_option('admin_tools_backup_compress'),
				'memoryAllocated'	=> (int)  get_option('admin_tools_backup_memory'),
				'ignoreSessions'	=> (bool) get_option('admin_tools_backup_sessions_ignore'),
				'download'			=> (bool) get_option('admin_tools_backup_download')
			];

			try {
				$backupFile = $this->backupService->createBackup($options);

				if ($options['download']) {
					$this->_sendBackupFile($backupFile);
				}

				$this->_helper->flashMessenger(__('A %s backup copy of the Omeka database has been created.', ($options['compress'] ? __('compressed') : '')), 'success');
			} catch (\Exception $e) {
				$this->_helper->flashMessenger(
					__('Error during backup: %s', $e->getMessage()),
					'error'
				);
			}

			$this->_helper->redirector('index');
		}
		
		/**
		 * Internal helper to serve the backup file
		 */
		protected function _sendBackupFile(string $filePath)
		{
			if (!file_exists($filePath)) {
				throw new Zend_Controller_Action_Exception(__('Backup file not found.'), 404);
			}

			// Disable the theme layout and prevent view script rendering
			if ($this->_helper->hasHelper('viewRenderer')) {
				$this->_helper->viewRenderer->setNoRender(true);
			}

			// Avoid double compression
			if ($isCompressed) {
				if (function_exists('apache_setenv')) {
					@apache_setenv('no-gzip', 1);
				}
				ini_set('zlib.output_compression', 'Off');
			}

			// Cleans output buffer
			while (ob_get_level()) {
				ob_end_clean();
			}

			$chunksize = 5 * (1024 * 1024); // 5 MB (= 5 242 880 bytes) per one chunk of file
			set_time_limit(300);
			$size = filesize($filePath);
			$isCompressed = pathinfo($filePath, PATHINFO_EXTENSION) === 'gz';

			header('Content-Type: ' . ($isCompressed ? 'application/gzip' : 'application/sql'));
			header('Content-Disposition: attachment; filename="OmekaDB-backup_' . date('Ymd_His') . ($isCompressed ? '.sql.gz' : '.sql') . '"');
			header('Content-Length: ' . $size);

			if (intval(sprintf("%u", $size)) > $chunksize) { 
				$handle = fopen($filePath, 'rb');
				if ($handle === false) {
					throw new Zend_Controller_Action_Exception(__('Unable to open file.'), 500);
				}

				while ($buffer = fread($handle, $chunksize)) {
					echo $buffer;

					flush();
				}

				fclose($handle); 
			} else {
				readfile($filePath);
			}
			exit; // Terminates after download
		}
		
		public function resetCacheAction()
		{
			$cache = Zend_Registry::get('Zend_Translate');
			$cache::clearCache();

			$this->_helper->flashMessenger(__('The translations cache has been reset.'), 'success');
			$this->_helper->redirector('index');
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

			return $this->_helper->redirector('index');
		}

		public function sessionsTrimAction()
		{
			$range = $this->getRequest()->getParam('rng', 'expired');

			try {
				$removed = $this->sessionService->trimByRange($range);

				if ($removed === 1) {
					$this->_helper->flashMessenger(__('1 session removed.'), 'success');
				} elseif ($removed > 1) {
					$this->_helper->flashMessenger(__("%d sessions removed.", $removed), 'success');
				} else {
					$this->_helper->flashMessenger(__('No sessions to remove.'), 'alert');
				}
			} catch (Exception $e) {
				$this->_helper->flashMessenger($e->getMessage(), 'error');
			}

			return $this->_helper->redirector('index');
		}
		
		public function pluginsActivateAction()
		{
			if ($this->pluginService->activateAll()) {
				$this->_helper->flashMessenger(__('All installed Plugins are now active.'), 'success');
			} else {
				$this->_helper->flashMessenger(__('All installed Plugins were already active.'), 'alert');
			}	
						
			return $this->_helper->redirector('index');
		}

		public function pluginsActivateBrowseAction()
		{
			if ($this->pluginService->activateAll()) {
				$this->_helper->flashMessenger(__('All installed Plugins are now active.'), 'success');
			} else {
				$this->_helper->flashMessenger(__('All installed Plugins were already active.'), 'alert');
			}	
						
			return $this->_redirect('/plugins');
		}

		public function pluginsDeactivateAction()
		{
			if ($this->pluginService->deactivateAll()) {
				$this->_helper->flashMessenger(__('All installed Plugins are now inactive.'), 'success');
			} else {
				$this->_helper->flashMessenger(__('All installed Plugins were already inactive.'), 'alert');
			}	
						
			return $this->_redirect('/plugins');
		}

		public function pluginsDeactivateBrowseAction()
		{
			self::pluginsDeactivateAction();
		}
		
		public function pluginsRemoveInvalidAction()
		{
			if ($this->pluginService->removeInvalid()) {
				$this->_helper->flashMessenger(__('All invalid/damaged Plugins were removed.'), 'success');
			} else {
				$this->_helper->flashMessenger(__('No invalid/damaged Plugin was found to remove.'), 'alert');
			}
			
			return $this->_helper->redirector('index');
		}

		public function pluginsRemoveInvalidBrowseAction()
		{
			if ($this->pluginService->removeInvalid()) {
				$this->_helper->flashMessenger(__('All invalid/damaged Plugins were removed.'), 'success');
			} else {
				$this->_helper->flashMessenger(__('No invalid/damaged Plugin was found to remove.'), 'alert');
			}

			return $this->_redirect('/plugins');
		}
		
		/**
		 * Returns the size in MB of a specific table or all tables in the database.
		 *
		 * @param string $tbl_name Table name (optional). If empty, returns all tables.
		 * @return float|array Size in MB of the table, or associative array ['table_name' => size_MB]
		 * @throws InvalidArgumentException if the given table name does not exist in the database.
		 */
		public function dbTableSize(string $tbl_name = '')
		{
			$db = get_db();
			$db_name = $db->getAdapter()->getConfig()['dbname'];

			if ($tbl_name !== '') {
				// Check that the table exists in the current database
				$checkSql = "SELECT COUNT(*) FROM information_schema.TABLES 
							 WHERE table_schema = ? AND table_name = ?";
				$exists = $db->fetchOne($checkSql, [$db_name, $tbl_name]);

				if (!$exists) {
					throw new InvalidArgumentException(__("Table '%s' does not exist in database '%s'.", $tbl_name, $db_name));
				}

				// Retrieve the size of the single table
				$sql = "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
						FROM information_schema.TABLES
						WHERE table_schema = ? AND table_name = ?";
				$size = $db->fetchOne($sql, [$db_name, $tbl_name]);

				return (float) $size;
			}

			// No table name provided: return all tables with their sizes
			$sql = "SELECT table_name, 
						   ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
					FROM information_schema.TABLES
					WHERE table_schema = ?
					ORDER BY (data_length + index_length) DESC";
			$rows = $db->fetchAll($sql, [$db_name]);

			$result = [];
			foreach ($rows as $row) {
				$result[$row['table_name']] = (float) $row['size_mb'];
			}

			return $result;
		}
	}
?>
