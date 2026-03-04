<?php
	class AdminTools_Service_BackupService
	{
		public function __construct()
		{
			$this->db = get_db();
		}
		
		public function createBackup(array $options): string
		{
			$config = $this->db->getAdapter()->getConfig();

			// Set memory
			$oldLimit = ini_get('memory_limit');
			if ($options['memory'] > 0) {
				ini_set('memory_limit', $options['memory'] . 'M');
			}

			$dumper = new Mysqldump\Mysqldump(
				'mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'],
				$config['username'],
				$config['password'],
				['compress' => $options['compress'] ? Mysqldump\Mysqldump::GZIP : Mysqldump\Mysqldump::NONE]
			);

			if ($options['ignoreSessions']) {
				$dumper->setTableLimits([$this->db->getTableName('Session') => 0]);
			}

			$outputFile = ADMIN_TOOLS_BACKUP_FILENAME . ($options['compress'] ? '.gz' : '');

			$dumper->start($outputFile);

			// Restore original memory
			if ($options['memory'] > 0) {
				ini_set('memory_limit', $oldLimit);
			}

			return $outputFile;
		}

		public function getLastBackupDateTime()
		{
			$files = [
				ADMIN_TOOLS_BACKUP_FILENAME,
				ADMIN_TOOLS_BACKUP_FILENAME . '.gz',
			];

			// Filter only existing files
			$existingFiles = array_filter($files, 'file_exists');

			if (empty($existingFiles)) {
				return null;
			}

			// Finds the most recent file
			$latestFile = array_reduce($existingFiles, function($carry, $file) {
				if ($carry === null || filemtime($file) > filemtime($carry)) {
					return $file;
				}
				return $carry;
			}, null);

			return $this->_getLastBackupDateTimeString(filemtime($latestFile), $latestFile);
		}
		
		protected function _getLastBackupDateTimeString($mtime, $filepath)
		{
			return ' (' . __('<a href="%s" title="%s">last backup</a> was created on %s at %s', $filepath, __('download file'), date('d/m/Y', $mtime), date('H:i:s', $mtime)) . ')';
		}

	}
?>