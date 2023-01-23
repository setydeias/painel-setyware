<?php
    error_reporting(E_ALL);
    include_once 'FirebirdCRUD.class.php';

    class ConvenioCobranca {

        protected $connection;

        public function __construct() {
            $this->connection = new FirebirdCRUD();
        }
        
        public function __destruct() {}

        public function get($sigla = null) {
            $data = $this->connection->Select(array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'params' => is_null($sigla) ? 'cc.BANCO, cc.TIPO, cc.MANTENEDOR, cc.CONVENIO' : "cc.BANCO, cc.TIPO, cc.MANTENEDOR, cc.CONVENIO where cc.MANTENEDOR = '$sigla'"
            ));

            if ( !count($data) ) return false;

            $convenio_info = array();
        
            for ( $i = 0 ; $i < count($data['BANCO']) ; $i++ ) {
                $convenio_info[] = array(
                    'BANCO' => $data['BANCO'][$i],
                    'TIPO' => $data['TIPO'][$i],
                    'MANTENEDOR' => $data['MANTENEDOR'][$i],
                    'CONVENIO' => $data['CONVENIO'][$i]
                );
            }

            return $convenio_info;
        }

        public function remove($convenio = null) {
            return $this->connection->Delete(array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'columns' => array( 'cc.CONVENIO' => $convenio ),
                'messageInSuccess' => 'Convênio excluído com sucesso'
            ));
        }
        
    }