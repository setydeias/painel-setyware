<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/FirebirdCRUD.class.php';

    class CloudServerDAO {

        public function __construct() {
            $this->crud = new FirebirdCRUD();
        }

        public function __destruct() {
            $this->crud = null;
        }

        public function get() {
            
            $data = $this->crud->Select(array(
                'table' => 'SERVIDOR_NUVENS_PARAMS snp',
                'params' => 'snp.IP_SERVER, snp.PASSWORD_SERVER, snp.FTP_LOGIN'
            ));
            
            return array(
                'IP_SERVER' => $data['IP_SERVER'][0],
                'PASSWORD_SERVER' => $data['PASSWORD_SERVER'][0],
                'FTP_LOGIN' => $data['FTP_LOGIN'][0]
            );
        }

    }