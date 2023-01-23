<?php
    try {
        error_reporting(0);
        include_once '../class/FirebirdCRUD.class.php';
        $data = json_decode(file_get_contents('php://input'));
        $id = $data->id;
        $path = "C:\\A";
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
            'params' => 'r.NOME_REM, r.ARQUIVO',
            'where' => array('r.ID_REGISTRO' => $id)
        );

        $data = $crud->Select($dataToSelect);
        
        if ( count($data) > 0 ) {
            $filename = $data['NOME_REM'][0];
            $filename = !file_exists("$path\\$filename.REM") ? "$filename.REM" : "$filename-".uniqid().".REM";
            $content = $data['ARQUIVO'][0];
            $fp = fopen("$path\\$filename", 'w+');
            $info = fwrite($fp, $content) 
                ? array( 'success' => true, 'message' => "O arquivo $path\\$filename foi baixado com sucesso" )
                : array( 'success' => false, 'message' => "O arquivo $path\\$filename não foi baixado" );
            fclose($fp);
            echo json_encode($info);
        } else {
            echo json_encode(array( 'success' => false, 'message' => "Remessa não encontrada" ));
        }
    } catch ( Exception $e ) {
        echo $e->getMessage();
    }