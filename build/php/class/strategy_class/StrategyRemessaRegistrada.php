<?php

    include_once 'StrategyRemessaBB.php';
    include_once 'StrategyRemessaCEF.php';
    include_once 'StrategyRemessaBRD.php';
    include_once 'StrategyRemessaCEFSinco.php';

    class StrategyRemessaRegistrada {

        private $strategy;

        public function __construct($banco, $convenio) {
            switch ( $banco ) {
                case '001':
                    $this->strategy = new StrategyRemessaBB();
                    break;
                case '104':
                    $this->strategy = ($convenio == '0264151' || $convenio == '0689494') 
                        ? new StrategyRemessaCEF() 
                        : new StrategyRemessaCEFSinco();
                    break;
                case '237':
                    $this->strategy = new StrategyRemessaBRD();
                    break;
                case '756':
                    $this->strategy = new StrategyRemessaSICOOB();
                    break;
                default:
                    $this->strategy = null;
                    break;
            }
        }

        public function GenerateFileHeader($params) {
            return $this->strategy->GenerateFileHeader($params);
        }

        public function GenerateLoteHeader($params) {
            return $this->strategy->GenerateLoteHeader($params);
        }

        public function SegmentoP($params, $data) {
            return $this->strategy->SegmentoP($params, $data);
        }

        public function SegmentoQ($params, $data) {
            return $this->strategy->SegmentoQ($params, $data);
        }

        public function SegmentoR($params, $data) {
            return $this->strategy->SegmentoR($params, $data);
        }

        public function GenerateLoteTrailer($params) {
            return $this->strategy->GenerateLoteTrailer($params);
        }

        public function GenerateFileTrailer($params) {
            return $this->strategy->GenerateFileTrailer($params);
        }

    }