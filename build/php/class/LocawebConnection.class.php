<?php

	include_once 'constants/locaweb_config.php';

	class LocawebConnection {
	 
		protected static $db;
		
		private function __construct() {
			try {
				self::$db = new PDO("mysql:host=".LW_HOST.";dbname=".LW_DATABASE, LW_LOGIN, LW_PASSWORD);
				self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				self::$db->setAttribute( PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8' );
			} catch (PDOException $e) {
				echo "Connection Error: " . $e->getMessage();
			}
		}
		 
		public static function getConnection() {

			if (!self::$db) {
				new LocawebConnection();
			}
		
			return self::$db;
		}
	 
	}