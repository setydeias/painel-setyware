<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/interfaces/IFileStrategy.php';

    class AbstractRemessaGenerator implements IFileStrategy {

        public function __construct($data, $tipo) {
            $this->data = $data;
        }

        public function GenerateFileHeader() {

        }

        public function GenerateLoteHeader() {

        }

        public function GenerateLoteTrailer() {

        }
        
        public function GenerateFileTrailer() {

        }

        public function create() {
            
        }

    }