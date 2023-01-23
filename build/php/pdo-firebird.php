<?php
	ini_set('error_reporting', E_WARNING);

	function connectPDO($banco) {
		$str_con = "firebird:dbname=localhost:$banco;host=localhost";
		try {
			$conn = new PDO($str_con, "SYSDBA", "masterkey");
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			return $conn;
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}