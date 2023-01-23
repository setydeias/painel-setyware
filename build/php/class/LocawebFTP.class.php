<?php

    include_once '../config/ftp_config.php';

    class LocawebFTP {

        public $connection;
        private $host;
        private $login;
        private $password;

        /*
        * CONSTRUCTOR AND DESTRUCTOT METHODS
        */

        public function __construct() {
            try {
                //Getting parameters
                $this->setHost(HOST);
                $this->setLogin(LOGIN);
                $this->setPassword(PASSWORD);
                //Open FTP connection
                $this->connection = $this->connect();
                ftp_login($this->connection, $this->getLogin(), $this->getPassword());
                ftp_pasv($this->connection, true);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        public function __destruct() {}

        /*
        * SETTERS AND GETTERS
        */

        public function setHost($host) {
            $this->host = $host;
        }

        public function getHost() {
            return $this->host;
        }

        public function setLogin($login) {
            $this->login = $login;
        }

        public function getLogin() {
            return $this->login;
        }

        public function setPassword($password) {
            $this->password = $password;
        }

        public function getPassword() {
            return $this->password;
        }

        /*
        * METHODS
        */

        //Generates the connection FTP ID
        public function connect() {
            $con = ftp_connect($this->getHost());
            
            return $con;
        }

        //Send files to remote server
        //@from is a local file that will be send
        //@to is the received remote file
        public function send($from, $to) {
            try {
                $status = array();

                //Verifica se o diretório existe
                //Se não existir, cria
                $dir = array_slice(explode('/', $to), 0, -1); //String sem o nome do arquivo, apenas o nome do diretório
                $this->checkDirExists($dir);

                //Envia o arquivo para o servidor
                if ( ftp_put($this->connection, $to, $from, FTP_BINARY) ) {
                    $status['received'] = true;
                    $status['message'] = "O arquivo foi enviado com sucesso";
                } else {
                    $status['received'] = false;
                    $status['message'] = "Erro ao enviar o relatório de duplicidade para a hospedagem, tente novamente";
                }
            } catch (Exception $e) {
                $status['received'] = false;
                $status['message'] = $e->getMessage();
            }

            return $status;
        }
        
        //Verify if dir exists
        //If does not exists, create
        public function checkDirExists($dir) {
            $path = array_pop($dir); //Diretório ao qual será verificada a existência
            $dirList = ftp_nlist($this->connection, implode('/', $dir));
            
            if ( !in_array($path, $dirList) ) {
                $newDir = implode('/', $dir)."/".$path;
                if ( !ftp_mkdir($this->connection, $newDir) ) {
                    echo "<section>Erro ao criar o diretório na hospedagem, tente novamente</section>";
                }
            }
        }
    }