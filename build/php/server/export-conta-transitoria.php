<?php
    error_reporting(0);
    ini_set('max_execution_time', 0);
    include_once '../class/DirManager.class.php';
    include_once '../class/STYCom.class.php';
    include_once '../class/FirebirdCRUD.class.php';
    
    $DirManager = new DirManager();
    $STYCom = new STYCom();
    $crud = new FirebirdCRUD();

    try {
        $data = json_decode(file_get_contents('php://input'));
        $customers = $data->customers;
        $not_found = $transf = $not_transf = array();
        
        foreach ( $customers as $customer ) {
            $customer = strtolower($customer);
            $path = "C:\\contatransitoria\\$customer\\";
            if ( !is_dir($path) ) {
                $not_found[] = strtoupper($customer);
                continue;
            }
            
            $files = $DirManager->getFiles($path, array('xls', 'htm'));
            
            foreach ( $files as $file ) {
                $crud->Insert(array('table' => 'TEMP_MENSALIDADE', 'columns' => array('CUSTOMER' => strtoupper($customer))));
                $data = array('customer' => $customer, 'file' => $file);
                $STYCom->uploadContaTransitoria($data) ? $transf[] = strtoupper($customer) : $not_transf[] = strtoupper($customer);
            }
        }

        $crud->Delete(array('table' => 'TEMP_MENSALIDADE', 'where' => array(1 => 1)));

        echo json_encode(array(
            'not_found' => array_values(array_unique($not_found)),
            'success' => array_values(array_unique($transf)),
            'failure' => array_values(array_unique($not_transf))
        ));
    } catch ( Exception $e ) {
        $crud->Delete(array('table' => 'TEMP_MENSALIDADE', 'where' => array(1 => 1)));
        echo json_encode(array(
            'error' => "Erro ao exportar arquivos: {$e->getMessage()}"
        ));
    }