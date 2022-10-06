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
						$this->backupDB();
						break;
					case "TSTW":
						$this->trimSessionsTable('W');
						$message = __('Omeka\'s Sessions table has been trimmed up to 1 week ago.');
					case "TSTM":
						$this->trimSessionsTable('M');
						$message = __('Omeka\'s Sessions table has been trimmed up to 1 month ago.');
					case "TSTY":
						$this->trimSessionsTable('Y');
						$message = __('Omeka\'s Sessions table has been trimmed up to 1 year ago.');
						break;
				}
			}
			
			if ($message != '') {
				$flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
				$flash->addMessage($message, 'success');
			}
			
			$this->view->sessionsCount = $this->_getSessionsCount();
		}
		
		public function backupDB()
		{
			$db = get_db();
			$db->setFetchMode(Zend_Db::FETCH_NUM);

			$handle = fopen(ADMIN_TOOLS_BACKUP_FILENAME, 'w+');

			// Retrieve all tables
			$query = 'SHOW TABLES';
			$tables = $db->query($query)->fetchAll();
			
			// Iterate over each database table
			foreach ($tables as $table)
			{
				$table = $table[0];

				// Add comment
				$return = '/* Table ' . $table . ' /nnnn';

				// Delete table
				$return .= 'DROP TABLE IF EXISTS ' . $table . ';nnnn';

				// Create table
				$query = 'SHOW CREATE TABLE ' . $table;
				$create = $db->query($query)->fetch();
				$return .= $create[1] . ';nnnn';

				// Populate table
				$return .= 'INSERT INTO ' . $table . ' VALUES nnnn';

				fwrite($handle, str_replace('nnnn', PHP_EOL, $return));
				$return = '';
				
				$query = 'SELECT * FROM ' . $table;
				$stmt = $db->query($query);
				$count = $stmt->rowCount();
				$j = 1;
				while ($row = $stmt->fetch()) {
					$num_fields = count($row);
					for ($i = 0 ; $i < $num_fields ; $i++)
					{
						if ($i == 0) {
							$return = '(';
						}
						
						$row[$i] = addslashes($row[$i]);
						if (isset($row[$i])) {
							$return .= '"' . $row[$i] . '"';
						} else {
							$return .= '""';
						}
						if ($i < ($num_fields - 1)) {
							$return .= ',';
						} else {
							$return .= ')';
						}
					}
					
					if ($j < $count) {
						$return .= ',nnnn';
					} else {
						$return .= ';nnnn';
					}

					fwrite($handle, str_replace('nnnn', PHP_EOL, $return));
					$return = '';
					
					$j++;
				}
				
				$return = 'nnnnnnnn';
				fwrite($handle, str_replace('nnnn', PHP_EOL, $return));
			}		

			fclose($handle);
					
			// Restore default fetch mode
			$db->setFetchMode(Zend_Db::FETCH_ASSOC);

			$flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
			$flash->addMessage(__('A backup copy of the Omeka database has been created.'), 'success');

			if (get_option('admin_tools_backup_download')) {
				header('Content-type: text/plain');
				header('Content-Disposition: attachment; filename="OmekaDB-backup_' . date('Ymd_His') . '.sql"');
				readfile(ADMIN_TOOLS_BACKUP_FILENAME);
				exit;
			}
		}
		
		private function _getSessionsCount() 
		{
			$table = $this->_helper->db->getTable('Session');
			return $table->count();			
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
			$timestamp = $date->getTimeStamp();
			$table = $db->getTableName('Session');
			$query = 'DELETE FROM ' . $table . ' WHERE modified < ' . $timestamp;
			$db->query($query);
		}
	}
?>
