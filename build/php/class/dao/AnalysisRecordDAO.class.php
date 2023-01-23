<?php
    
    include_once $_SERVER['DOCUMENT_ROOT']."/painel/build/php/class/FirebirdCRUD.class.php";
    include_once $_SERVER['DOCUMENT_ROOT']."/painel/build/php/class/constants/codigos_movimentacao.php";

    class AnalysisRecordDAO {

        public function __construct() {
            $this->con = new FirebirdCRUD();
            $this->occurrences_list = $this->getOccurrencesList();
        }

        /**
         * Insere os registros processados no banco de dados
         *
         * @param array $dataset
         * @return void
         */
        public function insertRecords(array $dataset) {
            $this->con->Insert(array( 'table' => 'TITULOS_PROCESSADOS', 'columns' => $dataset ));
        }

        /**
         * Retorna os dados dos nossos números informados
         * 
         * @param array $data
         * @return array
         */
        public function getRecordInfo(array $data) {
            $occurrences = array();
            $imploded_data = "(".implode(',', $data).")";
            
            $selected = $this->con->Select(array(
                'table' => 'TITULOS_PROCESSADOS tp',
                'params' => 'tp.COD_MOVIMENTO, tp.NOSSO_NUMERO, tp.MOTIVO_REJEICAO, tp.DATA_ARQUIVO, tp.SIGLA_CLIENTE, tp.VALOR_TITULO, tp.TIPO_REGISTRO',
                'where' => "tp.NOSSO_NUMERO in $imploded_data ORDER BY tp.DATA_ARQUIVO, tp.DATA_PROCESSAMENTO ASC"
            ));

            //Agrupa os dados por cliente e nosso número
            for ( $i = 0; $i < count($selected['COD_MOVIMENTO']); $i++ ) {
                $cod_movimento = $selected['COD_MOVIMENTO'][$i];
                $nosso_numero = $selected['NOSSO_NUMERO'][$i];
                $motivo_rejeicao = $selected['MOTIVO_REJEICAO'][$i];
                $data_arquivo = $selected['DATA_ARQUIVO'][$i];
                $sigla_cliente = $selected['SIGLA_CLIENTE'][$i];
                $valor = $selected['VALOR_TITULO'][$i];
                $tipo_registro = $selected['TIPO_REGISTRO'][$i];

                $occurrences[$sigla_cliente][$nosso_numero][] = array(
                    'VALOR_TITULO' => $valor,
                    'COD_MOVIMENTO' => $cod_movimento,
                    'MOTIVO_REJEICAO' => $cod_movimento === REJEITADO && $motivo_rejeicao !== "00" ? $this->occurrences_list[$motivo_rejeicao] : NULL,
                    'DATA_ARQUIVO' => $selected['DATA_ARQUIVO'][$i],
                    'TIPO_REGISTRO' => $tipo_registro
                );
            }

            return $occurrences;
        }

        /**
         * Obtém a lista de ocorrências no banco
         * 
         * @return array
         */
        public function getOccurrencesList() {
            $occurrences = $this->con->Select(array(
                'table' => 'MOTIVOS_REJEICAO mr',
                'params' => 'mr.CODIGO_MOTIVO, mr.DESCRICAO'
            ));
            
            $data = array();

            for ( $i = 0; $i < count($occurrences['CODIGO_MOTIVO']); $i++ ) {
                $data[$occurrences['CODIGO_MOTIVO'][$i]] = $occurrences['DESCRICAO'][$i];
            }

            return $data;
        }

    }