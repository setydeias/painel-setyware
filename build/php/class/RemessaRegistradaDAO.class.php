<?php

    include_once 'FirebirdCRUD.class.php';

    class RemessaRegistradaDAO {

        private $dao;

        public function __construct() {
            $this->dao = new FirebirdCRUD();
        }

        //Incrementa o sequencial da remessa dos bancos informados em $data
        public function UpdateSeqShipping($data) {
            //O que vem é o código do banco
            $bancos = array('001', '104', '237', '756');
            //Converte o código do banco no nome do campo na tabela
            $table_names = array('REMESSA_BB', 'REMESSA_CEF', 'REMESSA_BRD', 'REMESSA_SICOOB');
            //Transforma o array em string separado por vírgula
            $data = implode(', ', $data);
            $fields = str_replace($bancos, $table_names, $data);
            //Obtém a numeração do sequencial de cada banco
            $select = $this->dao->Select(array(
                'table' => 'REMESSAS_REGISTRADAS',
                'params' => $fields
            ));
            //Montando o array para atualizar
            $set = array();
            foreach ( $select as $field => $value ) $set[$field] = str_pad($value[0] + 1, 6, '0', STR_PAD_LEFT);
            
            $update = $this->dao->Update(array('table' => 'REMESSAS_REGISTRADAS','set' => $set, 'where' => array('1' => 1)));
        }

    }