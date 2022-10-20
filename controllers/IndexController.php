<?php
	class AdminTools_IndexController extends Omeka_Controller_AbstractActionController
	{
		public function indexAction()
		{
			$message = '';
			
			if (isset($_GET["op"])) {
				switch ($_GET["op"]) {
					case "SUM-disable":
						set_option('admin_tools_maintenance_active', 0);
						$message = __('The website is online again.');
						break;
					case "SUM-enable":
						set_option('admin_tools_maintenance_active', 1);
						$message = __('The "Under Maintenance" sign is on, and access to the website has been limited.');
						break;
					case "RC":
						$cache = Zend_Registry::get('Zend_Translate');
						$cache::clearCache();
						$message = __('The translations cache has been reset.');
						break;
					case "BD":
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
						$message = __('A %s backup copy of the Omeka database has been created.', ($isCompressed ? __('compressed') : ''));
		
						if (get_option('admin_tools_backup_download') && file_exists($outputFile)) {
							header('Content-type: ' . ($isCompressed ? 'application/gzip' : 'text/plain'));
							header('Content-Disposition: attachment; filename="OmekaDB-backup_' . date('Ymd_His') . ($isCompressed ? '.gz' : '.sql') . '"');
							header('Content-Length: ' . filesize($outputFile));
							$myInputStream = fopen($outputFile, 'rb');
							$myOutputStream = fopen('php://output', 'wb');
							stream_copy_to_stream($myInputStream, $myOutputStream);
							fclose($myOutputStream);
							fclose($myInputStream);
							
							exit;
						}
						break;
					case "TSTW":
						if ($this->trimSessionsTable('W')) {
							$message = __('Omeka\'s Sessions table has been trimmed up to 1 week ago.');
						}
						break;
					case "TSTM":
						if ($this->trimSessionsTable('M')) {
							$message = __('Omeka\'s Sessions table has been trimmed up to 1 month ago.');
						}
						break;
					case "TSTY":
						if ($this->trimSessionsTable('Y')) {
							$message = __('Omeka\'s Sessions table has been trimmed up to 1 year ago.');
						}
						break;
					case "TSTE":
						if ($this->trimSessionsTable('E')) {
							$message = __('Omeka\'s Sessions table has been trimmed up to all unexpired sessions.');
						}
						break;
				}
			}
			
			if ($message != '') {
				$flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
				$flash->addMessage($message, 'success');
			}
			
			if (get_option('admin_tools_sessions_count')) {
				$this->view->sessionsCount = $this->_getSessionsCount();
			}
			
			$this->view->sessionMaxLifeTime = number_format($this->_getSessionMaxLifeTime() / (60 * 60 * 24), 0);
		}

		
		private function _getSessionsCount() 
		{
			$db = get_db();
			return $db->getTable('Session')->count();			
		}
		
		public function _getSessionMaxLifeTime()
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
		
		public function trimSessionsTable($period)
		{
			$date = new DateTime();
			$db = get_db();

			switch($period) {
				case 'W':
					$query = 'DELETE FROM ' . $db->getTableName('Session') . ' WHERE modified < ' . $date->modify("-1 week")->getTimeStamp();
					break;
				case 'M':
					$query = 'DELETE FROM ' . $db->getTableName('Session') . ' WHERE modified < ' . $date->modify("-1 month")->getTimeStamp();
					break;
				case 'Y':
					$query = 'DELETE FROM ' . $db->getTableName('Session') . ' WHERE modified < ' . $date->modify("-1 year")->getTimeStamp();
					break;
				case 'E':
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
