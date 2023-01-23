<?php

	class FirebirdConnection {
	 
		protected static $db;
		
		private function __construct($host = null) {
			try {
				if ( !is_null($host) && is_array($host)) {
					$stmt = array(
						'driver' => $host['driver'],
						'dbname' => $host['dbname'],
						'charset' => $host['charset'],
						'user' => $host['user'],
						'password' => $host['password']
						);
				} else {
					$stmt = array(
						'driver' => 'firebird',
						'dbname' => 'localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB',
						'charset' => 'WIN1252',
						'user' => 'SYSDBA',
						'password' => 'masterkey'
						);
				}
				
				$connection_string = $stmt['driver'].":dbname=".$stmt['dbname'].";charset=".$stmt['charset'];

				self::$db = new PDO($connection_string, "{$stmt['user']}", "{$stmt['password']}");
				self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			} catch (PDOException $e) {
				echo "Connection Error: " . $e->getMessage();
			}
		}

		public function __destruct() {}
		 
		public static function getConnection($host) {

			if (!self::$db) {
				new FirebirdConnection($host);
			}
		
			return self::$db;
		}
	 
	}