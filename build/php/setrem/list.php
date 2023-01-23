<?php
    error_reporting(0);
    include_once '../class/FirebirdCRUD.class.php';
    include_once '../class/Util.class.php';
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

    $dataToSelect = array(
        'table' => 'REMESSAS r',
        'params' => 'FIRST 15 r.ID_REGISTRO, r.NOME_REM, r.QTDE_TITULOS, r.DATA_PROCESSAMENTO, r.HORA_PROCESSAMENTO, r.EXPORTADO',
        'where' => array('r.EXPORTADO' => 'S'),
        'order' => array('param_order' => 'r.DATA_PROCESSAMENTO', 'order_by' => 'DESC')
    );

    $data = $crud->Select($dataToSelect);
    $remessas = array();

    for ( $i = 0 ; $i < count($data['ID_REGISTRO']) ; $i++ ) {
        $remessas[] = array(
            'ID_REGISTRO' => $data['ID_REGISTRO'][$i],
            'NOME_REM' => $data['NOME_REM'][$i],
            'QTDE_TITULOS' => $data['QTDE_TITULOS'][$i],
            'DATA_PROCESSAMENTO' => Util::FmtDate($data['DATA_PROCESSAMENTO'][$i], '20'),
            'HORA_PROCESSAMENTO' => $data['HORA_PROCESSAMENTO'][$i],
            'EXPORTADO' => $data['EXPORTADO'][$i]
        );
    }
    
    echo json_encode($remessas);