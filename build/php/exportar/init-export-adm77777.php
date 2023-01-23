<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/DirManager.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/CloudServer.class.php';

    $dir = new DirManager();
    $CloudServer = new CloudServer();
    $CloudServer->connect();

    $path = $dir->getDirs(array('BANCO_ADM77777'))['BANCO_ADM77777'][0];

    $banco_nome = "ADM77777.GDB";
    $bancoadm77777 = "$path\\$banco_nome";

    $zip_name = "ADM77777_".uniqid().".zip";
    $zip_file = "$path\\$zip_name";

    $zip = new ZipArchive();
    
    if ( $zip->open($zip_file, ZipArchive::CREATE) === true ) {
        $zip->addFile($bancoadm77777, $banco_nome);
        $zip->close();

        if ( !$CloudServer->send($zip_file, "//ADM77777//$zip_name") ) {
            $data = array('success' => false, 'status' => 'O arquivo foi compactado porém não foi enviado, tente novamente');
        } else {
            $data = array('success' => true, 'status' => 'Arquivo compactado e enviado com sucesso');
        }

        if ( !rename($zip_file, "$path\\Backup\\$zip_name") ) {
            $data['rename'] = 'Não foi possível enviar o arquivo para a pasta de Backup (C:\Setydeias\Setyware\ADM77777\Backup\)';
        }

        echo json_encode($data);
    } else {
        echo json_encode(array('success' => false, 'status' => 'Não foi possível compactar o arquivo'));
    }