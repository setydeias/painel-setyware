<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/DirManager.class.php';
    $dir = new DirManager();
    $path = $dir->getDirs(array('BANCO_ADM77777'))['BANCO_ADM77777'][0];
    
    echo json_encode(array('path' => $path));
    