<?php

    /*
    * @author: Bruno Pontes
    * @description: Classe criada com o intuito de verificar se determinado usuário existe no banco
    * de senhas alteradas
    */

    include_once 'FirebirdCRUD.class.php';

    class Login2Via {
        
        private $con;

        public function __construct() {
            $con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
            $query = ibase_query($con, "SELECT sv.IP_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
            $ip_server = ibase_fetch_object($query)->IP_SERVER;
            ibase_close($con);
            $this->con = new FirebirdCRUD(array(
                'driver' => 'firebird',
                'dbname' => "$ip_server:E:\\ServidorWeb\\xampp\\htdocs\\app\\2via\\clientes\\banco-senhas\\LOGIN.FDB",
                'charset' => 'WIN1252',
                'user' => 'SYSDBA',
                'password' => 'masterkey'
            ));
        }

        public function __destruct() {}

        public function findUser($user) {
            $SelectLogin = array(
                'table' => 'USERS u',
                'params' => 'u.USUARIO',
                'where' => array('u.USUARIO' => $user)
            );
        
            $usuario = $this->con->Select($SelectLogin);
            $exists = count($usuario) > 0 ? true : false;

            return $exists;
        }
    }