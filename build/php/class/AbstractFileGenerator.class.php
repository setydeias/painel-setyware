<?php
    
    error_reporting(0);
    include_once 'interfaces/IAbstractFileGenerator.php';
    include_once 'strategy_class/FileStrategy.class.php';

    class AbstractFileGenerator implements IAbstractFileGenerator {
        
        public function __construct(array $data, $tipo = 'RETORNO') {
            $this->data = $data;
            $this->tipo = strtoupper($tipo);

            $this->fileStrategy = new FileStrategy($this->tipo, $this->data);
        }

        public function create() {
            return $this->fileStrategy->create($this->tipo, $this->data);
        }

    }