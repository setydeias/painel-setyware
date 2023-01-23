<?php
    error_reporting(0);
    include_once '../class/FirebirdCRUD.class.php';
    include_once '../class/Util.class.php';
    $con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
    $query = ibase_query($con, "SELECT sv.IP_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
    $ip_server = ibase_fetch_object($query)->IP_SERVER;
    ibase_close($con);
    $data = json_decode(file_get_contents('php://input'));
    $customer = $data->customer;
    $de = $data->de;
    $ate = $data->ate;
    
    $filter = array();
    if ( strlen($customer) != 0 ) $filter[] = "SUBSTRING(r.NOME_REM FROM 1 FOR 3) = '$customer'";
    if ( strlen($de) != 0 && strlen($ate) != 0 ) $filter[] = "r.DATA_PROCESSAMENTO BETWEEN '".Util::FmtDate($de, '23')."' AND '".Util::FmtDate($ate, '23')."'";
    if ( strlen($de) != 0 && strlen($ate) == 0 ) $filter[] = "r.DATA_PROCESSAMENTO >= '".Util::FmtDate($de, '23')."'";
    $filter[] = "r.EXPORTADO = 'S'";
    $filter = implode(" AND ", $filter);
    
    $crud = new FirebirdCRUD(array(
        'driver' => 'firebird',
        'dbname' => "$ip_server:E:\\ServidorWeb\\banco-setrem\\REM.FDB",
        'charset' => 'WIN1252',
        'user' => 'SYSDBA',
        'password' => 'masterkey'
    ));

    $dataToSelect = array(
        'table' => 'REMESSAS r',
        'params' => "r.ID_REGISTRO, r.NOME_REM, r.QTDE_TITULOS, r.DATA_PROCESSAMENTO, r.HORA_PROCESSAMENTO, r.EXPORTADO",
        'where' => $filter,
        'order' => array('param_order' => 'r.DATA_PROCESSAMENTO', 'order_by' => 'DESC')
    );

    $data = $crud->Select($dataToSelect);
    
    if ( count($data) > 0 ) {
        $remessas = array();
        
        for ( $i = 0 ; $i < count($data['ID_REGISTRO']) ; $i++ ) {
            $remessas[] = array(
                'ID_REGISTRO' => $data['ID_REGISTRO'][$i],
                'NOME_REM' => $data['NOME_REM'][$i],
                'QTDE_TITULOS' => $data['QTDE_TITULOS'][$i],
                'DATA_PROCESSAMENTO' => Util::FmtDate($data['DATA_PROCESSAMENTO'][$i], '20'),
                'HORA_PROCESSAMENTO' => $data['HORA_PROCESSAMENTO'][$i]
            );
        }

        echo json_encode($remessas);
        return;
    }

    echo json_encode(array('error' => true, 'message' => 'Nenhuma remessa encontrada'));