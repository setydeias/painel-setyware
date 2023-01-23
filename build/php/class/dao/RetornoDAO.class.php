<?php

    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/FirebirdCRUD.class.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/painel/build/php/class/Util.class.php';

    class RetornoDAO {

        public function __construct() {
            $this->con = new FirebirdCRUD();
        }

        /**
         * Formata a string da consulta
         *
         * @param string $de
         * @param string $ate
         * @return string
         */
        public function fmtDateParam($de, $ate) {
            //Checa se as datas são válidas
            if ( is_null($de) || $de === '' ) {
                throw new Exception("Campo De é obrigatório");
            }

            if ( $ate !== '' && Util::FmtDate($de, '23') > Util::FmtDate($ate, '23') ) {
                throw new Exception("Campo Até deve ser maior ou igual ao campo De");
            }

            //Formata os valores
            return $ate !== '' ? "tp.DATA_ARQUIVO BETWEEN '$de' AND '$ate'" : "tp.DATA_ARQUIVO >= '$de'";
        }

        /**
         * Retorna a lista de boletos pagos
         *
         * @param array $data
         * @return array
         */
        public function getPaidBillet(array $data) {
            try {
                $customer = $data['customer'];
                $convenio = $data['convenio'];
                $data_de = $data['data_de'];
                $data_ate = $data['data_ate'];
                $data_arquivo = $this->fmtDateParam($data_de, $data_ate);
                $convenio_clause = $convenio === '' || is_null($convenio) ? '' : "AND tp.CONVENIO = '$convenio'";

                $dataset = $this->con->Select(array(
                    'table' => "TITULOS_PROCESSADOS tp",
                    'params' => "tp.ID_TITULO_PROCESSADO, tp.BANCO, tp.CONVENIO, tp.COD_MOVIMENTO, tp.SIGLA_CLIENTE, tp.NOSSO_NUMERO, tp.SEU_NUMERO, tp.MOEDA, tp.AGENCIA, tp.AGENCIA_DV, tp.CONTA_CORRENTE, tp.CONTA_CORRENTE_DV, tp.BANCO_RECEBEDOR, tp.AGENCIA_RECEBEDORA, tp.AGENCIA_RECEBEDORA_DV, tp.VALOR, tp.VALOR_TARIFA, tp.VALOR_ENCARGOS, tp.VALOR_DESCONTO_CONCEDIDO, tp.VALOR_ABATIMENTO, tp.VALOR_PAGO, tp.VALOR_CREDITADO, tp.DATA_VCTO, tp.DATA_PGTO, tp.DATA_CREDITO, tp.MOTIVO_REJEICAO, tp.DATA_PROCESSAMENTO, tp.DATA_ARQUIVO, tp.VALOR_TITULO, tp.TIPO_REGISTRO",
                    'where' => "tp.COD_MOVIMENTO IN ('06', '17') AND tp.SIGLA_CLIENTE = '$customer' $convenio_clause AND $data_arquivo AND tp.TIPO_REGISTRO = 'RETORNO'"
                ));

                $paid_records = array();
                
                if ( count($dataset) > 0 ) {
                    foreach ( $dataset as $key => $value ) {
                        foreach ( $value as $index => $valor ) {
                            $paid_records[$index][$key] = $valor;
                        }
                    }
                }

                return $paid_records;
            } catch ( Exception $e ) {
                echo json_encode( array('success' => false, 'message' => $e->getMessage()) );
                exit;
            }
        }

    }