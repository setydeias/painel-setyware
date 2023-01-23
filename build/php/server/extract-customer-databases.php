<?php
    try {
        error_reporting(0);
        include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/DirManager.class.php';
        include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Customer.class.php';
        $customer = new Customer();
        $DirManager = new DirManager();

        $laboratorio = $DirManager->getDirs(array('LABORATORIO'))['LABORATORIO'][0];
        $zip_files = $DirManager->getFiles($laboratorio, array('zip'));
        $sync = $not_sync = array();
        $zip = new ZipArchive();
        
        foreach ( $zip_files as $zip_file ) {
            if ( $zip->open($zip_file) === true ) {
                //Faz um loop nos arquivos .zip para realizar as extrações
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i); //Nome do arquivo
                    $customer_pathname = strtoupper(substr($filename, 0, 8)); //Nome do cliente
                    $customer_sigla = substr($customer_pathname, 0, 3);
                    $pathname = "$laboratorio\\$customer_pathname";

                    //Extrai os arquivos dentro da pasta do cliente
                    if ( $zip->extractTo($pathname, $filename) ) {
                        if ( !in_array($customer_sigla, $sync) ) {
                            $sync[] = $customer_sigla;
                        }
                    } else {
                        if ( !in_array($customer_sigla, $not_sync) ) {
                            $not_sync[] = $customer_sigla;
                        }
                    }

                    //Se for arquivo zip
                    //Extrai o arquivo para sobrescrever o arquivo GDB
                    if ( pathinfo($filename, PATHINFO_EXTENSION) === 'zip' ) {
                        $zipFile = new ZipArchive();
                        $zipFile->extractTo($pathname, $filename);
                        $zipFile->close();
                        unlink("$pathname\\$filename");
                    }
                }
                $zip->close();
                unlink($zip_file);
            }
        }
        
        echo json_encode(array('sync' => $sync, 'not_sync' => $not_sync));
    } catch ( Exception $e ) {
        echo $e->getMessage();
    }