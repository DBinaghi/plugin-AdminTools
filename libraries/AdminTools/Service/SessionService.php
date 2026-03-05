<?php
	/**
	 * Service for sessions management in AdminTools
	 */
	class AdminTools_Service_SessionService
	{
		public function __construct()
		{
			$this->db = get_db();
			$this->sessionTable = $this->db->getTableName('Session');
		}

		/**
		 * Count all sessions
		 */
		public function countAll(): int
		{
			return (int) $this->db->getTable('Session')->count();
		}

		/**
		 * Count all sessions outside a range (DAY, WEEK, MONTH, YEAR)
		 */
		public function countByInterval(string $range): int
		{
			$range = strtoupper($range);
			$validRanges = ['DAY', 'WEEK', 'MONTH', 'YEAR'];
			if (!in_array($range, $validRanges)) {
				throw new InvalidArgumentException(__("Invalid range: %s", $range));
			}

			$sql = "SELECT COUNT(*) 
					FROM {$this->sessionTable} 
					WHERE modified < UNIX_TIMESTAMP(NOW() - INTERVAL 1 $range)";
			return (int) $this->db->fetchOne($sql);
		}

		/**
		 * Count expired sessions
		 */
		public function countExpired(): int
		{
			$maxLife = $this->_maxLifeTime();
			$sql = "SELECT COUNT(*) FROM {$this->sessionTable} 
					WHERE modified + lifetime < UNIX_TIMESTAMP()";
			return (int) $this->db->fetchOne($sql);
		}

		/**
		 * Remove expired or out of range sessions
		 * @param string $range 'EXPIRED', 'DAY', 'WEEK', 'MONTH', 'YEAR'
		 * @return int Removed sessions number
		 */
		public function trimByInterval(string $range): int
		{
			$range = strtoupper($range);

			if ($range === 'expired') {
				$sql = "DELETE FROM {$this->sessionTable} 
						WHERE modified + lifetime < UNIX_TIMESTAMP()";
			} else {
				$validRanges = ['DAY', 'WEEK', 'MONTH', 'YEAR'];
				if (!in_array($range, $validRanges)) {
					throw new InvalidArgumentException(__("Invalid range: %s", $range));
				}
				$sql = "DELETE FROM {$this->sessionTable} 
						WHERE modified < UNIX_TIMESTAMP(NOW() - INTERVAL 1 $range)";
			}

			return $this->db->query($sql)->rowCount();
		}

		/**
		 * Remove all sessions
		 */
		public function trimAll(): int
		{
			$sql = "DELETE FROM {$this->sessionTable}";
			return $this->db->query($sql)->rowCount();
		}
		
		public function maxLifeTime()
		{
			return $this->_maxLifeTime();
		}
		
		public function chartData($maxDays)
		{
			return $this->_chartData($maxDays);
		}

		/**
		 * Retrieve session's max lifetime from PHP.ini
		 */
		protected function _maxLifeTime(): int
		{
			$applicationFile = '../application/config/application.ini';
			if (!file_exists($applicationFile)) {
				throw new Zend_Config_Exception(__('Your Omeka application configuration file is missing.'));
			} elseif (!is_readable($applicationFile)) {
				throw new Zend_Config_Exception(__('Your Omeka application configuration file cannot be read by the application.'));
			}

			$sessionIni = new Zend_Config_Ini($applicationFile, 'production');
			$sessionParams = $sessionIni->toArray();

			return $sessionParams['resources']['session']['gc_maxlifetime'];
		}
		
		protected function _chartData($maxDays)
		{
			$db = get_db();
			$sessionTable = $db->getTableName('Session');
			$sql = "
				SELECT DATE(FROM_UNIXTIME(modified)) AS session_date, COUNT(id) AS total
				FROM {$sessionTable}
				WHERE modified >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ? DAY))
				GROUP BY session_date
				ORDER BY session_date ASC
			";

			return $db->fetchAll($sql, [(int)$maxDays]);
		}		
	}
?>
