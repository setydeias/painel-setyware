<?php

	try {		
		$conn = new PDO('mysql:host=localhost;port=3306;dbname=painel', 'styd', 'LGME2701');
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		return $conn;
	} catch(PDOException $e) {
	    echo 'ERROR: ' . $e->getMessage();
	}