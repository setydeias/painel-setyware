<?php
    error_reporting(0);
    $data = json_decode(file_get_contents('php://input'));
    $usuario = $data->usuario;

    if ( strlen($usuario) != 8 ) {
        echo json_encode(array('error' => 'O campo usuário deve conter 8 caracteres'));
    } else {
        include_once '../class/Login2Via.class.php';
        //Verifica se o usuário existe
        $login2via = new Login2Via();
        $user = $login2via->findUser($usuario);
        if ( $user ) {
            $sigla = strtoupper(substr($usuario, 0, 3));
            //Conecta ao banco local
            $con = ibase_connect("localhost:C:/Setydeias/Setyware/ADM77777/ADM77777.GDB", "SYSDBA", "masterkey");
            $query = "SELECT s.REPASSE_VARIACAO FROM SACADOS s WHERE s.CLI_SIGLA = '$sigla'";
            $result = ibase_query($con, $query);
            while ( $row = ibase_fetch_object($result) ) $pathname = $sigla.str_pad($row->REPASSE_VARIACAO, 5, '0', STR_PAD_LEFT);
            ibase_close($con);
            //Conecta ao banco nas nuvens
            $con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
            $query = ibase_query($con, "SELECT sv.IP_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
            $ip_server = ibase_fetch_object($query)->IP_SERVER;
            ibase_close($con);
            $con = ibase_connect("$ip_server:E:/ServidorWeb/xampp/htdocs/app/2via/clientes/$pathname/$pathname.GDB", "SYSDBA", "masterkey");
            $codsac = (int) substr($usuario, 3, 5);
            $query = "SELECT s.CODAUXSAC, s.DOCSAC, s.NOMSAC, s.ENDSAC, s.CIDSAC, s.UFSAC FROM SACADOS s WHERE s.CODSAC = '$codsac'";
            $result = ibase_query($query);
            $user_data = array();
            while ( $row = ibase_fetch_object($result) ) {
                $user_data['COD_AUX'] = $row->CODAUXSAC;
                $user_data['DOCUMENTO'] = $row->DOCSAC;
                $user_data['NOME'] = utf8_encode($row->NOMSAC);
                $user_data['ENDERECO'] = utf8_encode("{$row->ENDSAC} - {$row->CIDSAC}, {$row->UFSAC}");
            }
            ibase_close($con);
            echo json_encode(array('success' => true, 'data' => $user_data));
        } else {
            echo json_encode(array('success' => false, 'error' => 'Nenhum usuário foi encontrado'));
        }
    }