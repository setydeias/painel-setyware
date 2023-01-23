<?php

	spl_autoload_register(function ($class_name) {
	    include_once('assets/php/class/'.$class_name.'.class.php');
	});