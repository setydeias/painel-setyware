<?php

    include_once $_SERVER['DOCUMENT_ROOT']."/painel/build/php/class/FirebirdCRUD.class.php";

    class DirManagerDAO {

        public $con;

        public function __construct() {
            $this->con = new FirebirdCRUD();
        }

        /*
        * Retorna os diretórios informados no array em @dirs var
        */
        public function getDirs(array $dirs) {
            try {
                $dirs = implode(', ', $dirs);
                $selectDirs = array('table' => 'DIRETORIOS d', 'params' => $dirs);
                return $this->con->Select($selectDirs);
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

    }