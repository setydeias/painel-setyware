<?php
    include_once('../class/Util.class.php');
    $con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
    $query = ibase_query($con, "SELECT sv.IP_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
    $ip_server = ibase_fetch_object($query)->IP_SERVER;
    ibase_close($con);
    
    $host = "$ip_server:E:/ServidorWeb/banco-setrem/REM.FDB";
    $connect = ibase_connect($host, 'SYSDBA', 'masterkey');
    $query = "SELECT r.NOME_REM, r.QTDE_TITULOS, r.VALOR_TOTAL, r.DATA_PROCESSAMENTO, r.HORA_PROCESSAMENTO FROM REMESSAS r WHERE r.EXPORTADO = 'N'";
    $stmt = ibase_query($connect, $query);
    $remessas = array();

    while ( $row = ibase_fetch_object($stmt) ) {
        $row->DATA_PROCESSAMENTO = Util::FmtDate($row->DATA_PROCESSAMENTO, '20');
        $remessas[] = $row;
    }

    echo json_encode($remessas);