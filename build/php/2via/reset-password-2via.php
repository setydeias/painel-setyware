<?php

    $data = json_decode(file_get_contents('php://input'));
    $usuario = $data->usuario;

    if ( strlen($usuario) != 8 ) {
        echo json_encode(array('error' => 'O campo usuário deve conter 8 caracteres'));
        return false;
    } else {

        include_once '../class/FirebirdCRUD.class.php';
        $con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
        $query = ibase_query($con, "SELECT sv.IP_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
        $ip_server = ibase_fetch_object($query)->IP_SERVER;
        ibase_close($con);

        $crud = new FirebirdCRUD(array(
            'driver' => 'firebird',
            'dbname' => "$ip_server:E:\\ServidorWeb\\xampp\\htdocs\\app\\2via\\clientes\\banco-senhas\\LOGIN.FDB",
            'charset' => 'WIN1252',
            'user' => 'SYSDBA',
            'password' => 'masterkey'
            ));
        
        $dataToDelete = array(
            'table' => 'USERS s',
            'columns' => array(
                's.USUARIO' => $usuario
                ),
            'messageInSuccess' => 'Senha resetada com sucesso'
            );
        
        $result = $crud->Delete($dataToDelete);
        
        if ( $result['success'] ) {
            echo json_encode(array('status' => $result['status']));
            return true;
        } else {
            echo json_encode(array('error' => 'A senha não foi resetada, tente novamente ou contacte o desenvolvedor'));
            return false;
        }
    }