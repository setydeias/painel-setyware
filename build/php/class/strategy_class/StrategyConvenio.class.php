<?php

    class StrategyConvenio {

        private $customer;
        private $convenio;

        public function __construct($convenio) {
            $this->convenio = $convenio;
        }

        public function getParams() {
            $crud = new FirebirdCRUD();
            $convenio = $this->convenio;
            $data = $crud->Select(array(
                'table' => 'CEDENTES_CONVENIOS cc',
                'params' => 's.NOMSAC, s.DOCSAC, s.TPDOCSAC, s.CLI_SIGLA, cc.BANCO, cc.AGENCIA, cc.CONTA, cc.OPERACAO, cc.TIPO, cc.MANTENEDOR, cc.CONVENIO, cc.CARTEIRA, cc.VARIACAO, cc.PADRAO, cc.CHECAR_ARQUIVO_REPOSICAO',
                'left_join' => array(
                    'table' => 'SACADOS s',
                    'on' => 's.CLI_SIGLA, cc.MANTENEDOR'
                ),
                'where' => array('cc.CONVENIO' => $convenio )
            ));
            $convenio_info = array();
            
            if ( count($data) > 0 ) {
                for ( $i = 0 ; $i < count($data['BANCO']) ; $i++ ) {
                    $convenio_info[] = array(
                        'cod_banco' => $data['BANCO'][$i],
                        'agencia' => $data['AGENCIA'][$i],
                        'conta' => $data['CONTA'][$i],
                        'OPERACAO' => $data['OPERACAO'][$i],
                        'tipo_documento' => is_null($data['DOCSAC'][$i]) ? '2' : $data['TIPO'][$i],
                        'MANTENEDOR' => $data['MANTENEDOR'][$i],
                        'convenio' => $data['CONVENIO'][$i],
                        'carteira' => $data['CARTEIRA'][$i],
                        'variacao' => $data['VARIACAO'][$i],
                        'PADRAO' => $data['PADRAO'][$i],
                        'documento' => is_null($data['DOCSAC'][$i]) ? '03377700000198' : $data['DOCSAC'][$i],
                        'razao_social' => is_null($data['NOMSAC'][$i]) ? 'SETYDEIAS SERVICOS LTDA' : strtoupper($data['NOMSAC'][$i]),
                        'sigla' => is_null($data['CLI_SIGLA'][$i]) ? 'STY' : $data['CLI_SIGLA'][$i],
                        'CHECAR_ARQUIVO_REPOSICAO' => $data['CHECAR_ARQUIVO_REPOSICAO'][$i]
                    );
                }
            }
            $hasCustomer = count($convenio_info) > 0;
            $this->customer = $hasCustomer ? $convenio_info[0] : null;
            
            return $this->customer;
        }

    }