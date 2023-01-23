<?php
    error_reporting(0);
    $data = json_decode(file_get_contents('php://input'), true);
    $customer = $data['customer'];
    $dir = "C:/Setydeias/banco_modelo";
    if ( !is_dir($dir) ) {
        mkdir($dir);
    }
    $modelo = "$dir/modelo.gdb";
    $database = "$dir/bases_geradas/$customer.gdb";
    $status = array();

    //Creating a copy
    try {
        if ( !copy($modelo, $database) ) {
            mkdir("$dir/bases_geradas");
            if ( !copy($modelo, $database) ) {
                $status['copied'] = false;
                $status['message'] = "Erro ao criar a cópia do banco de dados, tente novamente";
            } else {
                $status['copied'] = true;
                $status['message'] = "Banco de dados criado com sucesso";
                $status['database'] = $database;
            }
        } else {
            $status['copied'] = true;
            $status['message'] = "Banco de dados criado com sucesso";
            $status['database'] = $database;
        }
    } catch (Exception $e) {
        $status['copied'] = false;
        $status['message'] = $e->getMessage();
    }

    echo json_encode($status);