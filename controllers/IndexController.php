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
						$message = __('A backup copy of the Omeka database has been created.');
						break;
				}
			}
			
			if ($message != '') {
				$flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
				$flash->addMessage($message, 'success');
			}
		}
		
		public function backupDB()
		{
			$db = get_db();
			$db->setFetchMode(Zend_Db::FETCH_NUM);

			// Retrieve all tables
			$query = 'SHOW TABLES';
			$tables = $db->query($query)->fetchAll();

			// Iterate over each database table
			$return = '';
			foreach ($tables as $table)
			{
				$table = $table[0];

				// Add comment
				$return .= '/* Table ' . $table . ' /nnnn';

				// Delete table
				$return .= 'DROP TABLE IF EXISTS ' . $table . ';nnnn';

				// Create table
				$query = 'SHOW CREATE TABLE ' . $table;
				$create = $db->query($query)->fetch();
				$return .= $create[1] . ';nnnn';

				// Populate table
				$query = 'SELECT * FROM ' . $table;
				$select = $db->query($query)->fetchAll();
				if(count($select) > 0) {
					$num_fields = count($select[0]);
					for($i = 0 ; $i < count($select) ; $i++)
					{
						if ($i == 0) {
							$return .= 'INSERT INTO ' . $table . ' VALUES nnnn';
						}
						for($j = 0 ; $j < $num_fields ; $j++)
						{
							if($j == 0) {
								$return .= '(';
							}
							$select[$i][$j] = addslashes($select[$i][$j]);
							if (isset($select[$i][$j])) {
								$return .= '"' . $select[$i][$j] . '"';
							} else {
								$return .= '""';
							}
							if ($j < ($num_fields - 1)) {
								$return .= ',';
							}
						}
						if ($i == count($select) - 1) {
							$return .= ');nnnn';
						} else {
							$return .= '),nnnn';
						}
					}
				}
				$return .= 'nnnnnnnn';
			}		
					
			// Restore default mode
			$db->setFetchMode(Zend_Db::FETCH_ASSOC);

			// Save it into an SQL file
			$handle = fopen(ADMIN_TOOLS_BACKUP_FILENAME, 'w+');
			fwrite($handle, str_replace('nnnn', PHP_EOL, $return));
			fclose($handle);

			if (get_option('admin_tools_backup_download')) {
				header('Content-type: text/plain');
				header('Content-Disposition: attachment; filename="OmekaDB-backup_' . date('Ymd_His') . '.sql"');
				readfile(ADMIN_TOOLS_BACKUP_FILENAME);
				exit;
			}
	  
			// $this->view->fileName = ADMIN_TOOLS_BACKUP_FILENAME;
		}
	}
?>
