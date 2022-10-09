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
						$dbFile = '..\db.ini';
						if (!file_exists($dbFile)) {
							throw new Zend_Config_Exception('Your Omeka database configuration file is missing.');
						}
						if (!is_readable($dbFile)) {
							throw new Zend_Config_Exception('Your Omeka database configuration file cannot be read by the application.');
						}
						$dbIni = new Zend_Config_Ini($dbFile, 'database');
						$connectionParams = $dbIni->toArray();
						$isCompressed = get_option('admin_tools_backup_compress');
						$outputFile = ($isCompressed ? str_replace('.sql', '.gz', ADMIN_TOOLS_BACKUP_FILENAME) : ADMIN_TOOLS_BACKUP_FILENAME);
						
						include_once('src/Ifsnop/Mysqldump/Mysqldump.php');
						
						$dumper = new Ifsnop\Mysqldump\Mysqldump(
							'mysql:host=' . $connectionParams['host'] . ';dbname=' . $connectionParams['dbname'], 
							$connectionParams['username'], 
							$connectionParams['password'],
							array(
								'compress' => ($isCompressed ? Ifsnop\Mysqldump\Mysqldump::GZIP : Ifsnop\Mysqldump\Mysqldump::NONE)
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
						//$this->backupDB();
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
				}
			}
			
			if ($message != '') {
				$flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
				$flash->addMessage($message, 'success');
			}
			
			$this->view->sessionsCount = $this->_getSessionsCount();
		}

		
		private function _getSessionsCount() 
		{
			$db = get_db();
			return $db->getTable('Session')->count();			
		}
		
		public function trimSessionsTable($period)
		{
			$date = new DateTime();
			switch($period) {
				case 'W':
					$date->modify("-1 week");
					break;
				case 'M':
					$date->modify("-1 month");
					break;
				case 'Y':
					$date->modify("-1 year");
					break;
				default:
					return false;
			}
			
			$db = get_db();
			$query = 'DELETE FROM ' . $db->getTableName('Session') . ' WHERE modified > ' . $date->getTimeStamp();
			$db->query($query);
			
			return true;
		}
	}
?>
