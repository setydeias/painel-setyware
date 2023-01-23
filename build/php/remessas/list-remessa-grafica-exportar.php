<?php

    ini_set('max_execution_time', -1);
    include_once '../class/Util.class.php';
    include_once '../class/CloudServerParams.class.php';
    $CloudServerParams = new CloudServerParams();
    $host_ip = CloudServerParams::getHost();
    $host = "$host_ip:E:/ServidorWeb/banco-setrem/REM.FDB";
    $connect = ibase_connect($host, 'SYSDBA', 'masterkey');
    $query = "SELECT r.NOME_REMESSA, r.DATA_ENVIO, r.HORA_ENVIO FROM REMESSAS_GRAFICA r WHERE r.EXPORTADO = 'N'";
    $stmt = ibase_query($connect, $query);
    $remessas = array();

    while ( $row = ibase_fetch_object($stmt) ) {
        $row->DATA_ENVIO = Util::FmtDate($row->DATA_ENVIO, '20');
        $remessas[] = $row;
    }

    echo json_encode($remessas);