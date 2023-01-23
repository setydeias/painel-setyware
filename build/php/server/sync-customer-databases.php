<?php
    set_time_limit(0);
    error_reporting(E_ALL);

    try {
        include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/CloudServer.class.php';
        include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/DirManager.class.php';
        $CloudServer = new CloudServer();
        $CloudServer->connect();

        $DirManager = new DirManager();
        $laboratorio = $DirManager->getDirs(array('LABORATORIO'))['LABORATORIO'][0];
        
        $downloaded = $CloudServer->get('./xampp/htdocs/app/2via/clientes/', $laboratorio, array('zip'));

        echo json_encode(array('success' => $downloaded ? $downloaded : false));
    } catch ( Exception $e ) {
        echo $e->getMessage();
    }