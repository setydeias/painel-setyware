<?php

    error_reporting(0);
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/AbstractRetornoGenerator.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/AbstractRemessaGenerator.class.php';

    class FileStrategy {

        public function __construct($tipo, $data) {
            $this->strategy = null;
            
            switch ( $tipo ) {
                case 'RETORNO':
                    $this->strategy = new AbstractRetornoGenerator($data);
                    break;
                case 'REMESSA':
                    $this->strategy = new AbstractRemessaGenerator($data, $tipo);
                    break;
                default:
                    $this->strategy = new AbstractRetornoGenerator($data, $tipo);
                    break;
            }
        }

        public function create($data) {
            return $this->strategy->create($data);
        }

    }