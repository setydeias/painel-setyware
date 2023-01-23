<?php
    try {
        error_reporting(0);
        include_once '../class/FirebirdCRUD.class.php';
        $data = json_decode(file_get_contents('php://input'));
        $id = $data->id;
        $con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
        $query = ibase_query($con, "SELECT sv.IP_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
        $ip_server = ibase_fetch_object($query)->IP_SERVER;
        ibase_close($con);

        $crud = new FirebirdCRUD(array(
            'driver' => 'firebird',
            'dbname' => "$ip_server:E:\\ServidorWeb\\banco-setrem\\REM.FDB",
            'charset' => 'WIN1252',
            'user' => 'SYSDBA',
            'password' => 'masterkey'
        ));

        $dataToUpdate = array(
            'table' => 'REMESSAS r',
            'set' => array('r.EXPORTADO' => 'N'),
            'where' => array('r.ID_REGISTRO' => $id)
        );

        $data = $crud->Update($dataToUpdate);

        echo json_encode(array(
            'success' => $data['success'],
            'message' => $data['success'] ? 'Arquivo selecionado está disponível para exportação' : $data['status']
        ));
    } catch ( Exception $e ) {
        echo $e->getMessage();
    } 